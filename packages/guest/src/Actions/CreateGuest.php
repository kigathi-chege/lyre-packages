<?php

namespace Lyre\Guest\Actions;

use Lyre\Guest\Models\Guest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use Stevebauman\Location\Facades\Location;

class CreateGuest
{
    public function __invoke()
    {
        $uuid = Cookie::get('guest_uuid');

        if (!$uuid && $guest = request()->attributes->get('guest')) {
            return $guest;
        }

        if ($uuid && !\Illuminate\Support\Str::isUuid($uuid)) {
            $raw = \Illuminate\Support\Facades\Crypt::decryptString($uuid);
            $parts = explode('|', $raw);
            $uuid = null;
            // Traverse backwards to find the first valid UUID
            for ($i = count($parts) - 1; $i >= 0; $i--) {
                if (\Illuminate\Support\Str::isUuid($parts[$i])) {
                    logger("UUID: " . $parts[$i]);
                    $uuid = $parts[$i];
                    break;
                }
            }
        }

        if (!$uuid) {
            $uuid = (string) Str::uuid();
        }

        $guest = Guest::where('uuid', $uuid)->first();

        // Get location with timeout to prevent blocking requests
        $position = null;
        try {
            $position = Location::get(request()->ip());
        } catch (\Throwable $e) {
            logger()->warning('Failed to get location for guest', [
                'ip' => request()->ip(),
                'error' => $e->getMessage(),
            ]);
            // Continue without location data
        }

        $country = $position?->countryName ?? null;
        $city    = $position?->cityName ?? null;

        if ($guest) {
            logger("Guest ({$guest->uuid}) already exists, updating...");

            $guest->update([
                'country' => $country ?? $guest->country,
                'city' => $city ?? $guest->city,
                'language' => request()->getPreferredLanguage() ?? $guest->language,
                'session_id' => session()->getId(),
                'currency_code' => $position?->currencyCode ?? $guest->currency_code,
                'country_code' => $position?->countryCode ?? $guest->currency_code,
                'region_code' => $position?->regionCode ?? $guest->currency_code,
                'region_name' => $position?->regionName ?? $guest->currency_code,
                'zip_code' => $position?->zipCode ?? $guest->currency_code,
                'iso_code' => $position?->isoCode ?? $guest->currency_code,
                'postal_code' => $position?->postalCode ?? $guest->currency_code,
                'latitude' => $position?->latitude ?? $guest->currency_code,
                'longitude' => $position?->longitude ?? $guest->currency_code,
                'metro_code' => $position?->metroCode ?? $guest->currency_code,
                'area_code' => $position?->areaCode ?? $guest->currency_code,
                'timezone' => $position?->timezone ?? $guest->currency_code
            ]);
            return $guest;
        }

        logger("Guest ({$uuid}) does not exist, creating...");

        $guest = Guest::create([
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->headers->get('referer'),
            'current_url' => request()->fullUrl(),
            'previous_url' => url()->previous(),
            'country' => $country,
            'city' => $city,
            'language' => request()->getPreferredLanguage(),
            'session_id' => session()->getId(),
            'currency_code' => $position?->currencyCode ?? null,
            'country_code' => $position?->countryCode ?? null,
            'region_code' => $position?->regionCode ?? null,
            'region_name' => $position?->regionName ?? null,
            'zip_code' => $position?->zipCode ?? null,
            'iso_code' => $position?->isoCode ?? null,
            'postal_code' => $position?->postalCode ?? null,
            'latitude' => $position?->latitude ?? null,
            'longitude' => $position?->longitude ?? null,
            'metro_code' => $position?->metroCode ?? null,
            'area_code' => $position?->areaCode ?? null,
            'timezone' => $position?->timezone ?? null
        ]);

        return $guest;
    }
}
