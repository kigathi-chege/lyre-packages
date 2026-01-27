<?php

return [
    'route_prefix' => 'api',
    'default_currency' => 'USD',
    'default_payment_term' => 'prepaid',
    'stock' => [
        'decrease_on' => 'paid', // paid|confirmed
        'restore_on_cancel' => true,
    ],
    'events' => [
        'emit_order_events' => true,
    ],

    'tax' => [
        'enabled' => false,
        'rate_percent' => 0,
    ],

    'payment_terms' => [
        // reference => behavior
        'prepaid' => 'require_payment_before_fulfillment',
        'cod' => 'fulfillment_before_payment',
        'postpaid_net30' => 'fulfillment_before_payment',
        'deposit_balance' => 'partial_payment_then_fulfillment',
        'subscription' => 'recurring',
    ],
];


