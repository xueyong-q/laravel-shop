<?php

namespace App\Services;

use App\Models\CartItem;
use Auth;

class CartService
{
    /**
     * 购物车商品列表
     *
     * @return void
     */
    public function get()
    {
        return Auth::user()->cartItems()->with(['productSku.product'])->get();
    }

    /**
     * 添加商品逻辑
     *
     * @param [type] $skuId
     * @param [type] $amount
     * @return void
     */
    public function add($skuId, $amount)
    {
        $user = Auth::user();
        // 从数据库中查询该商品是否已经在购物车中
        if ($item = $user->cartItems()->where('product_sku_id', $skuId)->first()) {
            // 如果存在则直接叠加商品数量
            $item->update([
                'amount' => $item->amount + $amount,
            ]);
        } else {
            $item = new CartItem(['amount' => $amount]);
            $item->user()->associate($user);
            $item->productSku()->associate($skuId);
            $item->save();
        }

        return $item;
    }

    /**
     * 从购物车中删除商品逻辑
     *
     * @param [type] $skuIds
     * @return void
     */
    public function remove($skuIds)
    {
        // 可以传单个 ID，也可以传 ID 数组
        if (! is_array($skuIds)) {
            $skuIds = [$skuIds];
        }

        Auth::user()->cartItems()->whereIn('product_sku_id', $skuIds)->delete();
    }
}
