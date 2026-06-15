<?php

namespace App\Policies;

class LabOrderPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'lab_orders';
    }
}
