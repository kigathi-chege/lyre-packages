<?php

namespace Lyre\Commerce\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lyre\Commerce\Models\Order;

class OrderInvoiced
{
    use Dispatchable, SerializesModels;

    public function __construct(public Order $order) {}
}

