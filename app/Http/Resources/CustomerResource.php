<?php

namespace App\Http\Resources;

use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'type' => $this->type instanceof BackedEnum ? $this->type->value : $this->type,
            'name' => $this->name,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'primary_contact_name' => $this->whenLoaded(
                'primaryContact',
                fn () => $this->primaryContact?->name
            ),
            'primary_phone_number' => $this->whenLoaded(
                'primaryContact',
                fn () => $this->primaryContact?->primaryPhoneNumber?->phone_number
            ),
            'primary_email_address' => $this->whenLoaded(
                'primaryContact',
                fn () => $this->primaryContact?->primaryEmailAddress?->email
            ),
            'primary_address' => CustomerAddressResource::make(
                $this->whenLoaded('primaryAddress')
            ),
            'billing_address' => CustomerAddressResource::make(
                $this->whenLoaded('billingAddress')
            ),
            'primary_contact' => CustomerContactResource::make(
                $this->whenLoaded('primaryContact')
            ),
            'contacts' => CustomerContactResource::collection(
                $this->whenLoaded('contacts')
            ),
            'addresses' => CustomerAddressResource::collection(
                $this->whenLoaded('addresses')
            ),
            'contacts_count' => $this->when(isset($this->contacts_count), $this->contacts_count),
            'jobs_count' => $this->when(isset($this->jobs_count), $this->jobs_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when(isset($this->deleted_at), $this->deleted_at),
        ];
    }
}
