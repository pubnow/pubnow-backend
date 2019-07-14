<?php

namespace App\Http\Requests\Api\Category;

use App\Http\Requests\Api\ApiRequest;

class UpdateCategory extends ApiRequest
{
    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    protected function validationData()
    {
        return $this->get('category') ?: [];
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'sometimes|max:60|unique:categories,name',
            'slug' => 'max:60|unique:categories,slug',
        ];
    }
}
