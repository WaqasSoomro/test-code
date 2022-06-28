<?php

namespace App\Imports;

use App\Club;
use App\Team;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Validator;

class DashboardTeamsImport implements ToCollection, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
        $records = collect($rows)->toArray();

        $validation = [
            '*.team_name' => 'required|max:255|unique:teams,team_name',
            //'*.age_group' => 'required',
            '*.min_age_group' => 'required|numeric',
            '*.max_age_group' => 'required|numeric',
            '*.image' => 'url',
            '*.club_name' => 'required|exists:clubs,title'
        ];

        $validator = Validator::make($records, $validation);

        if($validator->fails()) {
            \Session::put('response_team_csv', $validator->messages()->toArray());
            return false;
        }

        foreach($records as $record) {
            $club = Club::where('title', $record['club_name'])->first();
            if ($club) {
                $team = new Team([
                    'team_name' => $record['team_name'],
                    //'age_group' => $record['age_group'],
                    'min_age_group' => $record['min_age_group'],
                    'max_age_group' => $record['max_age_group'],
                    'image' => $record['image']
                ]);
                $team->save();

                $team->clubs()->syncWithoutDetaching([$club->id]);
                $team->trainers()->syncWithoutDetaching([Auth::user()->id]);

            }
        }
        \Session::put('response_team_csv', 'success');
    }
}
