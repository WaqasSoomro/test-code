<?php

namespace App\Imports;

use App\Club;
use App\Helpers\Helper;
use App\PricingPlan;
use App\Team;
use App\Trainer;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Validator;
use Exception;

class DashboardTrainersImport implements ToCollection, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function collection(Collection $rows)
    {
        $records = collect($rows)->toArray();
        $validator = Validator::make($records, [
            '*.first_name' => 'nullable|max:191',
            '*.last_name' => 'nullable|max:191',
            '*.email' => 'required',
            '*.team_name' => 'required|max:255|exists:teams,team_name',
        ]);
        if($validator->fails()) {
            \Session::put('response_team_csv', $validator->messages()->toArray());
            return false;
        }
        $club = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->first();
        if (!$club) {
            \Session::put('response_team_csv', "Club not found");
            return false;
        }
//        $check = PricingPlan::checkAvailability(count($records), 'trainers');
//        if($check) {
//            \Session::put('response_team_csv', "Your Plan exceeds the limit, kindly upgrade your plan to add more trainers");
//            return  false;
//        }
        try{
           DB::beginTransaction();
           foreach ($records as $val) {
               $team = Team::where('team_name',$val['team_name'])->first();
               $row = new \stdClass();
               $trainer = User::where('email', $val['email'])->whereHas('roles', function ($q) {
                   $q->where('roles.name', 'trainer');
               })->first();
               if(!$trainer){
                   $trainer = new User();
               }
               $row->first_name = $val['first_name'];
               $row->last_name = $val['last_name'];
               $row->email = $val['email'];
               $row->surname = isset($val['surname'])?$val['surname']:'';
               $row->phone = isset($val['phone'])?$val['phone']:'';
               $row->password = 'jogo123';
               $row->ip = '192.0.2.245';
               $row->imei = '490154203237518';
               $row->mac_id = '490154203237518';
               $row->device_type = 'web';
               $row->add_explicitly = true;
               $row->nationality_id = isset($val['nationality'])?$val['nationality']:152;
               $otp_code = Helper::generateOtp();
               $row->verification_code = $otp_code;
               $user = $trainer->registerWebUser($row);
               $user->teams_trainers()->sync($team);
               $user->clubs_trainers()->sync($club->club_id);
                
                $mailData = [
                    'user' => $user
                ];

               try {
                    Helper::sendMail('emails.send_reset_link', 'Welcome to JOGO', $mailData, $user);
               } catch (Exception $e) {
                   activity()->causedBy($user)->performedOn($user)->log($e->getMessage());
               }
           }
           DB::commit();
            \Session::put('response_team_csv', 'success');
        }catch (\Exception $e){
            \Session::put('response_team_csv', $e->getMessage());

            DB::rollBack();
        }
    }
}
