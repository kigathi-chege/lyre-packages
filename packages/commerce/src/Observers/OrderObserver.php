<?php

namespace Lyre\Commerce\Observers;

use Lyre\Commerce\Events\OrderConfirmed;
use Lyre\Commerce\Events\OrderInvoiced;
use Lyre\Commerce\Events\OrderPaid;
use Lyre\Commerce\Events\OrderReadyForFulfillment;
use Lyre\Commerce\Events\OrderFulfilled;
use Lyre\Commerce\Models\Order;
use Lyre\Observer;

class OrderObserver extends Observer
{
    public function updated($order): void
    {
        if ($order->wasChanged('status')) {
            $oldStatus = $order->getOriginal('status');
            $newStatus = $order->status;

            if ($newStatus === 'confirmed' && $oldStatus !== 'confirmed') {
                event(new OrderConfirmed($order));
            }

            if ($newStatus === 'invoiced' && $oldStatus !== 'invoiced') {
                event(new OrderInvoiced($order));
            }

            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                event(new OrderPaid($order));
            }

            if ($newStatus === 'ready_for_fulfillment' && $oldStatus !== 'ready_for_fulfillment') {
                event(new OrderReadyForFulfillment($order));
            }

            if ($newStatus === 'fulfilled' && $oldStatus !== 'fulfilled') {
                event(new OrderFulfilled($order));
            }
        }

        parent::updated($order);
    }
}
