<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'legal_name' => $this->legal_name,
            'slug' => $this->slug,
            'status' => $this->status,
            'primary_email' => $this->primary_email,
            'primary_phone' => $this->primary_phone,
            'phone_country_code' => $this->phone_country_code,
            'website_url' => $this->website_url,
            'timezone' => $this->timezone,
            'currency_code' => $this->currency_code,
            'default_locale' => $this->default_locale,
            'address_id' => $this->address_id,
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
            'organizations_count' => $this->when(isset($this->organizations_count), $this->organizations_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when(isset($this->deleted_at), $this->deleted_at),
        ];
    }
}
