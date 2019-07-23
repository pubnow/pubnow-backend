<?php

namespace App\Http\Requests\Api\Organization;

use App\Http\Requests\Api\ApiRequest;

class CreateOrganization extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:50|unique:organizations,name',
            'email' => 'required|email|max:255|unique:organizations,email',
            'description' => 'sometimes',
            'logo' => 'sometimes|file',
        ];
    }
}
