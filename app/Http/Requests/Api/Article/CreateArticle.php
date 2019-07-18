<?php

namespace App\Http\Requests\Api\Article;

use App\Http\Requests\Api\ApiRequest;

class CreateArticle extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|min:10',
            'content' => 'required|string',
            'category' => 'required|uuid|exists:categories,id',
            'tagList' => 'sometimes|array'
        ];
    }
}
