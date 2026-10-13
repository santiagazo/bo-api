<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerContactPhoneNumberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'phone_number' => $this->phone_number,
            'phone_country_code' => $this->phone_country_code,
            'is_primary' => $this->is_primary,
            'is_active' => $this->is_active,
        ];
    }
}
