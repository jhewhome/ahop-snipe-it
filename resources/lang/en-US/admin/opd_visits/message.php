<?php

return [
    'does_not_exist' => 'OPD visit does not exist.',
    'create' => [
        'error' => 'OPD visit was not created, please try again.',
        'success' => 'OPD visit created successfully.',
    ],
    'update' => [
        'error' => 'OPD visit was not updated, please try again.',
        'success' => 'OPD visit updated successfully.',
    ],
    'delete' => [
        'confirm' => 'Are you sure you wish to delete this OPD visit record?',
        'error' => 'There was an issue deleting the OPD visit. Please try again.',
        'success' => 'The OPD visit was deleted successfully.',
    ],
    'billing' => [
        'created' => 'Invoice created with default consultation charge. Add more charges or record payment.',
        'opened' => 'Opened existing invoice for this visit.',
        'failed' => 'Could not create billing invoice for this visit.',
        'confirm' => 'Create a billing invoice for this visit with the default consultation fee?',
        'auto_created' => 'Consultation invoice was created automatically.',
        'auto_failed' => 'Visit saved, but auto-billing failed: :error',
    ],
    'lab_order' => [
        'created' => 'Lab order created: :panel',
        'error' => 'Could not create lab order for this visit.',
    ],
    'status' => [
        'updated' => 'Visit status updated.',
    ],
];
