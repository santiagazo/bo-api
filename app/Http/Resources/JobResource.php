<?php

namespace App\Http\Resources;

use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'location_id' => $this->location_id,
            'customer_id' => $this->customer_id,
            'property_address_id' => $this->property_address_id,
            'reference_id' => $this->reference_id,
            'name' => $this->name,
            'status' => $this->enumValue($this->status),
            'status_label' => $this->status?->label(),
            'type' => $this->enumValue($this->type),
            'type_label' => $this->type?->label(),
            'property_type' => $this->enumValue($this->property_type),
            'property_type_label' => $this->property_type?->label(),
            'loss_type' => $this->enumValue($this->loss_type),
            'loss_type_label' => $this->loss_type?->label(),
            'claim_number' => $this->claim_number,
            'policy_number' => $this->policy_number,
            'opened_at' => $this->opened_at,
            'date_of_loss' => $this->date_of_loss,
            'notes' => $this->notes,
            'customer_name' => $this->whenLoaded('customer', fn () => $this->customer?->name),
            'location_name' => $this->whenLoaded('location', fn () => $this->location?->name),
            'customer' => CustomerResource::make($this->whenLoaded('customer')),
            'location' => LocationResource::make($this->whenLoaded('location')),
            'property_address' => $this->whenLoaded('propertyAddress', function () {
                return [
                    'id' => $this->propertyAddress?->id,
                    'name' => $this->propertyAddress?->name,
                    'line1' => $this->propertyAddress?->line1,
                    'line2' => $this->propertyAddress?->line2,
                    'city' => $this->propertyAddress?->city,
                    'state' => $this->propertyAddress?->state,
                    'postal_code' => $this->propertyAddress?->postal_code,
                    'country' => $this->propertyAddress?->country,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when(isset($this->deleted_at), $this->deleted_at),
        ];
    }

    protected function enumValue(mixed $value): mixed
    {
        return $value instanceof BackedEnum ? $value->value : $value;
    }
}
