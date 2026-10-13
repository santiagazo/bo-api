<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $color = $this->resource->resolvedColor();

        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'address_id' => $this->address_id,
            'name' => $this->name,
            'code' => $this->code,
            'sort_order' => $this->sort_order,
            'color' => $this->color,
            'color_label' => $color?->label(),
            'color_hex' => $color?->hex(),
            'phone' => $this->phone,
            'phone_country_code' => $this->phone_country_code,
            'email' => $this->email,
            'status' => $this->status,
            'timezone' => $this->timezone,
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
            'jobs_count' => $this->when(isset($this->jobs_count), $this->jobs_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when(isset($this->deleted_at), $this->deleted_at),
        ];
    }
}
