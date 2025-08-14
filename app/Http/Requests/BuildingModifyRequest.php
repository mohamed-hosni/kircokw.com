<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BuildingModifyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'percentage' => 'required|numeric|min:0|max:100',
        ];
    }
    
    public function messages():array
    {
        $messages = [
            'name.required' => 'يجب عليك إدخال الاسم',
            'percentage' => 'يجب عليك إدخال السعر',
        ];    
        return $messages;
    }
}
