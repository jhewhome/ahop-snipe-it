<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Watson\Validating\ValidatingTrait;

class LabResult extends ClinicalModel
{
    use HasFactory;
    use ValidatingTrait;

    protected $table = 'lab_results';

    protected $fillable = [
        'lab_order_id',
        'test_code',
        'test_name',
        'result_value',
        'unit',
        'reference_range',
        'flag',
        'result_at',
        'notes',
    ];

    protected $casts = [
        'lab_order_id' => 'integer',
        'result_at' => 'datetime',
    ];

    protected $rules = [
        'lab_order_id' => 'required|integer|exists_clinical:lab_orders,id',
        'test_code' => 'nullable|max:50',
        'test_name' => 'required|max:150',
        'result_value' => 'required|max:100',
        'unit' => 'nullable|max:30',
        'reference_range' => 'nullable|max:100',
        'flag' => 'nullable|in:normal,low,high,critical',
        'result_at' => 'required|date',
        'notes' => 'nullable|string',
    ];

    public function labOrder(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class);
    }

    public static function flagOptions(): array
    {
        return [
            'normal' => trans('admin/lab_orders/table.flag_normal'),
            'low' => trans('admin/lab_orders/table.flag_low'),
            'high' => trans('admin/lab_orders/table.flag_high'),
            'critical' => trans('admin/lab_orders/table.flag_critical'),
        ];
    }
}
