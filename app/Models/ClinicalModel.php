<?php

namespace App\Models;

use App\Models\Traits\UsesClinicalDatabase;

/**
 * Base model for AHOP clinical data stored on PostgreSQL (dual-database mode).
 */
abstract class ClinicalModel extends SnipeModel
{
    use UsesClinicalDatabase;
}
