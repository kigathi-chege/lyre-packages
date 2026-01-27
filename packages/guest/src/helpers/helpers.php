<?php

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;

if (!function_exists('after_login')) {
    function after_login($user, $request = null)
    {
        logger("User logged in", ['user_id' => $user->id]);
    }
}

if (!function_exists('after_logout')) {
    function after_logout($user, $request = null)
    {
        // logger("User logged out", ['user_id' => $user->id]);
    }
}

if (!function_exists('after_register')) {
    function after_register($user, $request = null)
    {
        // logger("User registered", ['user_id' => $user->id]);
    }
}


if (!function_exists('retrieve_guest_uuid')) {
    function retrieve_guest_uuid(\Illuminate\Http\Request $request)
    {
        // Priority order: X-Guest-UUID header > guest_uuid cookie > query parameter
        $guestUuid = $request->header('X-Guest-UUID') ?? $request->cookie('guest_uuid') ?? $request->query('gU');

        // If we got it from cookie and it's encrypted, decrypt it
        if ($guestUuid && !\Illuminate\Support\Str::isUuid($guestUuid) && $request->cookie('guest_uuid')) {
            try {
                $raw = \Illuminate\Support\Facades\Crypt::decryptString($guestUuid);
                $parts = explode('|', $raw);
                $guestUuid = null;
                // Traverse backwards to find the first valid UUID (same logic as CreateGuest)
                for ($i = count($parts) - 1; $i >= 0; $i--) {
                    if (\Illuminate\Support\Str::isUuid($parts[$i])) {
                        $guestUuid = $parts[$i];
                        break;
                    }
                }
            } catch (\Exception $e) {
                logger('Invalid encrypted guest_uuid cookie', [$e->getMessage()]);
                $guestUuid = null;
            }
        }

        return $guestUuid;
    }
}
