<?php

namespace App\Enums;

enum InventoryTransactionType: string
{
    case PURCHASE = 'purchase';
    case OPENING_BALANCE = 'opening_balance';
    case PRODUCTION_ISSUE = 'production_issue';
    case PRODUCTION_RETURN = 'production_return';
    case ADJUSTMENT_ADD = 'adjustment_add';
    case ADJUSTMENT_MINUS = 'adjustment_minus';
    case INVENTORY_COUNT = 'inventory_count';
    case DAMAGED = 'damaged';
    case RETURNED_SUPPLIER = 'returned_supplier';
    case CUSTOMER_RETURN = 'customer_return';

    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
