<?php

namespace App\Models;

use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\Loggable;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

class BillingInvoice extends ClinicalModel
{
    use CompanyableTrait;
    use HasFactory;
    use Loggable;
    use Searchable;
    use SoftDeletes;
    use UniqueUndeletedTrait;
    use ValidatingTrait;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ISSUED = 'issued';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'billing_invoices';

    protected $injectUniqueIdentifier = true;

    protected $fillable = [
        'invoice_number',
        'patient_id',
        'opd_visit_id',
        'appointment_id',
        'status',
        'subtotal',
        'amount_paid',
        'balance',
        'issued_at',
        'notes',
        'company_id',
        'created_by',
    ];

    protected $casts = [
        'patient_id' => 'integer',
        'opd_visit_id' => 'integer',
        'appointment_id' => 'integer',
        'subtotal' => 'float',
        'amount_paid' => 'float',
        'balance' => 'float',
        'issued_at' => 'datetime',
        'company_id' => 'integer',
        'created_by' => 'integer',
    ];

    protected $rules = [
        'invoice_number' => 'required|max:20|unique_undeleted:billing_invoices,invoice_number',
        'patient_id' => 'required|integer|exists_clinical:patients,id',
        'opd_visit_id' => 'nullable|integer|exists_clinical:opd_visits,id',
        'appointment_id' => 'nullable|integer|exists_clinical:appointments,id',
        'status' => 'required|in:draft,issued,partial,paid,cancelled',
        'subtotal' => 'numeric|min:0',
        'amount_paid' => 'numeric|min:0',
        'balance' => 'numeric|min:0',
        'issued_at' => 'nullable|date',
        'notes' => 'nullable|string',
        'company_id' => 'numeric|nullable|exists:companies,id',
    ];

    protected $searchableAttributes = [
        'invoice_number',
        'notes',
    ];

    protected $searchableRelations = [
        'patient' => ['full_name', 'patient_number'],
    ];

    public function getDisplayNameAttribute(): string
    {
        return $this->invoice_number.' — '.($this->patient?->full_name ?? 'Unknown');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function opdVisit(): BelongsTo
    {
        return $this->belongsTo(OpdVisit::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(BillingLineItem::class)->orderBy('id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillingPayment::class)->orderByDesc('paid_at');
    }

    public static function generateNextInvoiceNumber(): string
    {
        $maxId = (int) static::withTrashed()->max('id');
        $next = $maxId + 1;

        return 'ACB-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => trans('admin/billing_invoices/table.status_draft'),
            self::STATUS_ISSUED => trans('admin/billing_invoices/table.status_issued'),
            self::STATUS_PARTIAL => trans('admin/billing_invoices/table.status_partial'),
            self::STATUS_PAID => trans('admin/billing_invoices/table.status_paid'),
            self::STATUS_CANCELLED => trans('admin/billing_invoices/table.status_cancelled'),
        ];
    }

    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->lineItems()->sum('line_total');
        $amountPaid = (float) $this->payments()->sum('amount');
        $balance = max(0, round($subtotal - $amountPaid, 2));

        $this->subtotal = $subtotal;
        $this->amount_paid = $amountPaid;
        $this->balance = $balance;

        if ($this->status === self::STATUS_CANCELLED) {
            return;
        }

        if ($subtotal <= 0) {
            $this->status = self::STATUS_DRAFT;
        } elseif ($amountPaid <= 0) {
            $this->status = $this->issued_at ? self::STATUS_ISSUED : self::STATUS_DRAFT;
        } elseif ($balance > 0) {
            $this->status = self::STATUS_PARTIAL;
        } else {
            $this->status = self::STATUS_PAID;
        }

        $this->save();
    }

    public function issue(): void
    {
        if (! $this->issued_at) {
            $this->issued_at = now();
        }
        $this->recalculateTotals();
    }
}
