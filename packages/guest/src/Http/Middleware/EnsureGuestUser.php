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

class EnsureGuestUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && (!property_exists(auth()->user(), 'is_guest') || ! auth()->user()->is_guest)) {
            Cookie::queue(Cookie::forget('guest_uuid'));
            Session::forget('guest_uuid');

            return $next($request);
        }

        // Get the guest from TrackGuests middleware (should already be created)
        $guest = $request->attributes->get('guest');
        $guestUuid = $request->header('X-Guest-UUID') ?? $guest?->uuid;

        // If we have a guest from TrackGuests, authenticate it
        if ($guest && $guestUuid) {
            $this->authenticateUserForGuest($guest);
        } else {
            // Fallback: if TrackGuests didn't create a guest, create one here
            $guestUuid = retrieve_guest_uuid($request);

            if (!Str::isUuid($guestUuid)) {
                $guestUuid = $this->createGuestUser();
            } else {
                // Only query if we have a valid UUID
                $existingGuest = Guest::where('uuid', $guestUuid)->first();
                if ($existingGuest) {
                    $guestUuid = $this->authenticateUserForGuest($existingGuest);
                } else {
                    $guestUuid = $this->createGuestUser();
                }
            }
        }

        $response = $next($request);
        $response->headers->set('X-Guest-UUID', $guestUuid);

        \Lyre\Http\Middleware\SetCurrentTenant::setCurrentTenant(auth()->user());

        return $response;
    }

    public function createGuestUser()
    {
        $guest = app(CreateGuest::class)();

        $guestUuid = $guest->uuid;
        Cookie::queue('guest_uuid', $guestUuid, 60 * 24 * 30);

        return $this->authenticateUserForGuest($guest);
    }

    public function retrieveGuestUser($guestUuid)
    {
        // This method is no longer needed as we handle it inline
        // Keeping for backward compatibility but should not be called
        $guest = Guest::where('uuid', $guestUuid)->first();

        if (!$guest) {
            return $this->createGuestUser();
        }

        return $this->authenticateUserForGuest($guest);
    }

    public function authenticateUserForGuest(Guest $guest)
    {
        $authUser = $guest->user;

        if ($authUser) {
            auth()->login($authUser);
            return $guest->uuid;
        }

        $host = app_url_host();
        $guestUuid = $guest->uuid;
        $uniqueId = explode('-', $guestUuid)[0];
        $guestEmail = "{$uniqueId}@{$host}";

        $authUser = \App\Models\User::create([
            'email' => $guestEmail,
            'name' => "Guest {$uniqueId}",
            'password' => bcrypt(config('lyre.password')),
            'is_guest' => true
        ]);

        $guest->update([
            'user_id' => $authUser->id
        ]);

        auth()->login($authUser);

        return $guestUuid;
    }
}
