<?php

namespace App\Imports;

use App\Helpers\Helper;
use App\Player;
use App\PricingPlan;
use App\User;
use App\Team;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Http\Request;
use Validator;
use stdClass;

class DashboardPlayersImport implements ToCollection, WithHeadingRow
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
            '*.team_name' => 'required|max:255|exists:teams,team_name',
            '*.first_name' => 'required|max:255',
            '*.last_name' => 'required|max:255',
            '*.phone' => 'required',
            '*.age_group' => 'required',
            '*.gender' => 'required|in:male,female,mixed',
        ];

        $validator = Validator::make($records, $validation);

        if($validator->fails()) {
            \Session::put('response_players_csv', $validator->messages()->toArray());
            return false;
        }
        $check = PricingPlan::checkAvailability(count($records), 'players');

        if($check) {
            \Session::put('response_players_csv', "limit_exceed");
            return false;
        }

        foreach($records as $record) {
            $team = Team::where('team_name', $record['team_name'])->first();
            if ($team) {
                $request = new stdClass();
                $request->team_id = $team->id;
                $request->first_name = $record['first_name'];
                $request->last_name = $record['last_name'];
                $request->phone = $record['phone'];
                $request->age = $record['age_group'];
                $request->gender = $record['gender'];
                $request->ip = '192.0.2.245';
                $request->imei = '490154203237518';
                $request->device_type = 'android';
                $request->add_explicitly = true;
                $request->club_manager = 1;

                $user = User::where('phone', $record['phone'])->whereHas('roles', function ($q) {
                    $q->where('roles.name', 'player');
                })->first();

                if(!$user) {
                    $user = new User();
                }
                $user->registerPlayer($request);
                $player = Player::where('user_id', $user->id)->first();

                if (!$player)
                {
                    $player = new Player();

                    $player->created_at = now();
                }
                else
                {
                    $player->updated_at = now();
                }

                $player->user_id= $user->id;
                $player->save();
            }
        }
        \Session::put('response_players_csv', 'success');
    }
}
