<?php

namespace App\Http\Requests\Api\Feedback;

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
            'article_id' => 'sometimes|uuid|exists:articles,id',
            'username' => 'required|string|min:3',
            'reference' => 'required|string',
            'content' => 'required|string',
            'email' => 'required|email',
            'type' => 'required|integer',
        ];
    }
}
