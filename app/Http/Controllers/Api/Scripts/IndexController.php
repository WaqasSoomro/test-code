<?php
namespace App\Http\Controllers\Api\Scripts;
use App\Event;
use App\EventMatchType;
use App\EventRepetition;
use App\EventType;
use App\Exercise;
use App\ExercisePrivacy;
use App\Gender;
use App\Http\Controllers\Controller;
use App\Team;
use App\TeamPrivacy;
use App\TeamType;
use App\User;
use Illuminate\Http\Request;
use Exception;
use DB;

class IndexController extends Controller
{
    private $usersModel;

    public function __construct()
    {
        $this->usersModel = new User();
    }

    protected function createPlayersPositions()
    {
        DB::beginTransaction();

        try
        {
            $players = $this->usersModel
            ->select('id')
            ->with([
                'player' => function ($query)
                {
                    $query->select('id', 'user_id', 'position_id');
                },
                'player.positions' => function ($query)
                {
                    $query->select('player_id', 'positions.id');
                }
            ])
            ->whereHas('player')
            ->whereDoesntHave('player.positions')
            ->orderBy('created_at', 'desc');

            $totalPlayers = $players->count();

            $players = $players->get();

            if ($totalPlayers > 0)
            {
                foreach ($players as $player)
                {
                    $player->player->positions()->sync($player->player->position_id);

                    $player->player->position_id = NULL;
                    $player->player->save();

                    DB::commit();
                }
            }
        }
        catch (Exception $ex)
        {
            DB::rollBack();  

            return 'File: '.$ex->getFile().', Line: '.$ex->getLine().', Msg: '.$ex->getMessage();
        }

        return 'success';
    }

    public function updateUsers(){
        $users = User::get();
        $genders = Gender::get()->toArray();
        foreach ($users as $user){
            $user_gender = str_replace('_',' ',$user->gender);
            $gender_id = 0;
            foreach ($genders as $gender){
                if(strtolower($user_gender) == strtolower($gender['type'])){
                    $gender_id = $gender['id'];
                    break;
                }
            }
            $user->gender = $gender_id;
            $user->save();
        }
    }

    public function updateTeams(){
        $teams = Team::get();
        $team_privacies = TeamPrivacy::get()->toArray();
        $team_types = TeamType::get()->toArray();
        $genders = Gender::get()->toArray();
        foreach ($teams as $team){
            $team_privacy = str_replace('_',' ',$team->privacy);
            $privacy_id = 0;
            foreach ($team_privacies as $privacy){
                if(strtolower($team_privacy) == strtolower($privacy['name'])){
                    $privacy_id = $privacy['id'];
                    break;
                }
            }

            $team_type = str_replace('_',' ',$team->team_type);
            $type_id = 0;
            foreach ($team_types as $type){
                if(strtolower($team_type) == strtolower($type['name'])){
                    $type_id = $type['id'];
                    break;
                }
            }

            $team_gender = str_replace('_',' ',$team->gender);
            $gender_id = 0;
            foreach ($genders as $gender){
                if(strtolower($team_gender) == strtolower($gender['type'])){
                    $gender_id = $gender['id'];
                    break;
                }
            }

            $team->privacy = $privacy_id;
            $team->gender = $gender_id;
            $team->team_type = $type_id;

            $team->save();
        }
    }

    public function updateExercises(){
        $exercises = Exercise::get();
        $exercise_privacy = ExercisePrivacy::get()->toArray();
        $privacy_id = 0;
        foreach ($exercises as $exercise){
            foreach ($exercise_privacy as $privacy){
                if(strtolower($exercise->privacy) == 'my_team'){
                    if(strtolower($privacy['name']) == 'only my teams'){
                        $privacy_id = $privacy['id'];
                        break;
                    }
                }else{
                    if(strtolower($privacy['name']) == 'only my clubs'){
                        $privacy_id = $privacy['id'];
                        break;
                    }
                }
            }

            $exercise->privacy = $privacy_id;
            $exercise->save();
        }
    }

    public function updateEvents(){
        $events = Event::get();
        $event_repetitions = EventRepetition::get();
        $event_types = EventType::get();
        $event_match_types = EventMatchType::get();
        foreach ($events as $event){
            foreach ($event_repetitions as $event_repetition){
                $new_event_repetition = 1;
                if(strtolower($event->repetition) == strtolower($event_repetition->title)){
                    $new_event_repetition = $event_repetition->id;
                    break;
                }
            }


            foreach ($event_types as $event_type){
                $new_event_type = 1;
                if(strtolower($event->event_type) == strtolower($event_type->title)){
                    $new_event_type= $event_type->id;
                    break;
                }
            }

            foreach ($event_match_types as $event_match_type){
                $new_event_match_type = 1;
                if(strtolower($event->playing_area) == strtolower($event_match_type->title)){
                    $new_event_match_type= $event_match_type->id;
                    break;
                }
            }

            $event->repetition = $new_event_repetition;
            $event->event_type = $new_event_type;
            $event->playing_area = $new_event_match_type;

            $event->save();
        }
    }
}