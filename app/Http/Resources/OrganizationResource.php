<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'display_name' => $this->display_name,
            'legal_name' => $this->legal_name,
            'status' => $this->status,
            'website_url' => $this->website_url,
            'primary_phone' => $this->primary_phone,
            'primary_email' => $this->primary_email,
            'phone_country_code' => $this->phone_country_code,
            'address_id' => $this->address_id,
            'company' => CompanyResource::make($this->whenLoaded('company')),
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
            'types' => $this->whenLoaded('typeAssignments', fn () => $this->typeAssignments->map(fn ($type) => [
                'id' => $type->id,
                'type' => $type->type,
                'is_primary' => $type->is_primary,
            ])->values()),
            'contacts_count' => $this->when(isset($this->contacts_count), $this->contacts_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when(isset($this->deleted_at), $this->deleted_at),
        ];
    }
}
