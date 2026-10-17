<?php

namespace App\Models;

use App\Integrations\Turbo\Models\Shipment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_CUTTING = 'cutting';
    public const STATUS_SEWING = 'sewing';
    public const STATUS_READY_TO_SHIP = 'ready-to-ship';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELED = 'canceled';

    protected $fillable = [
        'customer_id',
        'order_number',
        'customer_name',
        'customer_phone_1',
        'customer_phone_2',
        'customer_instagram',
        'total_price',
        'is_shipped_paid',
        'shipping_due_amount',
        'return_due_amount',
        'order_contents',
        'source',
        'status',
        'is_urgent',
        'delivery_date',
    ];

    /**
     * Map any Turbo courier status code or text to standard Tagmedix order status.
     */
    public static function mapTurboStatus(int|string|null $turboStatus, ?string $statusText = null): ?string
    {
        $code = is_numeric($turboStatus) ? (int) $turboStatus : null;
        $text = mb_strtolower(trim((string) ($statusText ?? (is_string($turboStatus) ? $turboStatus : ''))));

        // 1. Strict numeric code matching from Turbo API specifications
        if ($code === 3) {
            return self::STATUS_DELIVERED;
        }
        if ($code === 4) {
            return self::STATUS_CANCELED;
        }
        if ($code === 1 || $code === 2) {
            return self::STATUS_SHIPPED;
        }

        // 2. Semantic text matching (Arabic & English)
        if (str_contains($text, 'تسليم') || str_contains($text, 'delivered') || str_contains($text, 'completed') || str_contains($text, 'مقبولة') || str_contains($text, 'المقبوله')) {
            return self::STATUS_DELIVERED;
        }
        if (str_contains($text, 'مرتجع') || str_contains($text, 'ملغي') || str_contains($text, 'إلغاء') || str_contains($text, 'محذوف') || str_contains($text, 'canceled') || str_contains($text, 'cancelled') || str_contains($text, 'returned') || str_contains($text, 'failed')) {
            return self::STATUS_CANCELED;
        }
        if (str_contains($text, 'جاري') || str_contains($text, 'شحن') || str_contains($text, 'transit') || str_contains($text, 'shipped') || str_contains($text, 'طريق') || str_contains($text, 'مندوب') || str_contains($text, 'استلام') || str_contains($text, 'pending') || str_contains($text, 'انتظار')) {
            return self::STATUS_SHIPPED;
        }

        return null;
    }

    protected $casts = [
        'is_shipped_paid' => 'boolean',
        'is_urgent' => 'boolean',
        'delivery_date' => 'date',
    ];

    // إرجاع قيمة Snapshot إن وجدت، أو العودة لبيانات العميل الحالية
    public function getCustomerNameAttribute()
    {
        return $this->attributes['customer_name'] ?? $this->customer?->name ?? '';
    }

    public function getPhone1Attribute()
    {
        return $this->attributes['customer_phone_1'] ?? $this->customer?->phone_1 ?? '';
    }

    public function getPhone2Attribute()
    {
        return $this->attributes['customer_phone_2'] ?? $this->customer?->phone_2 ?? '';
    }

    public function getGovernorateAttribute()
    {
        return $this->address?->governorate ?? '';
    }

    public function getFullAddressAttribute()
    {
        return $this->address?->full_address ?? '';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function address()
    {
        return $this->hasOne(CustomerOrderAddress::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getTotalPaid()
    {
        return $this->payments_sum_amount ?? 0;
    }

    public function getRemainingAmount()
    {
        return $this->total_price - $this->getTotalPaid();
    }



    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }


    public function productionTasks()
    {
        return $this->hasMany(ProductionTask::class);
    }
    public function productionLogs()
    {
        return $this->hasMany(ProductionStepLog::class);
    }
    public function histories()
    {
        return $this->hasMany(ProductionHistory::class);
    }
    public function tailorPiecePrices()
    {
        return $this->hasMany(TailorPiecePrice::class);
    }
    public function assignments()
    {
        return $this->hasMany(ProductionAssignment::class);
    }

    public function returns()
    {
        return $this->hasMany(OrderReturn::class);
    }

    /**
     * Get remaining days until delivery date.
     * Positive = days left, 0 = due today, Negative = days overdue.
     */
    public function getRemainingDaysAttribute(): ?int
    {
        if (!$this->delivery_date) {
            return null;
        }

        $today = now()->startOfDay();
        $target = $this->delivery_date->copy()->startOfDay();

        return (int) $today->diffInDays($target, false);
    }

    /**
     * Get detailed delivery status information for UI badges.
     */
    public function getDeliveryStatusInfoAttribute(): array
    {
        $days = $this->remaining_days;

        if ($days === null) {
            return [
                'has_date' => false,
                'days' => null,
                'label' => 'غير محدد',
                'badge_class' => 'bg-gray-100 text-gray-700 border-gray-300',
                'is_late' => false,
                'is_today' => false,
                'is_urgent' => (bool) $this->is_urgent,
            ];
        }

        if ($days < 0) {
            $absDays = abs($days);
            $dayWord = $absDays === 1 ? 'يوم' : ($absDays === 2 ? 'يومين' : ($absDays <= 10 ? 'أيام' : 'يوم'));
            return [
                'has_date' => true,
                'days' => $days,
                'label' => "متأخر {$absDays} {$dayWord}",
                'badge_class' => 'bg-red-100 text-red-800 border-red-300 animate-pulse',
                'is_late' => true,
                'is_today' => false,
                'is_urgent' => (bool) $this->is_urgent,
            ];
        }

        if ($days === 0) {
            return [
                'has_date' => true,
                'days' => 0,
                'label' => 'تسليم اليوم',
                'badge_class' => 'bg-amber-100 text-amber-800 border-amber-300 font-bold',
                'is_late' => false,
                'is_today' => true,
                'is_urgent' => (bool) $this->is_urgent,
            ];
        }

        $dayWord = $days === 1 ? 'يوم' : ($days === 2 ? 'يومين' : ($days <= 10 ? 'أيام' : 'يوم'));
        return [
            'has_date' => true,
            'days' => $days,
            'label' => "باقي {$days} {$dayWord}",
            'badge_class' => $days <= 2 ? 'bg-orange-100 text-orange-800 border-orange-300' : 'bg-blue-100 text-blue-800 border-blue-300',
            'is_late' => false,
            'is_today' => false,
            'is_urgent' => (bool) $this->is_urgent,
        ];
    }
}

