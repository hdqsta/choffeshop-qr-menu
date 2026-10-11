<?php

namespace App\Models;

use Database\Factories\ModifierOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['modifier_group_id', 'name', 'price_delta', 'is_available', 'sort_order'])]
class ModifierOption extends Model
{
    /** @use HasFactory<ModifierOptionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_delta' => 'integer',
            'is_available' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ModifierGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ModifierGroup::class, 'modifier_group_id');
    }

    /**
     * Scope a query to only include available options.
     *
     * @param  Builder<ModifierOption>  $query
     * @return Builder<ModifierOption>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope a query to sort options.
     *
     * @param  Builder<ModifierOption>  $query
     * @return Builder<ModifierOption>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get formatted price delta attribute.
     */
    public function getFormattedPriceDeltaAttribute(): string
    {
        if ($this->price_delta === 0) {
            return 'Gratis';
        }

        return ($this->price_delta > 0 ? '+ Rp ' : '- Rp ').number_format(abs($this->price_delta), 0, ',', '.');
    }
}
