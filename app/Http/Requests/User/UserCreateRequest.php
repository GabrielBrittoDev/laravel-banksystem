<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserCreateRequest extends FormRequest
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
            'name'     => 'required|min:2|max:100|alpha|string',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|max:100|string',
            'username' => 'required|min:2|max:100|string|unique:users,username',
        ];
    }
}
