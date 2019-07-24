<?php

namespace App\Http\Requests\Api\Role;

use App\Http\Requests\Api\ApiRequest;

class CreateRole extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:60|unique:roles,name',
            'description' => 'sometimes'
        ];
    }
}
