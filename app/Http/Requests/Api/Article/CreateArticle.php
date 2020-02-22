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
            'category_id' => 'required|uuid|exists:categories,id',
            'organization_id' => 'sometimes|uuid|exists:organizations,id',
            'organization_private' => 'sometimes|boolean',
            'tags' => 'sometimes|array'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Bài viết chưa có tiêu đề.',
            'title.min' => 'Tiêu đề bài viết quá ngắn. Độ dài tối thiểu 10 kí tự.',
            'content.required' => 'Bạn cần nhập nội dung bài viết.',
            'category_id.required' => 'Bạn chưa chọn danh mục cho bài viết.'
        ];
    }
}
