<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Services;

use App\Integrations\Turbo\DTO\AddOrderDTO;
use App\Integrations\Turbo\DTO\SearchOrderDTO;
use App\Integrations\Turbo\DTO\UpdateOrderDTO;
use App\Integrations\Turbo\Exceptions\TurboAuthenticationException;
use App\Integrations\Turbo\Exceptions\TurboNetworkException;
use App\Integrations\Turbo\Exceptions\TurboServerException;
use App\Integrations\Turbo\Exceptions\TurboValidationException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class TurboService
{
    private readonly string $baseUrl;

    private readonly string $authKey;

    private readonly string $clientCode;

    private readonly int $timeout;

    private readonly int $retry;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('turbo.base_url'), '/');
        $this->authKey = (string) config('turbo.auth_key');
        $this->clientCode = (string) config('turbo.client_code');
        $this->timeout = (int) config('turbo.timeout', 30);
        $this->retry = (int) config('turbo.retry', 3);
    }

    public function addOrder(AddOrderDTO $dto): array
    {
        return $this->post('/external-api/add-order', $this->buildPayload([
            'remote_order_id' => $dto->remoteOrderId,
            'receiver' => $dto->receiver,
            'phone' => $dto->phone,
            'government' => $dto->government,
            'area' => $dto->area,
            'address' => $dto->address,
            'amount' => $dto->amount,
            'weight' => $dto->weight,
            'notes' => $dto->notes,
            'delivery_type' => $dto->deliveryType->value,
            'is_order' => $dto->isOrder,
            'is_fragile' => $dto->isFragile,
            'status' => $dto->status->value,
        ]));
    }

    public function updateOrder(UpdateOrderDTO $dto): array
    {
        return $this->post('/external-api/edit-order', $this->buildPayload([
            'remote_order_id' => $dto->remoteOrderId,
            'receiver' => $dto->receiver,
            'phone' => $dto->phone,
            'government' => $dto->government,
            'area' => $dto->area,
            'address' => $dto->address,
            'amount' => $dto->amount,
            'weight' => $dto->weight,
            'notes' => $dto->notes,
            'delivery_type' => $dto->deliveryType?->value,
            'is_order' => $dto->isOrder,
            'is_fragile' => $dto->isFragile,
            'status' => $dto->status?->value,
        ]));
    }

    public function deleteOrder(string $remoteOrderId): array
    {
        return $this->post('/external-api/delete-order', $this->buildPayload([
            'remote_order_id' => $remoteOrderId,
        ]));
    }

    public function search(SearchOrderDTO $dto): array
    {
        return $this->post('/external-api/search-order', $this->buildPayload([
            'remote_order_id' => $dto->remoteOrderId,
            'tracking_number' => $dto->trackingNumber,
            'receiver' => $dto->receiver,
            'phone' => $dto->phone,
            'status' => $dto->status,
        ]));
    }

    public function getStatus(string $remoteOrderId): array
    {
        return $this->post('/external-api/get-status', $this->buildPayload([
            'remote_order_id' => $remoteOrderId,
        ]));
    }

    public function getGovernments(): array
    {
        return $this->get('/external-api/get-government');
    }

    public function getAreas(int|string $governmentId): array
    {
        return $this->get('/external-api/get-area/'.$governmentId);
    }

    public function resend(int|string $turboId): array
    {
        return $this->post('/external-api/resend-request', $this->buildPayload([
            'id' => $turboId,
        ]));
    }

    public function cancel(int|string $turboId): array
    {
        return $this->post('/external-api/canceled', $this->buildPayload([
            'id' => $turboId,
        ]));
    }

    private function get(string $endpoint, array $query = []): array
    {
        $start = $this->requestTime();

        // Pass authentication_key in query parameters for GET requests
        $queryParams = array_merge([
            'authentication_key' => $this->authKey,
        ], $query);

        try {
            $response = $this->client()->get($endpoint, $queryParams);

            return $this->handleResponse($response, $endpoint, $queryParams, $start, 1);
        } catch (Throwable $exception) {
            $this->logException($endpoint, $queryParams, $start, $exception);

            throw $exception;
        }
    }

    private function post(string $endpoint, array $payload): array
    {
        $start = $this->requestTime();

        for ($attempt = 1; $attempt <= $this->retry + 1; $attempt++) {
            try {
                $response = $this->client()->post($endpoint, $payload);

                if ($this->shouldRetry($response->status()) && $attempt <= $this->retry) {
                    continue;
                }

                return $this->handleResponse($response, $endpoint, $payload, $start, $attempt);
            } catch (Throwable $exception) {
                if ($attempt <= $this->retry && $this->shouldRetryException($exception)) {
                    continue;
                }

                $this->logException($endpoint, $payload, $start, $exception);

                throw $exception;
            }
        }

        throw new TurboNetworkException('Turbo request failed after retries.', 503);
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->timeout($this->timeout)
            ->retry($this->retry, 500, throw: false);
    }

    private function buildPayload(array $payload): array
    {
        return array_merge([
            'main_client_code' => $this->clientCode,
            'authentication_key' => $this->authKey,
        ], $payload);
    }

    private function handleResponse(Response $response, string $endpoint, array $payload, float $start, int $attempt): array
    {
        $elapsed = round((microtime(true) - $start) * 1000, 2);
        $statusCode = $response->status();
        $body = $response->body();
        $data = $response->json() ?? [];

        $this->logRequest($endpoint, $payload, $statusCode, $data ?: $body, $start, $attempt);

        if ($response->failed()) {
            $this->throwException($statusCode, 'Turbo request failed.');
        }

        return $data;
    }

    private function logRequest(string $endpoint, array $payload, int $statusCode, mixed $response, float $start, int $attempt): void
    {
        $elapsed = round((microtime(true) - $start) * 1000, 2);

        Log::info('Turbo API request completed', [
            'endpoint' => $endpoint,
            'payload' => $payload,
            'response' => $response,
            'execution_time_ms' => $elapsed,
            'http_status' => $statusCode,
            'attempt' => $attempt,
        ]);
    }

    private function logException(string $endpoint, array $payload, float $start, Throwable $exception): void
    {
        $elapsed = round((microtime(true) - $start) * 1000, 2);

        Log::error('Turbo API request exception', [
            'endpoint' => $endpoint,
            'payload' => $payload,
            'execution_time_ms' => $elapsed,
            'exception' => $exception->getMessage(),
        ]);
    }

    private function throwException(int $statusCode, string $message): never
    {
        if ($statusCode === 401 || $statusCode === 403) {
            throw new TurboAuthenticationException($message, $statusCode);
        }

        if ($statusCode === 422) {
            throw new TurboValidationException($message, $statusCode);
        }

        if ($statusCode >= 500) {
            throw new TurboServerException($message, $statusCode);
        }

        if ($statusCode === 429 || $statusCode === 408 || $statusCode === 503) {
            throw new TurboNetworkException($message, $statusCode);
        }

        throw new TurboNetworkException($message, $statusCode);
    }

    private function requestTime(): float
    {
        return microtime(true);
    }

    private function shouldRetry(int $statusCode): bool
    {
        return in_array($statusCode, [429, 500, 502, 503, 504], true);
    }

    private function shouldRetryException(Throwable $exception): bool
    {
        return false;
    }
}
