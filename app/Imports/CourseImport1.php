<?php

namespace App\Imports;

use App\Models\Course;
use App\Models\CoursePlan;
use App\Models\Learner;
use App\Models\UserCourse;
use Carbon\Carbon;
use DB;
// use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CourseImport1 implements
ToModel,
// WithHeadingRow,
WithMultipleSheets,
WithValidation,
WithChunkReading,
// ShouldQueue,
WithStartRow,
// WithEvents
SkipsOnFailure
, SkipsOnError
// SkipsOnFailure

{
    use Importable, SkipsErrors, SkipsFailures;

    private $course_id;
    private $plan_id;
    private $country_id;
    private $admin_id;

    public function __construct($course_id, $plan_id,$admin_id)
    {
        $this->course_id = $course_id;
        $this->plan_id = $plan_id;
        $this->admin_id = $admin_id;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }

    public function model(array $row)
    {

        if (isset($row['3']) && $row[3] != 'Phone Number' && $row['3'] != '' && isset($row['2']) && $row['2'] != '' && $row[2] != 'Name') {
            if($row['2']==1) {
                $contry_code = DB::table('countries')->where("id", 231)->first()->id;
            } else {

                $contry_code = DB::table('countries')->where("phonecode", $row['2'])->first()->id;
            }
            $learner = Learner::where("mobile", $row['3'])->where("country_id", $contry_code)->first();

            if ($learner) {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0');
                $expiredAt = get_plan_expire($this->plan_id);
                $learnerData = Learner::with('country:id,phonecode')->where("id", $learner->id)->get()->first();
                $ids[] = $learnerData->id;
                $coursePlanData = CoursePlan::where("id", $this->plan_id)->get()->first();
                $course_title = Course::where('id', $this->course_id)->first()->title;
                $userCourse = UserCourse::create([
                    'course_id' => $this->course_id,
                    'learner_id' => $learner->id,
                    'plan_id' => $this->plan_id,
                    'order_status' => 4,
                    'expire_at' => $expiredAt,
                    'price' => (!empty($coursePlanData)) ? $coursePlanData->final_payable_price : 0.00,
                    'full_name' => (!empty($learnerData)) ? $learnerData->name : null,
                    'email' => (!empty($learnerData)) ? $learnerData->email : null,
                    'mobile' => (!empty($learnerData)) ? $learnerData->mobile : null,
                    'country_id' => (!empty($learnerData)) ? $learnerData->country_id : null,
                    'state_id' => (!empty($learnerData)) ? $learnerData->state_id : null,
                    'city_id' => (!empty($learnerData)) ? $learnerData->city_id : null,
                    'created_by' => $this->admin_id,
                ]);

            } else {

                $learner = Learner::create(
                    [
                        'mobile' => $row['3'],
                        'name' => $row['0'],
                        'country_id' => $contry_code,
                        'email' => $row['1'],
                        'gender' => $row['4'],
                        'd_o_b' => $row['5'],
                    ]
                );

                // DB::statement('SET FOREIGN_KEY_CHECKS = 0');
                $expiredAt = get_plan_expire($this->plan_id);
                $learnerData = Learner::with('country:id,phonecode')->where("id", $learner->id)->get()->first();
                $ids[] = $learnerData->id;
                $coursePlanData = CoursePlan::where("id", $this->plan_id)->get()->first();
                $course_title = Course::where('id', $this->course_id)->first()->title;
                $userCourse = UserCourse::create([
                    'course_id' => $this->course_id,
                    'learner_id' => $learner->id,
                    'plan_id' => $this->plan_id,
                    'order_status' => 4,
                    'expire_at' => $expiredAt,
                    'price' => (!empty($coursePlanData)) ? $coursePlanData->final_payable_price : 0.00,
                    'full_name' => (!empty($learnerData)) ? $learnerData->name : null,
                    'email' => (!empty($learnerData)) ? $learnerData->email : null,
                    'mobile' => (!empty($learnerData)) ? $learnerData->mobile : null,
                    'country_id' => (!empty($learnerData)) ? $learnerData->country_id : null,
                    'state_id' => (!empty($learnerData)) ? $learnerData->state_id : null,
                    'city_id' => (!empty($learnerData)) ? $learnerData->city_id : null,
                    'created_by' => $this->admin_id,
                ]);

            }

        }
    }

    public function withValidator($validator)
    {

        $validator->after(function ($validator) {
            $now = Carbon::now();
            foreach ($validator->getData() as $key => $data) {
                $contry_code = DB::table('countries')->where("phonecode", $data['2'])->first();

                if (!$contry_code) {
                    $validator->errors()->add($key, 'Please enter valid country code.');
                }

                $date2 = \DateTime::createFromFormat('d/m/Y', $data['5']);
                // dd($date2);
                if ($now < $date2) {
                    $validator->errors()->add($key, 'The date of birth must be a date before today.');
                }

            }
        });
    }

    public function rules(): array
    {
        $now = Carbon::now();

        return [
            '*.2' => 'required|numeric',
            // '*.3' => 'required|digits:10',
            '*.3' => 'required|numeric',
            // '*.5' => 'nullable|before:'. $now,
            '*.1' => 'nullable|email',
        ];
    }

    public function customValidationMessages()
    {
        return [

            '*.2.required' => 'The country filed is required.',
            '*.2.numeric' => 'Valid country code.',
            '*.3.required' => 'The mobile filed is required..',
            '*.3.numeric' => 'The mobile must be enter numberic.',
            // '*.5.date_format' => 'The date of birth does not match the format d-m-Y.',
            // '*.5.before' => 'The date of birth must be a date before today.',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
