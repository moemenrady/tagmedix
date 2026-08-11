<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [

        'name',
        'sku',
        'description',
        'selling_price',
        'cost_price',
        'type',
        'sleeve_type',
        'design_code',
        'image',
        'is_active',
        'allow_stock_sale',
        'total_sales',
    ];

    protected static function booted()
    {
        static::creating(function ($product) {

            if (!$product->sku) {

                $product->sku =
                    'PRD-' . strtoupper(uniqid());
            }
        });
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function totalStock()
    {
        return $this->variants()
            ->sum('quantity');
    }
}
