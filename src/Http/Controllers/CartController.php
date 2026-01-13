<?php

namespace Lyre\Commerce\Http\Controllers;

use Lyre\Commerce\Http\Requests\StoreCartAddRequest;
use Lyre\Commerce\Http\Requests\StoreCartRemoveRequest;
use Lyre\Commerce\Http\Requests\StoreCartApplyCouponRequest;
use Lyre\Commerce\Services\CartService;
use Lyre\Controller;

class CartController extends Controller
{
    public function __construct(protected CartService $cart)
    {
        // No model binding for domain controller
        parent::__construct(['table' => 'orders', 'model' => \Lyre\Commerce\Models\Order::class], app()->make(\Lyre\Commerce\Repositories\Contracts\OrderRepositoryInterface::class));
    }

    public function add(StoreCartAddRequest $request)
    {
        $result = $this->cart->add($request->validated());
        return __response(true, 'Cart updated', $result, get_response_code('update-orders'));
    }

    public function remove(StoreCartRemoveRequest $request)
    {
        $result = $this->cart->remove($request->validated());
        return __response(true, 'Cart updated', $result, get_response_code('update-orders'));
    }

    public function summary()
    {
        $result = $this->cart->summary();
        return __response(true, 'Cart summary', $result, get_response_code('get-orders'));
    }

    public function applyCoupon(StoreCartApplyCouponRequest $request)
    {
        $result = $this->cart->applyCoupon($request->validated()['code']);
        return __response(true, 'Coupon applied', $result, get_response_code('update-orders'));
    }

    public function removeCoupon()
    {
        $result = $this->cart->removeCoupon();
        return __response(true, 'Coupon removed', $result, get_response_code('update-orders'));
    }
}
