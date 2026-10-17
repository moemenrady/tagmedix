<?php

namespace App\Http\Controllers;

use App\Integrations\Turbo\Models\Shipment;
use App\Services\Turbo\TurboApiService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TurboController extends Controller
{
    public function __construct(
        protected TurboApiService $turboApi
    ) {}

    public function index()
    {
        return view('pages.turbo.index');
    }

    /**
     * جلب الشحنات من الـ DB والـ API معاً والمطابقة بينهما
     */
    public function getData()
    {
        try {
            // 1. جلب شحنات قاعدة البيانات محلياً
            $dbShipments = Shipment::latest()->get();

            // 2. جلب شحنات Turbo API بحماية فائقة عبر getStatus
            $apiShipmentsMap = [];
            try {
                if (method_exists($this->turboApi, 'getStatus')) {
                    $from = date('Y-m-d', strtotime('-60 days'));
                    $to   = date('Y-m-d', strtotime('+1 day'));

                    $rawApiResponse = $this->turboApi->getStatus($from, $to);
                    $apiResponse = json_decode(json_encode($rawApiResponse), true);

                    $apiList = $apiResponse['data']
                        ?? $apiResponse['result']['data']
                        ?? $apiResponse['result']
                        ?? $apiResponse
                        ?? [];

                    if (is_array($apiList)) {
                        foreach ($apiList as $apiItem) {
                            if (is_array($apiItem)) {
                                $code = $apiItem['code'] ?? $apiItem['tracking_number'] ?? $apiItem['remote_order_id'] ?? $apiItem['id'] ?? null;
                                if ($code) {
                                    $apiShipmentsMap[(string) $code] = $apiItem;
                                }
                            }
                        }
                    }
                }
            } catch (Exception $apiEx) {
                Log::warning('Turbo API Fetch Warning: '.$apiEx->getMessage());
            }

            $resultList = collect();
            $processedApiCodes = [];

            // 3. معالجة سجلات الـ DB
            foreach ($dbShipments as $shipment) {

                $turboCode = $shipment->turbo_id;

                // استخراج كود Turbo المرجعي من حقل الـ response بأمان دون إحداث Crash
                if (! $turboCode && ! empty($shipment->response)) {
                    $resData = $shipment->response;

                    // إذا كان الحقل نصياً وليس مصفوفة مسبقاً، نقوم بفك التشفير
                    if (is_string($resData)) {
                        $resData = json_decode($resData, true) ?? [];
                    }

                    if (is_array($resData)) {
                        $turboCode = $resData['data'][0]['code']
                            ?? $resData['data']['code']
                            ?? $resData['result']['code']
                            ?? $resData['code']
                            ?? null;
                    }
                }

                $remoteOrderId = $shipment->remote_order_id;

                // المطابقة مع بيانات الـ API
                $apiData = null;
                if ($turboCode && isset($apiShipmentsMap[(string) $turboCode])) {
                    $apiData = $apiShipmentsMap[(string) $turboCode];
                    $processedApiCodes[] = (string) $turboCode;
                } elseif ($remoteOrderId && isset($apiShipmentsMap[(string) $remoteOrderId])) {
                    $apiData = $apiShipmentsMap[(string) $remoteOrderId];
                    $processedApiCodes[] = (string) $remoteOrderId;
                }

                $hasDb = true;
                $hasApi = ! is_null($apiData);

                // استخراج وتحديد حالة الشحنة
                $statusRaw = strtolower((string) ($apiData['status'] ?? $shipment->status ?? 'pending'));
                $statusId = match (true) {
                    in_array($statusRaw, ['pending', 'shipped', '1', 'قيد الانتظار', 'تم الشحن']) => 1,
                    in_array($statusRaw, ['in_transit', 'delivering', '2', 'جاري التوصيل', 'مع المندوب']) => 2,
                    in_array($statusRaw, ['delivered', 'completed', '3', 'تم التسليم', 'مكتمل']) => 3,
                    in_array($statusRaw, ['canceled', 'cancelled', '4', 'ملغي']) => 4,
                    default => 1,
                };

                $resultList->push([
                    'id' => (string) $shipment->id,
                    'order_code' => (string) ($shipment->remote_order_id ?? ('ORD-'.str_pad($shipment->id, 5, '0', STR_PAD_LEFT))),
                    'tracking_number' => (string) ($turboCode ?? $shipment->remote_order_id ?? 'غير مولد'),
                    'receiver' => (string) ($shipment->receiver ?? 'غير محدد'),
                    'phone' => (string) ($shipment->phone ?? ''),
                    'amount' => (float) ($shipment->amount ?? 0),
                    'government' => (string) ($shipment->government ?? ''),
                    'address' => (string) ($shipment->address ?? ''),
                    'status_id' => $statusId,
                    'status_name' => (string) ($apiData['status_text'] ?? $shipment->status_text ?? 'قيد المعالجة'),
                    'updated_at_human' => $shipment->updated_at ? $shipment->updated_at->diffForHumans() : 'مؤخراً',
                    'source_type' => ($hasDb && $hasApi) ? 'both' : 'db_only',
                ]);
            }

            // 4. معالجة سجلات الـ API Only (الشحنات الموجودة في Turbo وغير موجودة بالـ DB)
            foreach ($apiShipmentsMap as $code => $apiItem) {
                if (in_array((string) $code, $processedApiCodes)) {
                    continue;
                }

                $statusRaw = strtolower((string) ($apiItem['status'] ?? 'pending'));
                $statusId = match (true) {
                    in_array($statusRaw, ['pending', 'shipped', '1', 'قيد الانتظار', 'تم الشحن']) => 1,
                    in_array($statusRaw, ['in_transit', 'delivering', '2', 'جاري التوصيل', 'مع المندوب']) => 2,
                    in_array($statusRaw, ['delivered', 'completed', '3', 'تم التسليم', 'مكتمل']) => 3,
                    in_array($statusRaw, ['canceled', 'cancelled', '4', 'ملغي']) => 4,
                    default => 1,
                };

                $resultList->push([
                    'id' => 'API-'.$code,
                    'order_code' => (string) ($apiItem['remote_order_id'] ?? $apiItem['order_code'] ?? ('TURBO-'.$code)),
                    'tracking_number' => (string) $code,
                    'receiver' => (string) ($apiItem['receiver'] ?? $apiItem['customer_name'] ?? 'عميل Turbo'),
                    'phone' => (string) ($apiItem['phone'] ?? $apiItem['phone1'] ?? ''),
                    'amount' => (float) ($apiItem['amount'] ?? $apiItem['amount_to_be_collected'] ?? 0),
                    'government' => (string) ($apiItem['government'] ?? ''),
                    'address' => (string) ($apiItem['address'] ?? ''),
                    'status_id' => $statusId,
                    'status_name' => (string) ($apiItem['status_text'] ?? 'على سيرفر Turbo'),
                    'updated_at_human' => 'مباشر من Turbo',
                    'source_type' => 'api_only',
                ]);
            }

            return response()->json([
                'success' => true,
                'turbo' => $resultList->values(),
            ]);

        } catch (Exception $e) {
            Log::error('Turbo getData Exception: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: '.$e->getMessage(),
                'turbo' => [],
            ], 500);
        }
    }

    public function resendRequest(Request $request)
    {
        $validated = $request->validate(['id' => 'required|exists:shipments,id']);
        $shipment = Shipment::findOrFail($validated['id']);

        try {
            $payload = $shipment->payload ?? [
                'government' => $shipment->government,
                'address' => $shipment->address,
                'amount_to_be_collected' => $shipment->amount,
                'weight' => $shipment->weight ?? 1.0,
                'notes' => $shipment->notes,
                'remote_order_id' => $shipment->remote_order_id,
                'receiver' => $shipment->receiver,
                'phone1' => $shipment->phone,
                'phone2' => $shipment->phone2,
            ];

            $response = $this->turboApi->addOrder($payload);

            $turboCode = $response['data'][0]['code']
                ?? $response['data']['code']
                ?? $response['code']
                ?? $shipment->turbo_id;

            $shipment->update([
                'turbo_id' => $turboCode,
                'status' => 'shipped',
                'status_text' => 'تمت إعادة إرسال الشحنة بنجاح',
                'response' => $response,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تمت إعادة إرسال الشحنة لشركة Turbo بنجاح',
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function cancelRequest(Request $request)
    {
        $validated = $request->validate(['id' => 'required|exists:shipments,id']);
        $shipment = Shipment::findOrFail($validated['id']);

        try {
            if ($shipment->turbo_id) {
                $this->turboApi->cancelOrder($shipment->turbo_id);
            }

            $shipment->update([
                'status' => 'canceled',
                'status_text' => 'تم إلغاء الشحنة',
            ]);

            return response()->json(['success' => true, 'message' => 'تم إلغاء الشحنة بنجاح']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function deleteOrder(Request $request)
    {
        $validated = $request->validate(['remote_order_id' => 'required']);

        try {
            Shipment::where('remote_order_id', $validated['remote_order_id'])
                ->orWhere('id', $validated['remote_order_id'])
                ->delete();

            return response()->json(['success' => true, 'message' => 'تم حذف سجل الشحنة بنجاح']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Handle incoming Turbo Webhook
     */
    public function webhook(Request $request)
    {
        try {
            $result = $this->turboApi->handleWebhook($request->all());
            
            if ($result['success']) {
                return response()->json(['success' => true, 'message' => $result['message']]);
            }
            
            return response()->json(['success' => false, 'message' => $result['message']], 400);
        } catch (Exception $e) {
            Log::error('Turbo Webhook Exception: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Fetch status directly from Turbo API
     */
    public function apiStatus(Request $request)
    {
        $validated = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        try {
            $response = $this->turboApi->getStatus($validated['from'], $validated['to']);
            
            // Extract the list from the response
            $apiList = $response['data'] ?? $response['result']['data'] ?? $response['result'] ?? $response ?? [];
            
            return response()->json([
                'success' => true,
                'turbo' => $apiList
            ]);
        } catch (Exception $e) {
            Log::error('Turbo getStatus Exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب البيانات من الخادم',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
