<?php

namespace App\Models;

use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\Loggable;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

class Patient extends SnipeModel
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
        'bhc_id',
        'full_name',
        'sex',
        'birthdate',
        'contact_number',
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
        'bhc_id' => 'required|max:20|unique_undeleted:patients,bhc_id',
        'full_name' => 'required|max:255',
        'sex' => 'required|in:M,F',
        'birthdate' => 'required|date',
        'contact_number' => 'nullable|max:30',
        'notes' => 'nullable|string',
        'company_id' => 'numeric|nullable|exists:companies,id',
    ];

    protected $searchableAttributes = [
        'bhc_id',
        'full_name',
        'contact_number',
    ];

    public function getDisplayNameAttribute(): string
    {
        return $this->full_name.' ('.$this->bhc_id.')';
    }

    /**
     * Generate the next patient ID (e.g. BHC-000001).
     */
    public static function generateNextBhcId(): string
    {
        $maxId = (int) static::withTrashed()->max('id');
        $next = $maxId + 1;

        return 'BHC-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
