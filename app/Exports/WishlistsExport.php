<?php

namespace App\Exports;

use App\Models\Wishlist;
use Maatwebsite\Excel\Concerns\FromArray;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class WishlistsExport implements FromArray, WithHeadings
{
    protected $wishlist;

    public function __construct(array $wishlist)
    {
        $this->wishlist = $wishlist;
    }

    public function headings(): array
    {
        return ["Name", "Email", "Mobile No", "Course Name", "Time"];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        return $this->wishlist;
    }
}
