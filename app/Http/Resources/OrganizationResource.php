<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'phone' => $this->phone,
            'email' => $this->email,
            'phone_country_code' => $this->phone_country_code,
            'address_id' => $this->address_id,
            'referral_fee_percentage' => $this->referral_fee_percentage,
            'address' => $this->whenLoaded('address', function () {
                return [
                    'id' => $this->address->id,
                    'name' => $this->address->name,
                    'line1' => $this->address->line1,
                    'line2' => $this->address->line2,
                    'city' => $this->address->city,
                    'state' => $this->address->state,
                    'postal_code' => $this->address->postal_code,
                    'country' => $this->address->country,
                ];
            }),
            'users_count' => $this->when(isset($this->users_count), $this->users_count),
            'locations_count' => $this->when(isset($this->locations_count), $this->locations_count),
            'jobs_count' => $this->when(isset($this->jobs_count), $this->jobs_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when(isset($this->deleted_at), $this->deleted_at),
        ];
    }
}

