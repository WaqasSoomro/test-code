<?php

namespace App\Exports;

use App\Team;
use Maatwebsite\Excel\Concerns\FromCollection;

class DashboardTeamsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Team::select('team_name', 'age_group', 'min_age_group', 'max_age_group', 'image');
    }
}
