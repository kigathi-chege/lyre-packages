<?php

namespace Lyre\Guest\Concerns;

trait HasGuest
{
    /**
     * Get the guest model instance.
     *
     * @return \Lyre\Guest\Models\Guest|null
     */
    public function guests()
    {
        return $this->hasMany($this->guestModel());
    }

    /**
     * Check if the model is a guest.
     *
     * @return bool
     */
    public function isGuest()
    {
        return $this->is_guest;
    }

    /**
     * Get the guest model class.
     *
     * @return string
     */
    protected function guestModel()
    {
        return config('lyre.guest.model', \Lyre\Guest\Models\Guest::class);
    }
}
