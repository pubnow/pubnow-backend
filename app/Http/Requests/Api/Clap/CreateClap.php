<?php

namespace App\Http\Requests\Api\Clap;

use App\Http\Requests\Api\ApiRequest;

class CreateClap extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'article_id' => 'required|uuid|exists:articles,id'
        ];
    }
}
