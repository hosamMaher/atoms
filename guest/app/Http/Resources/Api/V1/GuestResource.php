<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class GuestResource extends JsonResource {
    public function toArray($request) {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'photo' => $this->photo,
            'category_id' => $this->category_id,
            'subcategory_id' => $this->subcategory_id,
            'status' => $this->status,
            'approved_by' => $this->approved_by,
            'approved_at' => optional($this->approved_at)->toDateTimeString(),
            'rejected_by' => $this->rejected_by,
            'rejected_at' => optional($this->rejected_at)->toDateTimeString(),
            'reject_reason' => $this->reject_reason,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
            'category' => $this->when(
                isset($this->category_data) && $this->category_data !== null,
                $this->category_data
            ),
            'subcategory' => $this->when(
                isset($this->subcategory_data) && $this->subcategory_data !== null,
                $this->subcategory_data
            ),
        ];
    }
}

