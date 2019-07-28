<?php

namespace App\Http\Requests\Api\Comment;

use App\Http\Requests\Api\ApiRequest;

class CreateComment extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'article_id' => 'required|uuid|exists:articles,id',
            'parent_id' => 'sometimes|uuid|exists:comments,id',
            'content' => 'required'
        ];
    }
}
