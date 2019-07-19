<?php

namespace App\Http\Requests\Api\Category;

use App\Http\Requests\Api\ApiRequest;

class CreateCategory extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:60|unique:categories,name',
            'image'   => 'sometimes|file',
        ];
    }
}
