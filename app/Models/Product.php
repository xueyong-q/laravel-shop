<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'description', 'image', 'on_sale', 
        'rating', 'sold_count', 'review_count', 'price'
    ];

    protected $casts = [
        'on_sale' => 'boolean'
    ];

    /**
     * 商品 sku 关联
     *
     * @return void
     */
    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }
}
