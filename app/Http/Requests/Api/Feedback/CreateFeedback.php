<?php

namespace App\Http\Requests;

use App\Http\Requests\Api\ApiRequest;

class CreateFeedback extends ApiRequest
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
