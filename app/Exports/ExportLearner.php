<?php

namespace App\Exports;

use App\Models\Learner;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportLearner implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
       // return Learner::all();
        return Learner::select('name','email')->get();
    }
}
