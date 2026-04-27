<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'scope_type' => $this->scope_type?->value ?? $this->scope_type,
            'company_id' => $this->company_id,
            'guard_name' => $this->guard_name,
            'company' => CompanyResource::make($this->whenLoaded('company')),
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'users_count' => $this->whenCounted('memberships'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
