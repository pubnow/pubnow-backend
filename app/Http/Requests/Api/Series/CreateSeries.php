<?php

namespace App\Http\Requests\Api\Series;

use App\Http\Requests\Api\ApiRequest;

class CreateSeries extends ApiRequest
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
            'articles' => 'sometimes|array'
        ];
    }
}
