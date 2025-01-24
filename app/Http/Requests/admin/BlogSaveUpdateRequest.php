<?php

namespace App\Http\Requests\admin;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class BlogSaveUpdateRequest extends FormRequest
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
                // 'title' => 'required|regex:/^[a-zA-ZÑñ\s]+$/|max:255',
                'title' => 'required|max:255',
                'slug' => 'required|unique:blogs,slug|alpha_dash',
                'category_id' => 'required|exists:dropdown_options,id',
                'cover' => 'required|mimes:jpeg,png,jpg,svg',
               // 'cover.*'=>'dimensions:max_width=1280,max_height=850',
                'content'=>'required|filled'
            ];
        }

        /**
         * This method is for update()
        */
        if(request()->isMethod('put') || request()->isMethod('patch')){
            return [
                // 'title' => 'required|max:255|regex:/^[a-zA-ZÑñ\s]+$/',
                'title' => 'required|max:255',
                'slug' => ['required','filled','max:255','alpha_dash',Rule::unique('blogs', 'slug')->ignore($this->blog)],
                'category_id' => 'required|exists:dropdown_options,id',
                'cover' => 'sometimes|present|mimes:jpeg,png,jpg,svg',
                'content'=>'required|filled'
            ];
        }
    }

    public function messages()
    {
        return [
            'title.required' => 'The name field is required.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'slug.unique' => 'The Slug has already been taken.',
            'slug.alpha_dash' => 'The invalid slug is format.',
            'category_id.required' => 'The category field is required.',
            'category_id.exists' => 'The category field is exists.',
            'cover.required' => 'The cover image is required.',
            // 'cover.max' => 'The cover image must be less than 500 kb..',
            //'cover.*' => 'All Image Dimention should be 1280*850',
            'content.required' => 'The content is required.'
        ];
    }
}
