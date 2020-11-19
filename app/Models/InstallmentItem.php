<?php

namespace App\Models;

use Brick\Math\BigDecimal;
use Brick\Math\BigNumber;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class InstallmentItem extends Model
{
    const REFUND_STATUS_PENDING = 'pending';
    const REFUND_STATUS_PROCESSING = 'processing';
    const REFUND_STATUS_SUCCESS = 'success';
    const REFUND_STATUS_FAILED = 'failed';

    public static $refundStatusMap = [
        self::REFUND_STATUS_PENDING => '未退款',
        self::REFUND_STATUS_PROCESSING => '退款中',
        self::REFUND_STATUS_SUCCESS => '退款成功',
        self::REFUND_STATUS_FAILED => '退款失败',
    ];

    protected $fillable = [
        'sequence', 'base', 'fee', 'fine', 'due_date', 'paid_at',
        'payment_method', 'payment_no', 'refund_status',
    ];

    protected $dates = ['due_date', 'paid_at'];

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }

    /**
     * 创建一个访问器，返回当前还款计划需还款的总金额
     *
     * @return void
     */
    public function getTotalAttribute()
    {
        // 使用 Brick\Math 扩展来处理金额
        $total = BigDecimal::of($this->base)->plus($this->fine);
        if (!is_null($this->fine)) {
            $total->plus($this->fine);
        }

        return format_number((string) $total);
    }

    /**
     * 创建一个访问器，返回当前还款计划是否已经逾期
     *
     * @return void
     */
    public function getIsOverdueAttribute()
    {
        return Carbon::now()->gt($this->due_date);
    }
}
