<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserGroupResource extends JsonResource
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
            'organization_id' => $this->organization_id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
            'users' => $this->whenLoaded('users', function () {
                return $this->users->map(fn ($user) => [
                    'id' => $user->id,
                    'organization_id' => $user->organization_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'phone_country_code' => $user->phone_country_code,
                ])->values();
            }),
            'users_count' => $this->when(isset($this->users_count), $this->users_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when(isset($this->deleted_at), $this->deleted_at),
        ];
    }
}
