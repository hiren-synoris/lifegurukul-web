<?php

namespace App\Imports;

use App\Models\Learner;
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

class ImportLearner implements
ToModel,
// WithHeadingRow,
WithMultipleSheets,
WithValidation,
WithChunkReading,
// ShouldQueue,
WithStartRow,
// WithEvents
SkipsOnError,
SkipsOnFailure
// SkipsOnFailure

{
    use Importable, SkipsErrors, SkipsFailures;

    private $courseId;
    private $planId;
    // private $setStartRow = 2;

    // ,SkipsErrors, SkipsFailures;
    public function __construct()
    {
        // $this->courseId= $courseId;
        // $this->planId= $planId;

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

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        $notification = [];
        $mobile = [];
        $email = [];
        $ids = [];
        $learner_count = 0;
        if (isset($row[2]) && $row[2] != 'Name' && isset($row[3]) && $row[3] != 'Phone Number') {
           
            if ($row[3] != '' && $row[2] != '') {

                if ($row['2'] == 1) {
                    $contry_code = DB::table('countries')->where("id", 231)->first()->id;
                } else {

                    $contry_code = DB::table('countries')->where("phonecode", $row['2'])->first()->id;
                }
                $learner = Learner::where('mobile', $row[3])->where('country_id', $contry_code)->first();

                if (!$learner) {

                    $learner = Learner::create(
                        [
                            'mobile' => $row[3],
                            'country_id' => $contry_code,
                            'name' => $row[0],
                            'email' => $row[1],
                            'gender' => $row[4],
                            // 'd_o_b' =>  $row[5]
                            'd_o_b' => $row['5'] != null ?  \DateTime::createFromFormat('d/m/Y', $row['5']) : null,
                        ]
                    );

                }

            }
        }

    }
    public function withValidator($validator)
    {

        // dd($validator->getData());
        $validator->after(function ($validator) {
            $now = Carbon::now();
            foreach ($validator->getData() as $key => $data) {
                $contry_code = DB::table('countries')->where("phonecode", $data['2'])->first();
                if (!$contry_code) {
                    $validator->errors()->add($key, 'Please enter valid country code.');
                }

                if ($data['2'] == 1) {
                    $contry_code = DB::table('countries')->where("id", 231)->first()->id;
                } else {

                    $contry_code = DB::table('countries')->where("phonecode", $data['2'])->first()->id;
                }
                $learner = Learner::where('mobile', $data[3])->where('country_id', $contry_code)->first();
                if($learner) {
                    $validator->errors()->add($key, 'The mobile has already been taken.');
                }

                $date2 = \DateTime::createFromFormat('d/m/Y', $data['5']);

                if ($now < $date2) {
                    $validator->errors()->add($key, 'The date of birth must be a date before today.');
                }
            }
        });
    }

    public function rules(): array
    {
        $now = Carbon::now()->format('d-m-Y');

        return [
            // '0' => 'required',
            // '1'=>"required",
            '*.1' => 'nullable|email',
            // Rule::unique('service_details','service_id')->ignore($this->route()->id)->where(function ($query) {
            //         $query->where('user_id', $this->request->get('user_id'));
            // })
            // '2' => 'required|unique:countries,term,NULL,id,taxonomy,category'
            '*.2' => 'required|numeric',
            // '*.3' => 'required|digits:10|unique:learners,mobile',
            '*.3' => 'required|numeric',
            // '4' => 'required|in:'.implode(",",Learner::GENDER),
            // '*.5' => 'nullable|date_format:j/n/Y|before:'. $now,
        ];
    }

    public function customValidationMessages()
    {
        return [
            // '*.1.required' => 'The email filed is required.',
            '*.1.email' => 'The email must be a valid email address..',
            // '*.1' => 'The email has already been taken.',

            '*.2' => 'The country filed is required.',

            '*.3.required' => 'The mobile number filed is required.',
            // '*.3.required' => 'The mobile number must be digits 10 .',
            '*.3.numeric' => 'The mobile number must be enter numeric.',
            // '*.3' => 'The mobile has already been taken.',

            // '*.5.date_format' => 'The date of birth does not match the format d/m/Y.',
            // '*.5.before' => 'The date of birth must be a date before today.',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    // public static function afterImport(AfterImport $event)
    // {
    // }

    // public function onFailure(Failure ...$failure)
    // {
    // }

}
