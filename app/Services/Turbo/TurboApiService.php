<?php

namespace App\Services\Turbo;

use App\Integrations\Turbo\Models\Shipment;
use App\Integrations\Turbo\Models\ShipmentStatusHistory;
use App\Models\Order;
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
     * Normalize Egyptian and international phone numbers to standard Egyptian format (01xxxxxxxxx) for Turbo
     */
    public function normalizePhoneForTurbo(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Convert Arabic-Indic digits to Latin
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $phone = str_replace($arabicNumbers, $englishNumbers, trim($phone));

        $digits = preg_replace('/[^\d]/', '', $phone);
        if (empty($digits)) {
            return null;
        }

        // 12 digits starting with 201 (e.g. 201223237666) -> 01223237666
        if (strlen($digits) === 12 && str_starts_with($digits, '201')) {
            return '0' . substr($digits, 2);
        }

        // 14 digits starting with 00201 (e.g. 00201223237666) -> 01223237666
        if (strlen($digits) === 14 && str_starts_with($digits, '00201')) {
            return '0' . substr($digits, 4);
        }

        // 10 digits starting with 1 (e.g. 10xxxxxxxx, 12xxxxxxxx) -> 01xxxxxxxxx
        if (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            return '0' . $digits;
        }

        return $digits;
    }

    /**
     * Handle Response & Throw Exception on Failures
     */
    protected function handleResponse(Response $response): array
    {
        $data = $response->json() ?? [];

        // Check all Turbo failure indicators (HTTP error, success === 0/false/"0", errors array, or error_msg)
        $isFailed = $response->failed()
            || (isset($data['success']) && in_array($data['success'], [0, '0', false], true))
            || !empty($data['errors'])
            || !empty($data['error_msg'])
            || (isset($data['status']) && in_array(strtolower((string) $data['status']), ['error', 'failed', '0'], true));

        if ($isFailed) {
            Log::error('Turbo API Error Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'json' => $data,
            ]);

            $errorParts = [];

            if (!empty($data['errors']) && is_array($data['errors'])) {
                foreach ($data['errors'] as $field => $messages) {
                    $fieldArabic = match ($field) {
                        'phone1' => 'رقم الهاتف الأساسي',
                        'phone2' => 'رقم الهاتف الإضافي',
                        'api_followup_phone' => 'هاتف المتابعة',
                        'government' => 'المحافظة',
                        'area' => 'المنطقة',
                        'address' => 'العنوان',
                        'receiver' => 'اسم المستلم',
                        'amount_to_be_collected' => 'المبلغ المطلوب تحصيله',
                        'weight' => 'الوزن',
                        'return_amount' => 'سعر الارتجاع',
                        'remote_order_id' => 'رقم الطلب',
                        'delivery_type' => 'نوع التوصيل',
                        'is_order' => 'نوع الطلب',
                        default => $field,
                    };

                    $formattedMessages = is_array($messages) ? implode(', ', $messages) : (string) $messages;

                    // Translate common Turbo messages
                    $formattedMessages = str_replace(
                        [
                            'The First Phone format is invalid.',
                            'The government field is required.',
                            'The area field is required.',
                            'The address field is required.',
                            'The receiver field is required.',
                        ],
                        [
                            'صيغة رقم الهاتف غير صالحة لدى Turbo (يجب أن يكون رقم مصري 01xxxxxxxxx)',
                            'حقل المحافظة مطلوب',
                            'حقل المنطقة مطلوب',
                            'حقل العنوان مطلوب',
                            'حقل اسم المستلم مطلوب',
                        ],
                        $formattedMessages
                    );

                    $errorParts[] = $fieldArabic . ': ' . $formattedMessages;
                }
            }

            $primaryMessage = $data['error_msg'] ?? $data['message'] ?? 'فشل إرسال الشحنة لشركة Turbo';

            // Translate primary message if common
            if ($primaryMessage === 'The First Phone format is invalid.') {
                $primaryMessage = 'صيغة رقم الهاتف غير صالحة لدى Turbo (يجب أن يكون 11 رقم يبدأ بـ 01)';
            }

            if (!empty($errorParts)) {
                $fullMessage = $primaryMessage . ' (' . implode(' | ', $errorParts) . ')';
            } else {
                $fullMessage = $primaryMessage;
            }

            throw new RuntimeException($fullMessage, $response->status() ?: 422);
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

        // 2. تنظيف وتوحيد أرقام التليفونات لشركة Turbo
        $cleanPhone1 = $this->normalizePhoneForTurbo($shipmentData['phone1'] ?? null);
        $cleanPhone2 = $this->normalizePhoneForTurbo($shipmentData['phone2'] ?? null);
        $cleanApiPhone = $this->normalizePhoneForTurbo($shipmentData['api_followup_phone'] ?? null);

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

        // dd($response->status(), $response->json(), $response->body());

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

        // When Turbo has no updated shipments in the range, it returns 404 with "Shipment not found"
        if ($response->status() === 404 && str_contains(strtolower($response->body()), 'not found')) {
            return ['success' => true, 'data' => []];
        }

        return $this->handleResponse($response);
    }

    /**
     * Search specific order directly by tracking number, order ID, or code on Turbo
     * POST /external-api/search-order
     */
    public function searchOrder(string|int $searchKey): array
    {
        $payload = $this->authParams([
            'search_key' => (string) $searchKey,
        ]);

        $response = $this->client()->post('/external-api/search-order', $payload);

        if ($response->status() === 404 && str_contains(strtolower($response->body()), 'not found')) {
            return ['success' => true, 'result' => []];
        }

        Log::info("Turbo searchOrder for '{$searchKey}':", [
            'status' => $response->status(),
            'data' => $response->json(),
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Unified central method to apply shipment updates from Turbo (API sync, Single sync, or Webhook)
     */
    public function applyShipmentUpdate(Shipment $shipment, array $payload, string $source = 'sync'): void
    {
        $oldStatus = (string) $shipment->status;
        $statusText = (string) ($payload['status_text'] ?? $payload['status'] ?? $shipment->status_text ?? $oldStatus);
        $newStatus = (string) ($payload['status'] ?? $payload['status_code'] ?? $statusText);

        // Protection against Turbo API returning stale status 1 (قيد الإنتظار) for deleted/canceled shipments
        $isLocallyDeleted = in_array(strtolower($oldStatus), ['محذوفة', 'محذوف', 'canceled', 'cancelled', 'ملغي', '4']);
        $isIncomingWaiting = in_array(strtolower($newStatus), ['1', 'قيد الإنتظار', 'pending', 'waiting']);
        if ($isLocallyDeleted && $isIncomingWaiting) {
            $newStatus = $oldStatus;
            $statusText = $shipment->status_text ?: 'محذوفة';
        }

        $existingResponse = is_array($shipment->response) ? $shipment->response : [];
        $mergedResponse = array_merge($existingResponse, $payload, [
            'captain_name' => $payload['captain_name'] ?? $payload['captain'] ?? ($existingResponse['captain_name'] ?? $existingResponse['captain'] ?? null),
            'captain_number1' => $payload['captain_number1'] ?? $payload['captain_phone'] ?? ($existingResponse['captain_number1'] ?? $existingResponse['captain_phone'] ?? null),
            'delay_reason' => $payload['delay_reason'] ?? ($existingResponse['delay_reason'] ?? null),
            'return_reason' => $payload['return_reason'] ?? ($existingResponse['return_reason'] ?? null),
            'branch' => $payload['branch'] ?? $payload['branch_name'] ?? ($existingResponse['branch'] ?? $existingResponse['branch_name'] ?? null),
            'expected_branch' => $payload['expected_branch'] ?? ($existingResponse['expected_branch'] ?? null),
            'last_sync_source' => $source,
        ]);

        // Financials
        $collectedAmount = isset($payload['received_amount']) 
            ? (float) $payload['received_amount'] 
            : (isset($payload['collected_amount']) ? (float) $payload['collected_amount'] : (float) $shipment->collected_amount);

        $orderAmount = isset($payload['order_price']) 
            ? (float) $payload['order_price'] 
            : (isset($payload['amount']) ? (float) $payload['amount'] : (float) $shipment->amount);

        // Timestamps based on lifecycle
        $now = now();
        $deliveredAt = $shipment->delivered_at;
        $returnedAt = $shipment->returned_at;
        $shippedAt = $shipment->shipped_at;

        $statusLower = strtolower($newStatus . ' ' . $statusText);
        if (in_array(strtolower($newStatus), ['delivered', 'completed', '3']) || str_contains($statusLower, 'تسليم') || str_contains($statusLower, 'delivered')) {
            $deliveredAt = $deliveredAt ?: $now;
        }

        if (in_array(strtolower($newStatus), ['returned', 'canceled', 'cancelled', '4', 'deleted']) || str_contains($statusLower, 'مرتجع') || str_contains($statusLower, 'ملغي') || str_contains($statusLower, 'محذوف') || str_contains($statusLower, 'deleted') || str_contains($statusLower, 'returned')) {
            $returnedAt = $returnedAt ?: $now;
        }

        if (in_array(strtolower($newStatus), ['in_transit', 'shipped', '2']) || str_contains($statusLower, 'جاري') || str_contains($statusLower, 'transit')) {
            $shippedAt = $shippedAt ?: $now;
        }

        // Status history audit trail
        if ($oldStatus !== $newStatus) {
            ShipmentStatusHistory::create([
                'shipment_id' => $shipment->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'response' => $payload,
                'created_at' => $now,
            ]);
        }

        // Sync with primary Order model safely
        $order = $shipment->order ?: ($shipment->order_id ? Order::find($shipment->order_id) : null);
        if (! $order && ! empty($shipment->remote_order_id)) {
            $order = Order::where('order_number', $shipment->remote_order_id)->first();
        }

        if ($order) {
            if (!$shipment->order_id) {
                $shipment->forceFill(['order_id' => $order->id])->saveQuietly();
            }

            $mappedStatus = Order::mapTurboStatus($newStatus, $statusText);
            if ($mappedStatus) {
                if ($mappedStatus === Order::STATUS_SHIPPED) {
                    if (!in_array($order->status, [Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_CANCELED])) {
                        $order->update(['status' => Order::STATUS_SHIPPED]);
                    }
                } else {
                    $order->update(['status' => $mappedStatus]);
                }
            }
        }

        // Persist shipment updates
        $shipment->forceFill([
            'status' => $newStatus,
            'status_text' => $statusText,
            'amount' => $orderAmount,
            'collected_amount' => $collectedAmount,
            'notes' => $payload['delay_reason'] ?? $payload['return_reason'] ?? $shipment->notes,
            'delivered_at' => $deliveredAt,
            'returned_at' => $returnedAt,
            'shipped_at' => $shippedAt,
            'last_synced_at' => $now,
            'response' => $mergedResponse,
        ])->save();
    }

    /**
     * Sync single shipment on-demand directly from Turbo API
     */
    public function syncSingleShipment(Shipment $shipment): bool
    {
        $searchCodes = array_filter([
            (string) $shipment->tracking_number,
            (string) $shipment->turbo_id,
            (string) $shipment->remote_order_id,
        ]);

        // 1. First priority: Direct lookup via /external-api/search-order
        foreach ($searchCodes as $code) {
            try {
                $searchResult = $this->searchOrder($code);
                $items = $searchResult['result'] ?? $searchResult['data'] ?? [];
                if (is_array($items) && ! empty($items[0])) {
                    $this->applyShipmentUpdate($shipment, $items[0], 'single_sync');
                    return true;
                }
            } catch (\Throwable $e) {
                // Ignore search exceptions and try fallback
            }
        }

        // 2. Fallback: Lookup in date range status updates
        $from = $shipment->created_at ? $shipment->created_at->format('Y-m-d') : date('Y-m-d', strtotime('-30 days'));
        $to = date('Y-m-d', strtotime('+1 day'));

        try {
            $response = $this->getStatus($from, $to);
            $items = $response['data'] ?? $response['result']['data'] ?? $response['result'] ?? [];

            if (is_array($items)) {
                foreach ($items as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    $itemCode = (string) ($item['code'] ?? $item['tracking_number'] ?? $item['remote_order_id'] ?? $item['id'] ?? '');
                    if ($itemCode && in_array($itemCode, $searchCodes, true)) {
                        $this->applyShipmentUpdate($shipment, $item, 'single_sync');
                        return true;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Single sync fallback getStatus error for {$shipment->id}: " . $e->getMessage());
        }

        // Touch last_synced_at even if no update found
        $shipment->forceFill(['last_synced_at' => now()])->save();
        return false;
    }

    /**
     * Batch sync all shipments within a date range from Turbo API into DB
     */
    public function syncShipmentsFromApi(string $from, string $to): array
    {
        $response = $this->getStatus($from, $to);
        $items = $response['data'] ?? $response['result']['data'] ?? $response['result'] ?? $response ?? [];

        if (! is_array($items)) {
            $items = [];
        }

        $updatedCount = 0;
        $matchedShipments = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $code = (string) ($item['code'] ?? $item['tracking_number'] ?? $item['remote_order_id'] ?? $item['id'] ?? '');
            if (! $code) {
                continue;
            }

            $shipment = Shipment::where('tracking_number', $code)
                ->orWhere('turbo_id', $code)
                ->orWhere('remote_order_id', $code)
                ->first();

            if ($shipment) {
                $this->applyShipmentUpdate($shipment, $item, 'batch_sync');
                $updatedCount++;
                $matchedShipments[] = $shipment->id;
            }
        }

        return [
            'success' => true,
            'total_fetched' => count($items),
            'updated_count' => $updatedCount,
            'matched_shipments' => $matchedShipments,
        ];
    }

    /**
     * Handle incoming Webhook Payload
     */
    public function handleWebhook(array $payload): array
    {
        Log::info('Turbo Webhook Payload in Service', $payload);

        $remoteOrderId = (string) ($payload['remote_order_id'] ?? $payload['order_number'] ?? $payload['order_id'] ?? '');
        $turboId = (string) ($payload['id'] ?? $payload['code'] ?? '');
        $trackingNumber = (string) ($payload['tracking_number'] ?? '');

        if ($remoteOrderId === '' && $turboId === '' && $trackingNumber === '') {
            return ['success' => false, 'message' => 'Missing remote order id, tracking number, or turbo id.'];
        }

        // Locate shipment safely (NEVER local auto-increment id)
        $shipment = Shipment::query()
            ->when($remoteOrderId !== '', fn($q) => $q->where('remote_order_id', $remoteOrderId))
            ->when($remoteOrderId === '' && $turboId !== '', fn($q) => $q->where('turbo_id', $turboId))
            ->when($remoteOrderId === '' && $trackingNumber !== '', fn($q) => $q->orWhere('tracking_number', $trackingNumber))
            ->first();

        if (! $shipment) {
            return ['success' => false, 'message' => 'Shipment not found.'];
        }

        $this->applyShipmentUpdate($shipment, $payload, 'webhook');

        return ['success' => true, 'message' => 'Webhook processed successfully'];
    }
}
