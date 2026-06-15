<?php

namespace App\Models;

use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\Loggable;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

class Appointment extends ClinicalModel
{
    use CompanyableTrait;
    use HasFactory;
    use Loggable;
    use Searchable;
    use SoftDeletes;
    use UniqueUndeletedTrait;
    use ValidatingTrait;

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_CHECKED_IN = 'checked_in';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_NO_SHOW = 'no_show';

    protected $table = 'appointments';

    protected $injectUniqueIdentifier = true;

    protected $fillable = [
        'appointment_number',
        'patient_id',
        'physician_id',
        'scheduled_at',
        'duration_minutes',
        'visit_type',
        'status',
        'reason',
        'notes',
        'reminder_sent_at',
        'opd_visit_id',
        'company_id',
        'created_by',
    ];

    protected $casts = [
        'patient_id' => 'integer',
        'physician_id' => 'integer',
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
        'reminder_sent_at' => 'datetime',
        'opd_visit_id' => 'integer',
        'company_id' => 'integer',
        'created_by' => 'integer',
    ];

    protected $rules = [
        'appointment_number' => 'required|max:20|unique_undeleted:appointments,appointment_number',
        'patient_id' => 'required|integer|exists_clinical:patients,id',
        'physician_id' => 'nullable|integer|exists:users,id',
        'scheduled_at' => 'required|date',
        'duration_minutes' => 'required|integer|min:5|max:480',
        'visit_type' => 'required|in:initial,follow_up,walk_in',
        'status' => 'required|in:scheduled,checked_in,completed,cancelled,no_show',
        'reason' => 'nullable|string',
        'notes' => 'nullable|string',
        'opd_visit_id' => 'nullable|integer|exists_clinical:opd_visits,id',
        'company_id' => 'numeric|nullable|exists:companies,id',
    ];

    protected $searchableAttributes = [
        'appointment_number',
        'reason',
        'notes',
    ];

    protected $searchableRelations = [
        'patient' => ['full_name', 'patient_number'],
    ];

    public function getDisplayNameAttribute(): string
    {
        return $this->appointment_number.' — '.($this->patient?->full_name ?? 'Unknown');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function physician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'physician_id');
    }

    public function opdVisit(): BelongsTo
    {
        return $this->belongsTo(OpdVisit::class, 'opd_visit_id');
    }

    public function activeBillingInvoice(): HasOne
    {
        return $this->hasOne(BillingInvoice::class, 'appointment_id')
            ->where('status', '!=', BillingInvoice::STATUS_CANCELLED)
            ->latestOfMany();
    }

    /**
     * Invoice linked to this appointment or its OPD visit (after check-in).
     */
    public function resolvedBillingInvoice(): ?BillingInvoice
    {
        $this->loadMissing(['opdVisit.activeBillingInvoice', 'activeBillingInvoice']);

        return $this->opdVisit?->activeBillingInvoice ?? $this->activeBillingInvoice;
    }

    public function canCheckIn(): bool
    {
        return in_array($this->status, [self::STATUS_SCHEDULED], true)
            && $this->opd_visit_id === null;
    }

    public static function generateNextAppointmentNumber(): string
    {
        $maxId = (int) static::withTrashed()->max('id');
        $next = $maxId + 1;

        return 'ACA-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_SCHEDULED => trans('admin/appointments/table.status_scheduled'),
            self::STATUS_CHECKED_IN => trans('admin/appointments/table.status_checked_in'),
            self::STATUS_COMPLETED => trans('admin/appointments/table.status_completed'),
            self::STATUS_CANCELLED => trans('admin/appointments/table.status_cancelled'),
            self::STATUS_NO_SHOW => trans('admin/appointments/table.status_no_show'),
        ];
    }

    /**
     * Status choices when scheduling a new appointment.
     *
     * @return array<string, string>
     */
    public static function creatableStatusOptions(): array
    {
        return [
            self::STATUS_SCHEDULED => trans('admin/appointments/table.status_scheduled'),
        ];
    }

    /**
     * Status choices allowed when editing an existing appointment.
     *
     * @return array<string, string>
     */
    public function editableStatusOptions(): array
    {
        $all = self::statusOptions();

        $keys = match ($this->status) {
            self::STATUS_SCHEDULED => [
                self::STATUS_SCHEDULED,
                self::STATUS_CANCELLED,
                self::STATUS_NO_SHOW,
            ],
            self::STATUS_CHECKED_IN => [
                self::STATUS_CHECKED_IN,
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
            ],
            self::STATUS_COMPLETED => [self::STATUS_COMPLETED],
            self::STATUS_CANCELLED => [
                self::STATUS_CANCELLED,
                self::STATUS_SCHEDULED,
            ],
            self::STATUS_NO_SHOW => [
                self::STATUS_NO_SHOW,
                self::STATUS_SCHEDULED,
            ],
            default => array_keys($all),
        };

        return array_intersect_key($all, array_flip($keys));
    }

    public static function visitTypeOptions(): array
    {
        return OpdVisit::visitTypeOptions();
    }
}
