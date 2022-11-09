<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCallPackageRequest extends FormRequest
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
            'name' => 'required',
            'duration_minutes' => 'required|integer',
            'discount_percentage' => 'nullable|integer|min:1|max:100',
            'price' => 'required|regex:/^\d{0,10}(\.\d{1,2})?$/',
        ];
    }
}
