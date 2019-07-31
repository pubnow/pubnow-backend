<?php

namespace App\Http\Requests\Api\Bookmark;

use App\Http\Requests\Api\ApiRequest;

class CreateBookmark extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'article_id' => 'uuid|exists:articles,id'
        ];
    }
}
