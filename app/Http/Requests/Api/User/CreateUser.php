<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'required|max:50|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:6',
            'name'     => 'required',
            'bio'      => 'sometimes',
            'avatar'   => 'sometimes|file',
            'role_id'     => 'sometimes|uuid|exists:roles,id',
        ];
    }

    public function messages()
    {
        return [
            'username.unique' => 'Tên tài khoản đã tồn tại',
            'email.unique' => 'Email đã tồn tại',
        ];
    }
}
