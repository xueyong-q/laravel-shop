<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSku extends Model
{
    protected $fillable = [
        'title', 'description', 'price', 'stock'
    ];

    /**
     * 商品关联
     *
     * @return void
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
