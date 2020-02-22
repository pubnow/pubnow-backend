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
            'image_id'   => 'sometimes|uuid|exists:images,id',
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'Tên chuyên mục đã tồn tại',
        ];
    }
}
