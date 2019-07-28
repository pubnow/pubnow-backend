<?php

namespace App\Http\Requests\Api\Organization;

use App\Http\Requests\Api\ApiRequest;

class UpdateOrganization extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'sometimes|max:60|unique:organizations,name',
            'email' => 'sometimes|email|max:255|unique:organizations,email',
            'description' => 'sometimes',
            'image_id' => 'sometimes|uuid|exists:images,id',
        ];
    }
}
