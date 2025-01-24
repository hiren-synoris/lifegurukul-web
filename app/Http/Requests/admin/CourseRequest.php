<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class CourseRequest extends FormRequest
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

            // dd(request()->all());
            return [
                'title' => 'required|max:255',
                'slug' =>'required|filled|max:255|unique:courses,slug|alpha_dash',
                'type' => 'required',
                // 'order' => 'required',
                'instructor_id' =>'required|exists:users,id',
                'category_id' =>'required|array',
                'course_coin' =>'nullable|gt:0',
                'course_finished' =>'nullable|gt:0',
                'course_finished_day' =>'nullable|gt:0',
                'days' =>'nullable|gt:0',
            ];
        }



        /**
         * This method is for update()
         */
        if(request()->isMethod('put') || request()->isMethod('patch')){
            $rules = [];
                // dd("");
                //Details Tab Validation
                if(request()->has('details_tab')){
                    // dd(request()->all());
                    $rules = [
                        // 'title' => 'required|regex:/^[a-zA-ZÑñ\s]+$/|max:255',
                        'title' => 'required|max:255', // Alphanumeric, colon, and spaces allowed
                        // 'order' => 'required',
                        // 'hours' => 'digits:1',
                        // 'minutes' => 'digits:1',
                        // 'instructor_id' =>'required',//|exists:users,id',
                        // 'instructor_id' =>'required',//|exists:users,id',
                        'intro_video' => 'mimetypes:video/*',
                        'banner_image' => 'image|mimes:jpg,png',
                        // 'banner_image' => 'image|mimes:jpeg,png|dimensions:min_width=1366,max_width=1920,min_height=300,max_height=350',
                        'category_id' => 'required|array',
                        // 'course_coin' =>'required',
                        'course_coin' =>'nullable|gt:0',
                        'course_finished' =>'nullable|gt:0',
                        'course_finished_day' =>'nullable|gt:0',
                        'days' =>'nullable|gt:0',
                    ];
                }

                //Pages Tab Validation
                // if(request()->has('pages_tab')){
                //     $rules = [
                //         'course_page_title' =>'required|max:255',
                //     ];
                // }

            return $rules;
        }
    }

    public function messages()
    {
        return [
            'title.required' => 'The title field is required.',
            'intro_video.mimetypes' => 'Only video file are allowed.',
            'title.max' => 'Title may not be greater than 255 characters.',
            'instructor_id.required' => 'The instructor field is required.',
            'instructor_id.exists' => 'Selected instructor is not a valid.',
            //Page Tab error message
            'course_page_title.required' => 'Course Page Title is required.',
            'course_page_title.max' => 'Course Page Title may not be greater than 255 characters.',
            // 'hours.digits' => 'The required must be two digit.',
            // 'minutes.digits' => 'The required must be two digit.',
            'course_coin' => 'Coin must be greater than 0.',
            'course_finished.gt' => 'Coin must be greater than 0.',
            'course_finished_day.gt' => 'Coin must be greater than 0.',
            'category_id' => 'the category field is Required.',
            // 'banner_image.max' => 'The banner image must be less than 500KB.',
        ];
    }
}
