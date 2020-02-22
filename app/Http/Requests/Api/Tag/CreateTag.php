<?php

namespace App\Http\Requests\Api\Tag;

use App\Http\Requests\Api\ApiRequest;

class CreateTag extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:60|unique:tags,name',
            'image_id'   => 'sometimes|uuid|exists:images,id',
        ];
    }
}
