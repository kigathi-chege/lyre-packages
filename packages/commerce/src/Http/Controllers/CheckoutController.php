<?php

namespace Lyre\Commerce\Http\Controllers;

use Illuminate\Http\Request;
use Lyre\Commerce\Http\Requests\StoreCheckoutConfirmShippingRequest;
use Lyre\Commerce\Http\Requests\StoreCheckoutPayRequest;
use Lyre\Commerce\Services\CheckoutService;
use Lyre\Controller;

class CheckoutController extends Controller
{
    public function __construct(protected CheckoutService $checkout)
    {
        parent::__construct(['table' => 'orders', 'model' => \Lyre\Commerce\Models\Order::class], app()->make(\Lyre\Commerce\Repositories\Contracts\OrderRepositoryInterface::class));
    }

    public function confirmShipping(StoreCheckoutConfirmShippingRequest $request)
    {
        $result = $this->checkout->confirmShipping($request->validated());
        return __response(true, 'Shipping confirmed', $result, get_response_code('update-order'));
    }

    public function confirmOrder(Request $request)
    {
        $result = $this->checkout->confirmOrder($request->get('order_id'));
        return __response(true, 'Order confirmed', $result, get_response_code('update-order'));
    }

    public function invoice(Request $request)
    {
        $result = $this->checkout->invoice($request->get('order_id'));
        return __response(true, 'Invoice created', $result, get_response_code('update-order'));
    }

    public function pay(StoreCheckoutPayRequest $request)
    {
        $validated = $request->validated();
        $result = $this->checkout->pay($validated['order_id'], $validated);
        return __response(true, 'Payment processed', $result, get_response_code('update-order'));
    }
}


