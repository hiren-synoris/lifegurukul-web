<?php

namespace App\Exports;

use App\Models\Course;
use Maatwebsite\Excel\Concerns\FromArray;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class CourseOrdersExport implements FromArray, WithHeadings
{
    protected $course_orders;

    public function __construct(array $course_orders)
    {
        $this->course_orders = $course_orders;
    }

    public function headings(): array
    {
        return ["OrderID", "Title", "Learner", "Status", "Amount", "Date", "LastLoginDate"];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        return $this->course_orders;
    }
}
