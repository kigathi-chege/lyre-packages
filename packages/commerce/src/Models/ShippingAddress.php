<?php

namespace Lyre\Commerce\Models;

use Lyre\Model;

class ShippingAddress extends Model
{
    protected $fillable = [
        'user_id', 'location_id', 'delivery_method', 'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country', 'contact_name', 'contact_phone', 'is_default',
    ];

    public function user()
    {
        $userClass = get_user_model();
        return $this->belongsTo($userClass);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}


