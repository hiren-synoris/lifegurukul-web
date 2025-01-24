<?php

namespace App\Jobs;

use App\Models\Course;
use App\Imports\CourseImport;
use Illuminate\Bus\Queueable;
use App\Imports\CourseImport1;
use App\Imports\ImportLearner;
use App\Mail\ImportExcelError;
use App\Mail\ImportExcelLearner;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ImportExcelErrorJob1 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $data;

    public function __construct($request)
    {
        $this->data = $request;

        // dd($this->request);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // dd($this->data['import_file']);

        $planId = isset($this->data['plan_id']) && !empty($this->data['plan_id']) ? $this->data['plan_id'] : "";
        // $file = $request->file('import')->store('import123');
        $file = $this->data['import_file'];
        if(isset($this->data['course_id'])) {
            // dd($this->data['course_id']);

            $importLearner = new CourseImport1($this->data['course_id'], $planId);
            $rows = Excel::toCollection($importLearner, $file)->first();

            $filteredRows = $rows->filter(function ($row) {
                return !empty(array_filter($row->toArray()));
            });

            $filteredFullFilePath = Storage::disk('public')->path($file);

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            foreach ($filteredRows as $index => $row) {
                $rowIndex = $index + 1;
                foreach ($row as $colIndex => $cell) {
                    $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex, $cell);
                }
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filteredFullFilePath);


            $importLearner->import($filteredFullFilePath);

            Storage::delete($file);

        } else {

            $importLearner = new ImportLearner();
            $rows = Excel::toCollection($importLearner, $file)->first();

            $filteredRows = $rows->filter(function ($row) {
                    return !empty(array_filter($row->toArray()));
            });

            $filteredFullFilePath = Storage::disk('public')->path($file);

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            foreach ($filteredRows as $index => $row) {
                $rowIndex = $index + 1;
                foreach ($row as $colIndex => $cell) {
                    $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex, $cell);
                }
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filteredFullFilePath);

            $importLearner->import($filteredFullFilePath);
            Storage::delete($file);
        }

        $msg ="";
        if ($importLearner->failures()->isNotEmpty()) {
            $data["failures"] = $importLearner->failures();
            $course_name = @Course::where("id",$this->data['course_id'])->select("title")->first();
            if($course_name) {
                $msg = "Learner import for ".$course_name->title." course | LifeGurukul";
            } else {
                $msg = "Learner Import". ' |'. "LifeGurukul";
            }
            $inst = new ImportExcelError($msg,$data["failures"]);
            $to = $this->data['email'];
            if(!empty($inst) && !empty($to)){
                Mail::to($to)->send($inst);
            }
        }
    }
}
