<?php

use Illuminate\Support\Facades\Route;
use Lyre\Commerce\Http\Controllers as Controllers;

Route::prefix(config('commerce.route_prefix', 'api'))
    ->middleware([
        'api',
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        \Lyre\Guest\Http\Middleware\EnsureGuestUser::class
    ])
    ->group(function () {
        Route::apiResources([
            'locations' => Controllers\LocationController::class,
            'shippingaddresses' => Controllers\ShippingAddressController::class,
            'products' => Controllers\ProductController::class,
            'productvariants' => Controllers\ProductVariantController::class,
            'userproductvariants' => Controllers\UserProductVariantController::class,
            'productvariantprices' => Controllers\ProductVariantPriceController::class,
            'orders' => Controllers\OrderController::class,
            'orderitems' => Controllers\OrderItemController::class,
            'coupons' => Controllers\CouponController::class,
            'couponusages' => Controllers\CouponUsageController::class,
        ]);

        // Cart endpoints
        Route::post('cart/add', [Controllers\CartController::class, 'add']);
        Route::post('cart/remove', [Controllers\CartController::class, 'remove']);
        Route::get('cart/summary', [Controllers\CartController::class, 'summary']);
        Route::post('cart/apply-coupon', [Controllers\CartController::class, 'applyCoupon']);
        Route::post('cart/remove-coupon', [Controllers\CartController::class, 'removeCoupon']);

        // Checkout endpoints
        Route::post('checkout/confirm-shipping', [Controllers\CheckoutController::class, 'confirmShipping']);
        Route::post('checkout/confirm-order', [Controllers\CheckoutController::class, 'confirmOrder']);
        Route::post('checkout/invoice', [Controllers\CheckoutController::class, 'invoice']);
        Route::post('checkout/pay', [Controllers\CheckoutController::class, 'pay']);
    });
