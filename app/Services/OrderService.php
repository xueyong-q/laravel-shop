<?php

namespace App\Services;

use App\Exceptions\CouponCodeUnavailableException;
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseOrder;
use App\Models\CouponCode;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\User;
use App\Models\UserAddress;
use Carbon\Carbon;

class OrderService
{
    public function store(User $user, UserAddress $address, $remark, $items, CouponCode $couponCode = null)
    {
        // 如果传入了优惠券，则先检查是否可用
        if ($couponCode) {
            $couponCode->checkAvailable($user);
        }

        // 开启一个数据库事务
        $order = \DB::transaction(function () use ($user, $address, $remark, $items, $couponCode) {

            // 更新此地址的最后使用时间
            $address->update(['last_used_at' => Carbon::now()]);

            // 创建一个订单
            $order = new Order([
                'address' => [
                    'address' => $address->full_address,
                    'zip' => $address->zip,
                    'contact_name' => $address->contact_name,
                    'contact_phone' => $address->contact_phone
                ],
                'remark' => $remark,
                'total_amount' => 0,
            ]);

            // 订单关联到当前用户
            $order->user()->associate($user);
            // 写入数据库
            $order->save();

            $totalAmount = 0;

            // 遍历用户提交的 SKU
            foreach ($items as $item) {
                $sku = ProductSku::find($item['sku_id']);
                // 创建一个 OrderItem 并直接与当前订单关联
                $data = $order->items()->make([
                    'amount' => $item['amount'],
                    'price' => $sku->price,
                ]);
                $data->product()->associate($sku->product_id);
                $data->productSku()->associate($sku);
                $data->save();

                $totalAmount += $sku->price * $item['amount'];
                if ($sku->decreaseStock($item['amount']) <= 0) {
                    throw new InvalidRequestException('该商品库存不足');
                }
            }

            // 优惠券
            if ($couponCode) {
                // 检查是否符合优惠券规则
                $couponCode->checkAvailable($user, $totalAmount);
                // 把订单金额修改为优惠后的金额
                $totalAmount = $couponCode->getAdjustedPrice($totalAmount);
                // 将订单与优惠券关联
                $order->couponCode()->associate($couponCode);
                // 增加优惠券的用量，需判断返回值
                if ($couponCode->changeUsed() <= 0) {
                    throw new CouponCodeUnavailableException('该优惠券已被兑完');
                }
            }

            // 更新订单总金额
            $order->update(['total_amount' => $totalAmount]);

            // 将下单的商品从购物车中移除
            $skuIds = collect($items)->pluck('sku_id')->all();
            app(CartService::class)->remove($skuIds);

            return $order;
        });

        dispatch(new CloseOrder($order, config('app.order_ttl')));

        return $order;
    }

    /**
     * 众筹下单逻辑
     *
     * @param User $user
     * @param UserAddress $userAddress
     * @param ProductSku $productSku
     * @param [type] $amount
     * @return void
     */
    public function crowdfunding(User $user, UserAddress $userAddress, ProductSku $productSku, $amount)
    {
        // 开启事务
        $order = \DB::transaction(function () use ($amount, $productSku, $user, $userAddress) {
            // 更新地址最后使用时间
            $userAddress->update(['last_used_at' => Carbon::now()]);

            // 创建一个订单
            $order = new Order([
                'address' => [
                    'address' => $userAddress->full_address,
                    'zip' => $userAddress->zip,
                    'contact_name' => $userAddress->contact_name,
                    'contact_phone' => $userAddress->contact_phone
                ],
                'remark' => '',
                'total_amount' => $productSku->price * $amount,
            ]);

            // 订单关联到当前用户
            $order->user()->associate($user);
            // 写入数据库
            $order->save();

            // 创建一个新的订单项并与 SKU 关联
            $item = $order->items()->make([
                'amount' => $amount,
                'price' => $productSku->price
            ]);

            $item->product()->associate($productSku->product_id);
            $item->productSku()->associate($productSku);
            $item->save();

            // 扣减对应 SKU 库存
            if ($productSku->decreaseStock($amount) <= 0) {
                throw new InvalidRequestException('该商品库存不足');
            }

            return $order;
        });

        // 众筹结束时间减去当前时间得到剩余秒数
        $crowdfundingTtl = $productSku->product->crowdfunding->end_at->getTimestamp() - time();
        // 剩余秒数与默认订单关闭时间取较小值作为订单关闭时间
        dispatch(new CloseOrder($order, min(config('app.order_ttl'), $crowdfundingTtl)));

        return $order;
    }
}
