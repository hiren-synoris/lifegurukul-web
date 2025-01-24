<?php


namespace App\Imports;

use DateTime;
use Throwable;
use Carbon\Carbon;
use App\Models\Course;
use App\Models\Learner;
use App\Models\CoursePlan;
use App\Models\UserCourse;
use App\Models\UserImportData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CourseImport implements
   // ToModel,
   ToCollection,
    WithHeadingRow,
    SkipsOnError,
    WithValidation,
    SkipsOnFailure

{
    use Importable, SkipsErrors,SkipsFailures;

    private $course_id;
    private $plan_id;
    private $country_id;


    public function __construct($course_id, $plan_id)
    {
        $this->course_id = $course_id;
        $this->plan_id = $plan_id;
    }


    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            $contry_code =  DB::table('countries')->where("phonecode",$row['phone_number_country_code_without_sign'])->first()->id;
            $learner = Learner::where("mobile", $row['phone_number_without_space_and_country_code'])->where("country_id", $contry_code)->first();

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
                        'created_by' => auth()->user()->id,
                    ]);

            } else {

                $learner = Learner::create(
                    [
                    'mobile' => isset($row['phone_number_without_space_and_country_code']) ? $row['phone_number_without_space_and_country_code'] : "",
                    'name' =>  isset($row['name']) ? $row['name'] : "",
                    'country_id' => $contry_code,
                    'email' =>  $row['email'] ,
                    'gender' =>  isset($row['gender1male2female3other']) ? $row['gender1male2female3other'] : "",
                    'd_o_b' =>  isset($row['date_of_birth_ddmmyyyy']) ? $row['date_of_birth_ddmmyyyy'] : ""
                    ]
                );


                $expiredAt = get_plan_expire($this->plan_id);
                $learnerData = Learner::with('country:id,phonecode')->where("id", $learner->id)->first();
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
                    'created_by' => auth()->user()->id,
                ]);

            }

        }
    }

    public function withValidator($validator){


        $validator->after(function ($validator) {
            $now = Carbon::now();
            foreach($validator->getData() as $key=>$data){
                $contry_code =  DB::table('countries')->where("phonecode",$data['phone_number_country_code_without_sign'])->first();
                if(!$contry_code) {
                    $validator->errors()->add($key,'Please enter valid country code.');
                }
                $date2 = DateTime::createFromFormat('d/m/Y', $data['date_of_birth_ddmmyyyy']);

                if($now < $date2) {
                    $validator->errors()->add($key,'The date of birth must be a date before today.');
                }
            }
        });
    }


    public function rules(): array
    {
        return [
            '*.email' => 'nullable|email',
            '*.phone_number_country_code_without_sign' => 'required|numeric',
            '*.phone_number_without_space_and_country_code' => 'required|numeric|digits:10'
        ];
    }

    public function customValidationMessages()
    {
        return [

            '*.email.email' => 'The email must be a valid email address..',
            '*.phone_number_country_code_without_sign' => 'The country filed is required.',
            '*.phone_number_without_space_and_country_code.required' => 'The mobile number filed is required.',
            '*.phone_number_without_space_and_country_code.digits' => 'The mobile number must be digits 10 .',
            '*.phone_number_without_space_and_country_code.digits.numeric' => 'The mobile number must be enter numeric.',
        ];
    }



}
