<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
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
            'title'       => 'required|string|max:255',
            'language_id' => 'required',
            'category_id' => 'required',
            'user_id'     => 'required|integer',
            'country_id'  => 'integer',
            'state_id'    => 'integer',
            'district_id' => 'integer',
            'taluka_id'   => 'integer',
            'media_url'   => 'required|mimes:svg,mp3,mpeg,mp4,3gp',
        ];
    }
}
