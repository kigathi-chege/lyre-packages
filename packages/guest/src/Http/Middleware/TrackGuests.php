<?php

namespace Lyre\Guest\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Lyre\Guest\Actions\CreateGuest;
use Lyre\Guest\Models\Guest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class TrackGuests
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && (!property_exists(auth()->user(), 'is_guest') || ! auth()->user()->is_guest)) {
            Cookie::queue(Cookie::forget('guest_uuid'));
            Session::forget('guest_uuid');
            return $next($request);
        }

        $guestUuid = retrieve_guest_uuid($request);
        if (!Str::isUuid($guestUuid)) {
            // Invalid UUID, create new guest
            $guest = app(CreateGuest::class)();
            $request->attributes->set('guest', $guest);
            $guestUuid = $guest->uuid;
        } else {
            // Valid UUID, try to retrieve existing guest
            $guest = Guest::where('uuid', $guestUuid)->first();
            if ($guest) {
                $request->attributes->set('guest', $guest);
            } else {
                // UUID doesn't exist, create new guest
                $guest = app(CreateGuest::class)();
                $request->attributes->set('guest', $guest);
                $guestUuid = $guest->uuid;
            }
        }

        Session::put('guest_uuid', $guestUuid);

        $response = $next($request);
        $response->headers->set('X-Guest-UUID', $guestUuid);

        // Set the cookie in the response headers for beacon package
        $cookie = cookie('guest_uuid', $guestUuid, 60 * 24 * 30, '/', config('session.domain'));
        $response->headers->set('Set-Cookie', $cookie->__toString());

        return $response;
    }
}
