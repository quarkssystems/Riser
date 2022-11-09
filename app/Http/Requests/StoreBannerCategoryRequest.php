<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBannerCategoryRequest extends FormRequest
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
        $name_validation = (in_array($this->method(), ['PUT', 'PATCH'])) ? ',name,'.$this->banner_category->id : '';
        $slug_validation = (in_array($this->method(), ['PUT', 'PATCH'])) ? ',slug,'.$this->banner_category->id : '';

        return [
            'name' => 'required|unique:master_banner_categories'.$name_validation,
            'slug' => 'required|regex:/^[a-zA-Z0-9-_]+$/|unique:master_banner_categories'.$slug_validation,
        ];
    }
}
