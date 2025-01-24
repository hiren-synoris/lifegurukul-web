<?php

namespace App\Imports;

use App\Helper\Helper;
use App\Models\Learner;
use App\Models\CoursePlan;
use App\Models\UserCourse;
use App\Models\Course;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Validators\Failure;
use DB;

class ImportOnlyLearner implements
ToModel,
// WithHeadingRow,
WithMultipleSheets,
WithValidation,
WithChunkReading,
ShouldQueue,
WithStartRow,
WithEvents
// ,SkipsOnError,
// SkipsOnFailure
{
    private $courseId;
    private $planId;
    // private $setStartRow = 2;
    use Importable, RegistersEventListeners;
    // ,SkipsErrors, SkipsFailures;
    public function  __construct()
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



        $notification=[];
        $mobile=[];
        $email=[];
        $ids=[];
        $learner_count = 0;
        if(isset($row[0]) && $row[0] != 'Name' && isset($row[3]) && $row[3] != 'Phone Number'){
            if($row[3] != '' && $row[2] != '')
            {
                $learner = Learner::where('mobile',$row[3])->where('country_id', $row[2])->first();
                
                if(!$learner){
                    $learner = Learner::create(
                        [
                            'mobile' => $row[3],
                            'country_id' => $row[2],
                            'name' =>  $row[0],
                            'email' =>  $row[1] ,
                            'gender' =>  $row[4],
                            'd_o_b' =>  $row[5]
                        ]
                    );

                }
                // return $learner;
            }
        }


    }

    public function rules(): array
    {
        return [
            '0' => 'required',
            '1' => 'required|unique:learners,email',
            // Rule::unique('service_details','service_id')->ignore($this->route()->id)->where(function ($query) {
            //         $query->where('user_id', $this->request->get('user_id'));
            // })
            // '2' => 'required|unique:countries,term,NULL,id,taxonomy,category'
            '2' => 'required|numeric',
            '3' => 'required|min:10|unique:learners,mobile',
            '4' => 'required|in:'.implode(",",Learner::GENDER),
            '5' => 'date_format:Y-m-d|before:today'
        ];
    }
    public function chunkSize(): int
    {
        return 1000;
    }

    public static function afterImport(AfterImport $event)
    {
    }

    public function onFailure(Failure ...$failure)
    {
    }

}
