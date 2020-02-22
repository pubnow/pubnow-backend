<?php

namespace App\Http\Requests\Api\Image;

use App\Http\Requests\Api\ApiRequest;

class StoreImage extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => 'required|image|max:2000',
        ];
    }
}
