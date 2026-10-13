<?php

namespace App\Support;

use App\Models\Location;

class LocationContext
{
    protected ?Location $location = null;

    public function setLocation(Location $location): void
    {
        $this->location = $location;
    }

    public function location(): ?Location
    {
        return $this->location;
    }

    public function locationId(): ?int
    {
        return $this->location?->id;
    }
}
