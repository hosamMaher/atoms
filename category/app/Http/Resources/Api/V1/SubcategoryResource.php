<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class SubcategoryResource extends JsonResource {
    public function toArray($request) {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category_id' => $this->category_id,
            'is_active' => (bool) $this->is_active,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
            'category' => $this->when($this->relationLoaded('category') && $this->category, [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
        ];
    }
}

