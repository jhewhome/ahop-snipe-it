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

class Patient extends ClinicalModel
{
    use CompanyableTrait;
    use HasFactory;
    use Loggable;
    use Searchable;
    use SoftDeletes;
    use UniqueUndeletedTrait;
    use ValidatingTrait;

    protected $table = 'patients';

    protected $injectUniqueIdentifier = true;

    protected $fillable = [
        'patient_number',
        'full_name',
        'sex',
        'birthdate',
        'contact_number',
        'email',
        'allergies',
        'problem_list',
        'notes',
        'company_id',
        'created_by',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'company_id' => 'integer',
        'created_by' => 'integer',
    ];

    protected $rules = [
        'patient_number' => 'required|max:20|unique_undeleted:patients,patient_number',
        'full_name' => 'required|max:255',
        'sex' => 'required|in:M,F',
        'birthdate' => 'required|date',
        'contact_number' => 'nullable|max:30',
        'email' => 'nullable|email|max:150',
        'allergies' => 'nullable|string',
        'problem_list' => 'nullable|string',
        'notes' => 'nullable|string',
        'company_id' => 'numeric|nullable|exists:companies,id',
    ];

    protected $searchableAttributes = [
        'patient_number',
        'full_name',
        'contact_number',
        'email',
    ];

    public function getDisplayNameAttribute(): string
    {
        return $this->full_name.' ('.$this->patient_number.')';
    }

    /**
     * Generate the next AgilityCare patient number (e.g. AC-000001).
     */
    public static function generateNextPatientNumber(): string
    {
        $maxId = (int) static::withTrashed()->max('id');
        $next = $maxId + 1;

        return 'AC-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function opdVisits(): HasMany
    {
        return $this->hasMany(OpdVisit::class)->orderByDesc('visit_date');
    }

    public function labOrders(): HasMany
    {
        return $this->hasMany(LabOrder::class)->orderByDesc('ordered_at');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class)->orderByDesc('scheduled_at');
    }

    public function billingInvoices(): HasMany
    {
        return $this->hasMany(BillingInvoice::class)->orderByDesc('issued_at');
    }
}
