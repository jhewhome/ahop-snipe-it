<?php

return [
    'create' => [
        'success' => 'Invoice created successfully.',
    ],
    'update' => [
        'success' => 'Invoice updated successfully.',
    ],
    'delete' => [
        'success' => 'Invoice deleted.',
        'error' => 'Could not delete invoice.',
        'confirm' => 'Delete this invoice?',
    ],
    'line_item' => [
        'success' => 'Charge added.',
        'deleted' => 'Charge removed.',
    ],
    'payment' => [
        'success' => 'Payment recorded.',
        'deleted' => 'Payment removed.',
    ],
    'cancelled_locked' => 'This invoice is cancelled and cannot be changed.',
];
