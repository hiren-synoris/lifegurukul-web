<?php

namespace App\Http\Requests\admin;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class PageRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        /**
         * This method is for store()
         */
        if(request()->isMethod('post')){
            return [
                // 'name' => 'required|regex:/^[a-zA-ZÑñ\s]+$/|max:255',
                'name' => 'required|max:255',
                'slug' =>'required|filled|max:255|unique:pages,slug|alpha_dash',
                'body' => 'required'
            ];
        }

        /**
         * This method is for update()
         */
        if(request()->isMethod('put') || request()->isMethod('patch')){
            return [
                // 'name' => 'required|max:255|regex:/^[a-zA-ZÑñ\s]+$/',
                'name' => 'required|max:255',
                'slug' => ['required','filled','max:255','alpha_dash',Rule::unique('pages', 'slug')->ignore($this->page)],
                'body' => 'required'
            ];
        }
    }

    public function messages()
    {
        return [
            'name.required' => 'the title field is required.',
            'name.max' => 'Title may not be greater than 255 characters.',
            'slug.required' => 'the slug field is required',
            'slug.alpha_dash' => 'Invalid slug format',
            'slug.unique' => 'Slug already exists, It must be unique.',
            'body.required' => 'the body field is required'
        ];
    }
}
