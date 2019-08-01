<?php

namespace App\Http\Requests\Api\InviteRequest;

use App\Http\Requests\Api\ApiRequest;

class CreateInviteRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'organization_id' => 'required|uuid|exists:organizations,id',
            'user_id' => 'required|uuid|exists:users,id',
        ];
    }
}
