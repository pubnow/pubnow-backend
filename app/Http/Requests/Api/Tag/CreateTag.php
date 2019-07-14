<?php

namespace App\Http\Requests\Api\Tag;

use App\Http\Requests\Api\ApiRequest;

class CreateTag extends ApiRequest
{
    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    protected function validationData()
    {
        return $this->get('tag') ?: [];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:60|unique:tags,name',
            'slug' => 'required|max:60|unique:tags,slug',
        ];
    }
}
