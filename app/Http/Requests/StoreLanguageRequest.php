<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLanguageRequest extends FormRequest
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
        $name_validation = (in_array($this->method(), ['PUT', 'PATCH'])) ? ',language_name,'.$this->language->id : '';

        return [
            'language_name' => 'required|unique:master_languages'.$name_validation,
        ];
    }
}
