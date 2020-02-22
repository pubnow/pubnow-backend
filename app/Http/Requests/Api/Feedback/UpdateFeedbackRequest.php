<?php

namespace App\Http\Requests\Api\Feedback;

use App\Http\Requests\Api\ApiRequest;

class UpdateFeedbackRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'resolve' => 'sometimes|boolean'
        ];
    }
}
