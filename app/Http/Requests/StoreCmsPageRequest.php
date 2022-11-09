<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCmsPageRequest extends FormRequest
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
        $slug_validation = (in_array($this->method(), ['PUT', 'PATCH'])) ? ',slug,'.$this->cms_page->id : '';

        return [
            'page_title' => 'required',
            'slug' => 'required|regex:/^[a-zA-Z0-9-_]+$/|unique:cms_pages'.$slug_validation,
        ];
    }
}
