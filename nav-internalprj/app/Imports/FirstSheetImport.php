<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\HasReferencesToOtherSheets;
use DB;

class FirstSheetImport implements ToCollection, HasReferencesToOtherSheets
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        // dd($dataProject);   
    }
}
