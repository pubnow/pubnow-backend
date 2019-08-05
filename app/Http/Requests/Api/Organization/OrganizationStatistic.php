<?php

namespace App\Http\Requests\Api\Organization;

use App\Http\Requests\Api\ApiRequest;

class OrganizationStatistic extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start' => 'required|date',
            'end' => 'required|date',
        ];
    }
}
