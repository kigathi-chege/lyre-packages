<?php

namespace Lyre\Commerce\Models;

use Lyre\Model;

class Location extends Model
{
    protected $fillable = [
        'name', 'latitude', 'longitude', 'address', 'delivery_fee',
    ];

    public function shippingAddresses()
    {
        return $this->hasMany(ShippingAddress::class);
    }
}


