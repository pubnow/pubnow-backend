<?php

namespace App\Http\Requests\Api\Organization;

use App\Http\Requests\Api\ApiRequest;

class FollowOrganization extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'organization_id' => 'required|uuid|exists:organizations,id'
        ];
    }
}
