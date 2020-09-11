<?php

namespace App\Models;

use App\Exceptions\InternalException;
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

    /**
     * 减库存
     *
     * @param [type] $amount
     * @return void
     */
    public function decreaseStock($amount)
    {
        if ($amount < 0) {
            throw new InternalException('减库存不能小于0');
        }

        return $this->where('id', $this->id)->where('stock', '>=', $amount)->decrement('stock', $amount);
    }

    /**
     * 添加库存
     *
     * @param [type] $amount
     * @return void
     */
    public function addStock($amount)
    {
        if ($amount < 0) {
            throw new InternalException('加库存不能小于0');
        }

        $this->increment('stock', $amount);
    }
}
