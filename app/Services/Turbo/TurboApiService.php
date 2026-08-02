<?php

namespace App\Services\Turbo;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TurboApiService
{
    protected string $baseUrl;

    protected string|int $mainClientCode;

    protected string $authenticationKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('services.turbo_api.base_url'),
            '/'
        );

        $this->mainClientCode = config(
            'services.turbo_api.main_client_code'
        );

        $this->authenticationKey = config(
            'services.turbo_api.authentication_key'
        );
    }

    /**
     * Base HTTP Client
     */
    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->timeout(
                config('services.turbo_api.timeout', 30)
            )
            ->connectTimeout(
                config('services.turbo_api.connect_timeout', 10)
            )
            ->retry(
                config('services.turbo_api.retry_times', 3),
                config('services.turbo_api.retry_sleep', 500),
                throw: false
            );
    }

    /**
     * Authentication Query Parameters
     */
    protected function authParams(
        array $params = []
    ): array {

        return array_merge([

            'main_client_code' => $this->mainClientCode,

            'authentication_key' => $this->authenticationKey,

        ], $params);
    }

    /**
     * Handle API Response
     */
    protected function handleResponse($response): array
    {
        if ($response->failed()) {

            Log::error('Turbo API Error', [

                'status' => $response->status(),

                'body' => $response->body(),

            ]);

            throw new RuntimeException(

                'Turbo API request failed: ' .
                    $response->status()

            );
        }

        return $response->json() ?? [];
    }

    /*
    |--------------------------------------------------------------------------
    | Turbo Metadata
    |--------------------------------------------------------------------------
    */

    /**
     * جلب فئات التذاكر
     */
    public function getCategories(): array
    {
        $response = $this->client()->get(
            '/external-api/turbo/categories',
            $this->authParams()
        );

        return $this->handleResponse($response);
    }

    /**
     * جلب حالات التذاكر
     */
    public function getStatuses(): array
    {
        $response = $this->client()->get(
            '/external-api/turbo/statuses',
            $this->authParams()
        );

        return $this->handleResponse($response);
    }

    /**
     * جلب تعليمات التذاكر
     */
    public function getInstructions(): array
    {
        $response = $this->client()->get(
            '/external-api/turbo/instructions',
            $this->authParams()
        );

        return $this->handleResponse($response);
    }

    /*
    |--------------------------------------------------------------------------
    | Turbo
    |--------------------------------------------------------------------------
    */

    /**
     * جلب جميع التذاكر
     */
    public function getTurbo(
        array $filters = []
    ): array {

        $response = $this->client()->get(

            '/external-api/turbo',

            $this->authParams($filters)

        );

        return $this->handleResponse($response);
    }

    /**
     * التحقق من إمكانية إنشاء تذكرة
     */
    public function canCreateTurbo(): array
    {
        $response = $this->client()->get(

            '/external-api/turbo/can-create',

            $this->authParams()

        );

        return $this->handleResponse($response);
    }

    /**
     * إنشاء تذكرة جديدة
     */
    public function createTurbo(
        int|string $categoryId,

        string $content,

        ?string $code = null,

        array $media = []

    ): array {

        $payload = [

            'category_id' => $categoryId,

            'content' => $content,

            'code' => $code,

        ];

        return $this->sendMultipartRequest(

            method: 'post',

            endpoint: '/external-api/turbo/create',

            payload: $payload,

            media: $media

        );
    }

    /**
     * إضافة رسالة إلى تذكرة
     */
    public function addMessage(
        int|string $turboId,

        string $content,

        array $media = []

    ): array {

        $payload = [

            'Turbo_id' => $turboId,

            'content' => $content,

        ];

        return $this->sendMultipartRequest(

            method: 'post',

            endpoint: "/external-api/turbo/{$turboId}/message-store",

            payload: $payload,

            media: $media

        );
    }

    /**
     * تقييم التذكرة
     */
    public function rateTurbo(

        int|string $turboId,

        int $rate

    ): array {

        $response = $this->client()->post(

            "/external-api/turbo/{$turboId}/rate",

            [

                'rate' => $rate,

            ]

        );

        return $this->handleResponse($response);
    }

    /**
     * إجمالي التذاكر المفتوحة
     */
    public function getTotalOpenTurbo(): array
    {
        $response = $this->client()->get(

            '/external-api/turbo/total-open-turbo',

            $this->authParams()

        );

        return $this->handleResponse($response);
    }

    /*
    |--------------------------------------------------------------------------
    | Media
    |--------------------------------------------------------------------------
    */

    /**
     * إرسال Multipart Request
     */
    protected function sendMultipartRequest(

        string $method,

        string $endpoint,

        array $payload,

        array $media = []

    ): array {

        $request = $this->client()->asMultipart();

        foreach ($payload as $key => $value) {

            if ($value !== null) {

                $request = $request->attach(

                    $key,

                    (string) $value

                );
            }
        }

        foreach ($media as $file) {

            if ($file instanceof UploadedFile) {

                $request = $request->attach(

                    'media[]',

                    fopen(
                        $file->getRealPath(),
                        'r'
                    ),

                    $file->getClientOriginalName()

                );
            }
        }

        $response = $request->$method(

            $endpoint

        );

        return $this->handleResponse($response);
    }
}
