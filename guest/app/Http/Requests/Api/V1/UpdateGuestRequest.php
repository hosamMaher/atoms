<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $guestId = $this->route('id');
        
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:guests,email,' . $guestId,
            'password' => 'nullable|string|min:6|confirmed',
            'mobile' => 'sometimes|string|max:255',
            'photo' => 'nullable|string',
            'category_id' => 'sometimes|integer',
            'subcategory_id' => 'nullable|integer',
            'status' => 'nullable|string'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}

