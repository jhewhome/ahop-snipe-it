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

class LabOrder extends ClinicalModel
{
    use CompanyableTrait;
    use HasFactory;
    use Loggable;
    use Searchable;
    use SoftDeletes;
    use UniqueUndeletedTrait;
    use ValidatingTrait;

    public const STATUS_ORDERED = 'ordered';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'lab_orders';

    protected $injectUniqueIdentifier = true;

    protected $fillable = [
        'order_number',
        'patient_id',
        'opd_visit_id',
        'ordered_by',
        'test_panel',
        'status',
        'priority',
        'clinical_notes',
        'ordered_at',
        'completed_at',
        'company_id',
        'created_by',
    ];

    protected $casts = [
        'patient_id' => 'integer',
        'opd_visit_id' => 'integer',
        'ordered_by' => 'integer',
        'ordered_at' => 'datetime',
        'completed_at' => 'datetime',
        'company_id' => 'integer',
        'created_by' => 'integer',
    ];

    protected $rules = [
        'order_number' => 'required|max:20|unique_undeleted:lab_orders,order_number',
        'patient_id' => 'required|integer|exists_clinical:patients,id',
        'opd_visit_id' => 'nullable|integer|exists_clinical:opd_visits,id',
        'ordered_by' => 'nullable|integer|exists:users,id',
        'test_panel' => 'required|max:100',
        'status' => 'required|in:ordered,in_progress,completed,cancelled',
        'priority' => 'required|in:routine,urgent',
        'clinical_notes' => 'nullable|string',
        'ordered_at' => 'required|date',
        'completed_at' => 'nullable|date',
        'company_id' => 'numeric|nullable|exists:companies,id',
    ];

    protected $searchableAttributes = [
        'order_number',
        'test_panel',
        'clinical_notes',
    ];

    protected $searchableRelations = [
        'patient' => ['full_name', 'patient_number'],
    ];

    public function getDisplayNameAttribute(): string
    {
        return $this->order_number.' — '.$this->test_panel;
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function opdVisit(): BelongsTo
    {
        return $this->belongsTo(OpdVisit::class);
    }

    public function orderedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function results(): HasMany
    {
        return $this->hasMany(LabResult::class)->orderByDesc('result_at');
    }

    public static function generateNextOrderNumber(): string
    {
        $maxId = (int) static::withTrashed()->max('id');
        $next = $maxId + 1;

        return 'ACL-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ORDERED => trans('admin/lab_orders/table.status_ordered'),
            self::STATUS_IN_PROGRESS => trans('admin/lab_orders/table.status_in_progress'),
            self::STATUS_COMPLETED => trans('admin/lab_orders/table.status_completed'),
            self::STATUS_CANCELLED => trans('admin/lab_orders/table.status_cancelled'),
        ];
    }

    public static function priorityOptions(): array
    {
        return [
            'routine' => trans('admin/lab_orders/table.priority_routine'),
            'urgent' => trans('admin/lab_orders/table.priority_urgent'),
        ];
    }

    public static function testPanelOptions(): array
    {
        return [
            'CBC' => 'Complete Blood Count (CBC)',
            'BMP' => 'Basic Metabolic Panel (BMP)',
            'LIPID' => 'Lipid Panel',
            'URINALYSIS' => 'Urinalysis',
            'HBA1C' => 'HbA1c',
            'LFT' => 'Liver Function Tests',
            'TFT' => 'Thyroid Function Tests',
            'OTHER' => 'Other / Custom',
        ];
    }

    public function markCompletedIfHasResults(): void
    {
        if ($this->results()->count() > 0 && $this->status !== self::STATUS_CANCELLED) {
            $this->status = self::STATUS_COMPLETED;
            $this->completed_at = $this->completed_at ?? now();
            $this->save();
        }
    }
}
