<?php

namespace App\Http\Requests\Api\Article;

use App\Http\Requests\Api\ApiRequest;

class UpdateArticle extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'sometimes|min:10',
            'content' => 'sometimes|string',
            'category' => 'sometimes|uuid|exists:categories,id'
        ];
    }
}
