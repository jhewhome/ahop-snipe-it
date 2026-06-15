<?php

namespace App\Policies;

class BillingInvoicePolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'billing_invoices';
    }
}
