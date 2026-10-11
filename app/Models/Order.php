<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'public_token',
    'order_number',
    'table_id',
    'notes',
    'total_amount',
    'payment_status',
    'order_status',
    'needs_review',
    'expires_at',
    'paid_at',
    'receipt_printed_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
            'payment_status' => PaymentStatus::class,
            'order_status' => OrderStatus::class,
            'needs_review' => 'boolean',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'receipt_printed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Table, $this>
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasOne<Payment, $this>
     */
    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    /**
     * Scope for Bar dashboard: only paid orders that are active.
     *
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    public function scopeForBar(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatus::Paid)
            ->whereIn('order_status', [OrderStatus::Confirmed, OrderStatus::Preparing])
            ->oldest();
    }

    /**
     * Scope for orders needing review by cashier.
     *
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    public function scopeNeedsReview(Builder $query): Builder
    {
        return $query->where('needs_review', true)->latest();
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Paid;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp '.number_format($this->total_amount, 0, ',', '.');
    }
}
