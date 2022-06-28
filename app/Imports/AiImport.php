<?php

namespace App\Imports;

use App\ExerciseAiData;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class AiImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $records = collect($collection)->toArray();
        return $records[0];
    }
}
