<?php

namespace App\Imports;

use App\Models\WorksOn;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithConditionalSheets;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class ExcelImport implements WithMultipleSheets
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    use WithConditionalSheets;

    public function conditionalSheets(): array
    {   
        return [ 
            'Employees' => new FirstSheetImport(),
            'Assignment' => new SecondSheetImport(),
        ];
    }
    public function batchSize(): int
    {
        return 10000;
    }
    
    public function chunkSize(): int
    {
        return 10000;
    }

}
