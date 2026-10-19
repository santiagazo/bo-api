<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyBrandProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'logo_url' => $this->logo_url,
            'icon_url' => $this->icon_url,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'accent_color' => $this->accent_color,
            'tagline' => $this->tagline,
            'university_name' => $this->university_name,
            'mission' => $this->mission,
            'vision' => $this->vision,
            'updated_at' => $this->updated_at,
        ];
    }
}
