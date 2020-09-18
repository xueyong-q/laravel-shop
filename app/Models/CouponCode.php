<?php

namespace App\Models;

use App\Exceptions\CouponCodeUnavailableException;
use Carbon\Carbon;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CouponCode extends Model
{
    use DefaultDatetimeFormat;

    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENT = 'percent';

    public static $typeMap = [
        self::TYPE_FIXED => '固定金额',
        self::TYPE_PERCENT => '比例'
    ];

    protected $fillable = [
        'name', 'code', 'type', 'value', 'total', 'used', 'min_amount',
        'not_before', 'not_after', 'enabled'
    ];

    protected $casts = [
        'enabled' => 'boolean'
    ];

    protected $dates = ['not_before', 'not_after'];

    protected $appends = ['description'];

    /**
     * 生成优惠码
     *
     * @param integer $length
     * @return void
     */
    public static function findAvailableCode($length = 16)
    {
        do {
            $code = strtoupper(Str::random($length));
        } while (self::query()->where('code', $code)->exists());

        return $code;
    }

    public function getDescriptionAttribute()
    {
        $str = '';
        if ($this->min_amount > 0) {
            $str = '满 ' . str_replace('.00', '', $this->min_amount);
        }

        if ($this->type === self::TYPE_PERCENT) {
            return $str . ' 优惠 ' . str_replace('.00', '', $this->value) . ' %';
        }

        return $str . ' 减 ' . str_replace('.00', '', $this->value);
    }

    /**
     * 检查优惠券
     *
     * @param [type] $orderAmount
     * @return void
     */
    public function checkAvailable(User $user, $orderAmount = null)
    {
        if (!$this->enabled) {
            throw new CouponCodeUnavailableException('优惠券不存在');
        }

        if ($this->total - $this->used <= 0) {
            throw new CouponCodeUnavailableException('该优惠券已被兑完');
        }

        if ($this->not_before && $this->not_before->gt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券现在还不能使用');
        }

        if ($this->not_after && $this->not_after->lt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券已过期');
        }

        if (!is_null($orderAmount) && $orderAmount < $this->min_amount) {
            throw new CouponCodeUnavailableException('订单金额不满足该优惠券最低金额');
        }

        $used =  Order::where('user_id', $user->id)
            ->where('coupon_code_id', $this->id)
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->whereNull('paid_at')
                        ->where('closed', false);
                })->orWhere(function ($query) {
                    $query->whereNotNull('paid_at')
                        ->where('refund_status', '!=', Order::REFUND_STATUS_SUCCESS);
                });
            })->exists();

        if ($used) {
            throw new CouponCodeUnavailableException('你已经使用过这张优惠券了');
        }
    }

    /**
     * 计算优惠后的价格
     *
     * @param [type] $orderAmount
     * @return void
     */
    public function getAdjustedPrice($orderAmount)
    {
        if ($this->type === self::TYPE_FIXED) {
            return max(0.01, $orderAmount - $this->value);
        }

        return number_format($orderAmount * (100 - $this->value) / 100, 2, '.', '');
    }

    /**
     * 更新优惠券使用量
     *
     * @param boolean $increase
     * @return void
     */
    public function changeUsed($increase = true)
    {
        // 传入 true 代表新增用量，否则是减少用量
        if ($increase) {
            // 检查当前用量是否已经超过总量
            return $this->where('id', $this->id)->where('used', '<', $this->total)->increment('used');
        } else {
            return $this->decrement('used');
        }
    }
}
