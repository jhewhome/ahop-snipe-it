<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Watson\Validating\ValidatingTrait;

class BillingLineItem extends ClinicalModel
{
    use ValidatingTrait;

    protected $table = 'billing_line_items';

    protected $fillable = [
        'billing_invoice_id',
        'billable_service_id',
        'description',
        'quantity',
        'unit_amount',
        'line_total',
    ];

    protected $casts = [
        'billing_invoice_id' => 'integer',
        'billable_service_id' => 'integer',
        'quantity' => 'integer',
        'unit_amount' => 'float',
        'line_total' => 'float',
    ];

    protected $rules = [
        'billing_invoice_id' => 'required|integer|exists_clinical:billing_invoices,id',
        'billable_service_id' => 'nullable|integer|exists_clinical:billable_services,id',
        'description' => 'required|max:255',
        'quantity' => 'required|integer|min:1|max:999',
        'unit_amount' => 'required|numeric|min:0',
        'line_total' => 'required|numeric|min:0',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'billing_invoice_id');
    }

    public function billableService(): BelongsTo
    {
        return $this->belongsTo(BillableService::class);
    }

    public static function computeLineTotal(int $quantity, float $unitAmount): float
    {
        return round($quantity * $unitAmount, 2);
    }
}
