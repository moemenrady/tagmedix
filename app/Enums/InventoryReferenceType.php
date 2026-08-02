<?php

namespace App\Enums;

enum InventoryReferenceType: string
{
    case PURCHASE = 'purchase';
    case PRODUCTION_ORDER = 'production_order';
    case SALES_ORDER = 'sales_order';
    case MANUAL = 'manual';
    case INVENTORY_COUNT = 'inventory_count';

    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
