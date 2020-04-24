<?php

namespace Afbora\IyzipayLaravel\Models;

use Afbora\IyzipayLaravel\Exceptions\Transaction\TransactionVoidException;
use Afbora\IyzipayLaravel\IyzipayLaravelFacade as IyzipayLaravel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'amount',
        'products',
        'refunds',
        'iyzipay_key',
        'voided_at',
        'currency'
    ];

    protected $casts = [
        'products' => 'array',
        'refunds' => 'array'
    ];

    protected $dates = [
        'voided_at'
    ];

    protected $appends = [
        'refunded_amount'
    ];

    public function billable(): BelongsTo
    {
        return $this->belongsTo(config('iyzipay.billableModel'), 'billable_id');
    }

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function void(): Transaction
    {
        if ($this->created_at < Carbon::today()->startOfDay()) {
            throw new TransactionVoidException('This transaction cannot be voided.');
        }

        return IyzipayLaravel::void($this);
    }

    public function refund(): Transaction
    {
        return IyzipayLaravel::void($this);
    }

    public function getRefundedAmountAttribute()
    {
        if (empty($this->refunds) === true) {
            return 0;
        }

        return array_sum(array_column($this->refunds, 'amount'));
    }
}
