<?php


namespace App\Imports;

use Throwable;
use Carbon\Carbon;
use App\Models\Learner;
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

class NewImportUser implements
   // ToModel,
   ToCollection,
   WithValidation,
   WithChunkReading,
    WithHeadingRow,
    SkipsOnError,
    SkipsOnFailure

{
    use Importable, SkipsErrors,SkipsFailures;

    public function  __construct()
    {
        // $this->courseId= $courseId;
        // $this->planId= $planId;

    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $contry_code =  DB::table('countries')->where("phonecode",$row['phone_number_country_code_without_sign'])->first()->id;
            $learner = Learner::where('mobile',$row['phone_number_without_space_and_country_code'])->where('country_id', $contry_code)->first();
            if(!$learner){
                $learner = Learner::create(
                    [
                        'mobile' => isset($row['phone_number_without_space_and_country_code']) ? $row['phone_number_without_space_and_country_code'] : "",
                        'country_id' => $contry_code,
                        'name' =>  $row['name'],
                        'email' =>  $row["email"] ,
                        'gender' =>  isset($row["gender1male2female3other"]) ? $row["gender1male2female3other"] : "",
                        'd_o_b' =>   isset($row['date_of_birth_ddmmyyyy']) ? \DateTime::createFromFormat('d/m/Y', $row['date_of_birth_ddmmyyyy']) : ""
                    ]
                );
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
                $date2 = \DateTime::createFromFormat('d/m/Y', $data['date_of_birth_ddmmyyyy']);
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
            '*.phone_number_without_space_and_country_code' => 'required|numeric|unique:learners,mobile|digits:10'
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
            '*.phone_number_without_space_and_country_code.unique' => 'The mobile has already been taken.',

        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }



}
