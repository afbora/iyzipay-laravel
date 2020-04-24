<?php

namespace Afbora\IyzipayLaravel\Models;

use Afbora\IyzipayLaravel\StorableClasses\Plan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{

    protected $dates = [
        'next_charge_at',
        'canceled_at',
        'created_at',
        'updated_at'
    ];

    public function scopeActive($query)
    {
        return $query->whereNull('canceled_at')
            ->where('next_charge_at', '>=', Carbon::now());
    }

    public function scopeNotPaid($query)
    {
        return $query->whereNull('canceled_at')
            ->where('next_charge_at', '<', Carbon::now());
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(config('iyzipay.billableModel'), 'billable_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function cancel(): Subscription
    {
        $this->canceled_at = Carbon::now();
        $this->save();

        return $this;
    }

    public function canceled(): bool
    {
        return !empty($this->canceled_at);
    }

    public function setPlanAttribute($value)
    {
        $this->attributes['plan'] = (string)$value;
    }

    public function getPlanAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return (new \JsonMapper())->map(json_decode($value), new Plan());
    }
}
