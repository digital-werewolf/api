<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\CustomFormRequest;

class SignUpRequest extends CustomFormRequest
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
            'username' => 'required|min:2|max:10|unique:players',
            'email' => 'required|max:50|email|unique:players',
            'password' => 'required|min:5|confirmed',
        ];
    }
}
