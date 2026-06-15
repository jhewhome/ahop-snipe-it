<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class BillableService extends ClinicalModel
{
    protected $table = 'billable_services';

    protected $fillable = [
        'code',
        'name',
        'category',
        'default_amount',
        'is_active',
    ];

    protected $casts = [
        'default_amount' => 'float',
        'is_active' => 'boolean',
    ];

    public function lineItems(): HasMany
    {
        return $this->hasMany(BillingLineItem::class);
    }

    public static function activeCatalog()
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();
    }
}
