<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminRequest extends FormRequest
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
        $email_validation = (in_array($this->method(), ['PUT', 'PATCH'])) ? ',id'.$this->id : '';

        $password_validation = (in_array($this->method(), ['PUT', 'PATCH'])) ? 'nullable' : 'required';

        $genderCheck = config('constant.gender.male').','.config('constant.gender.female').','.config('constant.gender.other');
        
        return [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required_without:contact_number|email|max:255|unique:users'.$email_validation,
            'username' => 'max:255|unique:users'.$email_validation,
            'contact_number' => 'required_without:email',
            'whatsapp_number' => 'nullable|min:8|max:13',
            'password' => 'min:8|confirmed|'.$password_validation,
            'gender' => 'nullable|in:'.$genderCheck,
            'profile_picture' => 'nullable|image',
            'facebook_link' => 'nullable|url',
            'twitter_link' => 'nullable|url',
            'linkedin_link' => 'nullable|url',
            'instagram_link' => 'nullable|url',
            'youtube_link' => 'nullable|url',
        ];
    }
}
