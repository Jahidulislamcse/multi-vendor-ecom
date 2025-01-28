<?php

return [

    'default' => env('PAYMENTS_TYPE', 'cash-in-hand'),

    'types' => [
        'cash-in-hand' => [
            'driver' => 'offline',
            'released' => 'payment-offline',
            'authorized' => 'payment-offline',
        ],
        'sslcommerz' => [
            'driver' => 'sslcommerz',
            'released' => 'payment-received',
            'authorized' => 'payment-received',
        ],
    ],

];
