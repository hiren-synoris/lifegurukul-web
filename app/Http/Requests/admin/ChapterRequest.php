<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class ChapterRequest extends FormRequest
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
        // dd(request()->all());
        if(request()->isMethod('post')){
            return [
                'title' => 'required|max:255',

                // 'thumbnail' => 'mimes:jpeg,png|max:500|dimensions:min_width=1366,max_width=1920,min_height=300,max_height=350',

                //'instructor_id' =>'required|exists:users,id',
            ];
        }



        /**
         * This method is for update()
         */
        if(request()->isMethod('put') || request()->isMethod('patch')){
            $rules = [];

                //Heading Validation
                if(request()->has('heading_type')){
                    $rules = [
                        'title' => 'required|max:255',

                        // 'thumbnail' => 'mimes:jpeg,png|max:500|dimensions:min_width=1366,max_width=1920,min_height=300,max_height=350',
                    ];
                }

                //Pages Tab Validation
                if(request()->has('text_type')){
                    $rules = [
                        'title' => 'required|max:255',

                        //'tags' => 'required',
                        // 'availability_setting' => 'required',
                        // 'available_from' => 'required_if:availability_setting,1',
                        // 'available_till' => 'required_if:availability_setting,1',
                        //'description' => 'required',
                    ];
                }

                //Pages Tab Validation
                if(request()->has('image_type')){
                    $rules = [
                        'title' => 'required|max:255',
                        // 'thumbnail' => 'mimes:jpeg,png|max:500|dimensions:min_width=1366,max_width=1920,min_height=300,max_height=350',

                        //'tags' => 'required',
                        // 'availability_setting' => 'required',
                        // 'available_from' => 'required_if:availability_setting,1',
                        // 'available_till' => 'required_if:availability_setting,1',
                        //'description' => 'required',
                    ];
                }

                //Pages Tab Validation
                if(request()->has('pdf_type')){
                    $rules = [
                        'title' => 'required|max:255',
                        //'tags' => 'required',
                        // 'availability_setting' => 'required',
                        // 'available_from' => 'required_if:availability_setting,1',
                        // 'available_till' => 'required_if:availability_setting,1',
                        //'description' => 'required',
                    ];
                }
                if(request()->has('timer')){
                    $rules = [
                        'timer' => 'required|numeric',
                    ];
                }

                //Pages Tab Validation
                if(request()->has('link_type')){
                    $rules = [
                        'title' => 'required|max:255',
                        //'tags' => 'required',
                        // 'availability_setting' => 'required',
                        // 'available_from' => 'required_if:availability_setting,1',
                        // 'available_till' => 'required_if:availability_setting,1',
                        //'description' => 'required',
                    ];
                }
                //Pages Tab Validation
                if(request()->has('sellbuy_type')){

                    $rules = [
                        'title' => 'required',
                        'plan_id' => 'required',

                        //'tags' => 'required',
                        // 'availability_setting' => 'required',
                        // 'available_from' => 'required_if:availability_setting,1',
                        // 'available_till' => 'required_if:availability_setting,1',
                        //'description' => 'required',
                    ];
                }
                //Pages Tab Validation
                if(request()->has('video_type')){
                    $rules = [
                        'title' => 'required|max:255',
                        //'tags' => 'required',
                        // 'availability_setting' => 'required',
                        // 'available_from' => 'required_if:availability_setting,1',
                        // 'available_till' => 'required_if:availability_setting,1',
                       // 'file_url' => 'required',
                    ];
                }

            return $rules;
        }
    }

    public function messages()
    {
        return [
            'title.required' => 'Title is required.',
            'file_url.required' => 'file_url is required.',
            'title.max' => 'Title may not be greater than 255 characters.',
            'tags.required' => 'Tags are required.',
            //'availability_setting.required' => 'Availability Setting is required.',
            'available_from.required' => 'Available From is required.',
            'available_till.required' => 'Available Till is required.',
            'timer.required' => 'Minutes is required',
        ];
    }
}
