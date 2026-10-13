<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'name' => $this->name,
            'title' => $this->title,
            'notes' => $this->notes,
            'is_primary' => $this->is_primary,
            'is_active' => $this->is_active,
            'primary_phone_number' => $this->whenLoaded(
                'primaryPhoneNumber',
                fn () => $this->primaryPhoneNumber?->phone_number
            ),
            'primary_email_address' => $this->whenLoaded(
                'primaryEmailAddress',
                fn () => $this->primaryEmailAddress?->email
            ),
            'phone_numbers' => CustomerContactPhoneNumberResource::collection(
                $this->whenLoaded('phoneNumbers')
            ),
            'email_addresses' => CustomerContactEmailAddressResource::collection(
                $this->whenLoaded('emailAddresses')
            ),
        ];
    }
}
