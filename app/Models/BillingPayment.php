<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Watson\Validating\ValidatingTrait;

class BillingPayment extends ClinicalModel
{
    use ValidatingTrait;

    public const METHOD_CASH = 'cash';

    public const METHOD_GCASH = 'gcash';

    public const METHOD_CARD = 'card';

    public const METHOD_BANK = 'bank_transfer';

    public const METHOD_OTHER = 'other';

    protected $table = 'billing_payments';

    protected $fillable = [
        'billing_invoice_id',
        'amount',
        'payment_method',
        'reference',
        'paid_at',
        'received_by',
        'notes',
    ];

    protected $casts = [
        'billing_invoice_id' => 'integer',
        'amount' => 'float',
        'paid_at' => 'datetime',
        'received_by' => 'integer',
    ];

    protected $rules = [
        'billing_invoice_id' => 'required|integer|exists_clinical:billing_invoices,id',
        'amount' => 'required|numeric|min:0.01',
        'payment_method' => 'required|in:cash,gcash,card,bank_transfer,other',
        'reference' => 'nullable|max:100',
        'paid_at' => 'required|date',
        'received_by' => 'nullable|integer|exists:users,id',
        'notes' => 'nullable|string',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'billing_invoice_id');
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public static function paymentMethodOptions(): array
    {
        return [
            self::METHOD_CASH => trans('admin/billing_invoices/table.method_cash'),
            self::METHOD_GCASH => trans('admin/billing_invoices/table.method_gcash'),
            self::METHOD_CARD => trans('admin/billing_invoices/table.method_card'),
            self::METHOD_BANK => trans('admin/billing_invoices/table.method_bank'),
            self::METHOD_OTHER => trans('admin/billing_invoices/table.method_other'),
        ];
    }
}
