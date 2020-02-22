<?php

namespace App\Http\Requests\Api\User;

use App\Http\Requests\Api\ApiRequest;

class UpdateUser extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'password' => 'sometimes|min:6',
            'name'     => 'sometimes',
            'bio'      => 'sometimes',
            'image_id'   => 'sometimes|uuid|exists:images,id',
            'role_id'     => 'sometimes',
        ];
    }
}
