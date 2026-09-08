<?php

namespace App\Services\Turbo;

use App\Integrations\Turbo\Models\Shipment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TurboApiService
{
    protected string $baseUrl;

    protected string|int|null $mainClientCode;

    protected ?string $authenticationKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('services.turbo_api.base_url', 'https://platform.turbo.info'),
            '/'
        );

        $this->mainClientCode = config('services.turbo_api.main_client_code');
        $this->authenticationKey = config('services.turbo_api.authentication_key');
    }

    /**
     * Base HTTP Client
     */
    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->timeout(config('services.turbo_api.timeout', 30))
            ->connectTimeout(config('services.turbo_api.connect_timeout', 10))
            ->retry(
                config('services.turbo_api.retry_times', 3),
                config('services.turbo_api.retry_sleep', 500),
                throw: false
            );
    }

    /**
     * Authentication Parameters
     */
    protected function authParams(array $params = []): array
    {
        return array_merge([
            'main_client_code' => $this->mainClientCode,
            'authentication_key' => $this->authenticationKey,
        ], $params);
    }

    /**
     * Handle Response & Throw Exception on Failures
     */
    protected function handleResponse(Response $response): array
    {
        $data = $response->json() ?? [];

        if ($response->failed() || (isset($data['success']) && $data['success'] === false)) {
            $errorMessage = $data['message'] ?? 'Turbo API Request Failed';

            Log::error('Turbo API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException($errorMessage, $response->status() ?: 400);
        }

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Shipment Management Integration
    |--------------------------------------------------------------------------
    */

    /**
     * Add New Order / Shipment
     * POST /external-api/add-order
     */
    public function addOrder(array $shipmentData): array
    {

        $cleanAddress = $shipmentData['address'] ?? null;
        if ($cleanAddress) {
            $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $cleanAddress = str_replace($arabicNumbers, $englishNumbers, $cleanAddress);

            $cleanAddress = str_replace(["\r\n", "\r", "\n", '"', '\"'], ' - ', $cleanAddress);
            $cleanAddress = preg_replace('/\s+/', ' ', $cleanAddress);
            $cleanAddress = preg_replace('/(\s*-\s*)+/', ' - ', $cleanAddress);
            $cleanAddress = trim($cleanAddress, ' -');
        }

        // 2. تنظيف أرقام التليفونات وضمان رجوع phone2 كـ null لو فاضي
        $cleanPhone1 = ! empty($shipmentData['phone1']) ? preg_replace('/[^\d]/', '', $shipmentData['phone1']) : null;
        $cleanPhone2 = ! empty($shipmentData['phone2']) ? preg_replace('/[^\d]/', '', $shipmentData['phone2']) : null;
        $cleanApiPhone = ! empty($shipmentData['api_followup_phone']) ? preg_replace('/[^\d]/', '', $shipmentData['api_followup_phone']) : null;

        $payload = $this->authParams([
            'second_client' => $shipmentData['second_client'] ?? null,
            'government' => $shipmentData['government'] ?? null,
            'area' => $shipmentData['area'] ?? null,
            'address' => $cleanAddress,
            'api_followup_phone' => $cleanApiPhone,
            'amount_to_be_collected' => (float) ($shipmentData['amount_to_be_collected'] ?? 0),
            'order_summary' => 'منتجات',
            'weight' => (float) ($shipmentData['weight'] ?? 1),
            'notes' => $shipmentData['notes'] ?? null,
            'delivery_type' => (int) ($shipmentData['delivery_type'] ?? 0),
            'remote_order_id' => $shipmentData['remote_order_id'] ?? null,
            'receiver' => $shipmentData['receiver'] ?? null,
            'phone1' => $cleanPhone1,
            'phone2' => $cleanPhone2,
            'is_order' => (int) ($shipmentData['is_order'] ?? 0),
            'is_fragile' => (int) ($shipmentData['is_fragile'] ?? 0),
            'return_amount' => (float) ($shipmentData['return_amount'] ?? 0),
            'remote_shipment_id' => $shipmentData['remote_shipment_id'] ?? null,
            'return_summary' => $shipmentData['return_summary'] ?? null,
            'invoice_number' => $shipmentData['invoice_number'] ?? null,
            'can_open' => (int) ($shipmentData['can_open'] ?? 1),
            'number_of_items' => (int) ($shipmentData['number_of_items'] ?? 1),
        ]);

        $response = $this->client()->post('/external-api/add-order', $payload);


        // شيلنا الـ dd() عشان يرجع الرد الطبيعي للسيستم ويكمل حفظ
        return $this->handleResponse($response);
    }

    /**
     * Update / Edit Existing Order
     * POST /external-api/edit-order
     */
    public function editOrder(int|string $code, array $shipmentData): array
    {
        $payload = $this->authParams([
            'code' => $code,
            'second_client' => $shipmentData['second_client'] ?? null,
            'government' => $shipmentData['government'] ?? null,
            'area' => $shipmentData['area'] ?? null,
            'address' => $shipmentData['address'] ?? null,
            'api_followup_phone' => $shipmentData['api_followup_phone'] ?? null,
            'amount_to_be_collected' => $shipmentData['amount_to_be_collected'] ?? 0,
            'order_summary' => $shipmentData['order_summary'] ?? null,
            'weight' => $shipmentData['weight'] ?? 1,
            'notes' => $shipmentData['notes'] ?? null,
            'delivery_type' => $shipmentData['delivery_type'] ?? 0,
            'remote_order_id' => $shipmentData['remote_order_id'] ?? null,
            'receiver' => $shipmentData['receiver'] ?? null,
            'phone1' => $shipmentData['phone1'] ?? null,
            'phone2' => $shipmentData['phone2'] ?? null,
            'is_order' => $shipmentData['is_order'] ?? 1,
            'is_fragile' => $shipmentData['is_fragile'] ?? 0,
            'return_amount' => $shipmentData['return_amount'] ?? 0,
            'return_summary' => $shipmentData['return_summary'] ?? null,
            'can_open' => $shipmentData['can_open'] ?? 1,
            'number_of_items' => $shipmentData['number_of_items'] ?? 1,
        ]);

        $response = $this->client()->post('/external-api/edit-order', $payload);

        return $this->handleResponse($response);
    }

    /**
     * Cancel Order Request
     * POST /external-api/canceled
     */
    public function cancelOrder(int|string $id): array
    {
        $payload = [
            'authentication_key' => $this->authenticationKey,
            'id' => $id,
        ];

        $response = $this->client()->post('/external-api/canceled', $payload);

        return $this->handleResponse($response);
    }

    /**
     * Get Shipments Status within a Date Range
     * POST /external-api/get-status
     */
    public function getStatus(string $from, string $to): array
    {
        $payload = $this->authParams([
            'from' => $from,
            'to' => $to,
        ]);

        $response = $this->client()->post('/external-api/get-status', $payload);

        return $this->handleResponse($response);
    }

    /**
     * Handle incoming Webhook Payload
     */
    public function handleWebhook(array $payload): array
    {
        Log::info('Turbo Webhook Payload in Service', $payload);

        $remoteOrderId = (string) ($payload['remote_order_id'] ?? $payload['order_number'] ?? $payload['order_id'] ?? $payload['id'] ?? '');

        if ($remoteOrderId === '') {
            return ['success' => false, 'message' => 'Missing remote order id or order number.'];
        }

        // Locate shipment
        $shipment = Shipment::where('remote_order_id', $remoteOrderId)
            ->orWhere('turbo_id', $remoteOrderId)
            ->orWhere('tracking_number', $remoteOrderId)
            ->orWhere('id', $remoteOrderId)
            ->first();

        if (! $shipment) {
            return ['success' => false, 'message' => 'Shipment not found.'];
        }

        $statusText = (string) ($payload['status_text'] ?? $payload['status'] ?? $shipment->status);
        $newStatus = (string) ($payload['status'] ?? $statusText);

        $existingResponse = is_array($shipment->response) ? $shipment->response : [];
        $mergedResponse = array_merge($existingResponse, $payload, [
            'captain_name' => $payload['captain_name'] ?? ($existingResponse['captain_name'] ?? null),
            'captain_number1' => $payload['captain_number1'] ?? ($existingResponse['captain_number1'] ?? null),
            'delay_reason' => $payload['delay_reason'] ?? ($existingResponse['delay_reason'] ?? null),
            'return_reason' => $payload['return_reason'] ?? ($existingResponse['return_reason'] ?? null),
        ]);

        $shipment->forceFill([
            'status' => $newStatus,
            'status_text' => $statusText,
            'amount' => isset($payload['order_price']) ? (float) $payload['order_price'] : $shipment->amount,
            'notes' => $payload['delay_reason'] ?? $payload['return_reason'] ?? $shipment->notes,
            'response' => $mergedResponse,
        ])->save();

        return ['success' => true, 'message' => 'Webhook processed successfully'];
    }
}
