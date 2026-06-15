<?php

namespace App\Models;

use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\Loggable;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

class OpdVisit extends ClinicalModel
{
    use CompanyableTrait;
    use HasFactory;
    use Loggable;
    use Searchable;
    use SoftDeletes;
    use UniqueUndeletedTrait;
    use ValidatingTrait;

    public const TYPE_INITIAL = 'initial';

    public const TYPE_FOLLOW_UP = 'follow_up';

    public const TYPE_WALK_IN = 'walk_in';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'opd_visits';

    protected $injectUniqueIdentifier = true;

    protected $fillable = [
        'visit_number',
        'patient_id',
        'physician_id',
        'visit_date',
        'visit_type',
        'status',
        'chief_complaint',
        'blood_pressure',
        'pulse_rate',
        'temperature',
        'weight_kg',
        'height_cm',
        'assessment',
        'diagnosis',
        'rest_days',
        'med_cert_remarks',
        'company_id',
        'created_by',
    ];

    protected $casts = [
        'patient_id' => 'integer',
        'physician_id' => 'integer',
        'visit_date' => 'datetime',
        'pulse_rate' => 'integer',
        'temperature' => 'float',
        'weight_kg' => 'float',
        'height_cm' => 'float',
        'rest_days' => 'integer',
        'company_id' => 'integer',
        'created_by' => 'integer',
    ];

    protected $rules = [
        'visit_number' => 'required|max:20|unique_undeleted:opd_visits,visit_number',
        'patient_id' => 'required|integer|exists_clinical:patients,id',
        'physician_id' => 'nullable|integer|exists:users,id',
        'visit_date' => 'required|date',
        'visit_type' => 'required|in:initial,follow_up,walk_in',
        'status' => 'required|in:scheduled,in_progress,completed,cancelled',
        'chief_complaint' => 'nullable|string',
        'blood_pressure' => 'nullable|max:20',
        'pulse_rate' => 'nullable|integer|min:0|max:300',
        'temperature' => 'nullable|numeric|min:30|max:45',
        'weight_kg' => 'nullable|numeric|min:0|max:500',
        'height_cm' => 'nullable|numeric|min:0|max:300',
        'assessment' => 'nullable|string',
        'diagnosis' => 'nullable|string',
        'rest_days' => 'nullable|integer|min:0|max:365',
        'med_cert_remarks' => 'nullable|string',
        'company_id' => 'numeric|nullable|exists:companies,id',
    ];

    protected $searchableAttributes = [
        'visit_number',
        'chief_complaint',
        'diagnosis',
    ];

    protected $searchableRelations = [
        'patient' => ['full_name', 'patient_number'],
    ];

    public function getDisplayNameAttribute(): string
    {
        return $this->visit_number.' — '.($this->patient?->full_name ?? 'Unknown');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function physician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'physician_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function billingInvoices(): HasMany
    {
        return $this->hasMany(BillingInvoice::class);
    }

    public function activeBillingInvoice(): HasOne
    {
        return $this->hasOne(BillingInvoice::class, 'opd_visit_id')
            ->where('status', '!=', BillingInvoice::STATUS_CANCELLED)
            ->latestOfMany();
    }

    public function labOrders(): HasMany
    {
        return $this->hasMany(LabOrder::class);
    }

    public static function generateNextVisitNumber(): string
    {
        $maxId = (int) static::withTrashed()->max('id');
        $next = $maxId + 1;

        return 'ACV-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public static function visitTypeOptions(): array
    {
        return [
            self::TYPE_INITIAL => trans('admin/opd_visits/table.type_initial'),
            self::TYPE_FOLLOW_UP => trans('admin/opd_visits/table.type_follow_up'),
            self::TYPE_WALK_IN => trans('admin/opd_visits/table.type_walk_in'),
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_SCHEDULED => trans('admin/opd_visits/table.status_scheduled'),
            self::STATUS_IN_PROGRESS => trans('admin/opd_visits/table.status_in_progress'),
            self::STATUS_COMPLETED => trans('admin/opd_visits/table.status_completed'),
            self::STATUS_CANCELLED => trans('admin/opd_visits/table.status_cancelled'),
        ];
    }
}
