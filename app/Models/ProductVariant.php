<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [

        'product_id',
        'color',
        'size',
        'quantity',
        'minimum_quantity',
        'selling_price',
        'barcode',
        'image',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}