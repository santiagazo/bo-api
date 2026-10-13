<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerContactEmailAddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'email' => $this->email,
            'is_primary' => $this->is_primary,
            'is_active' => $this->is_active,
        ];
    }
}
