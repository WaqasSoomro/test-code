<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Helper;

class PlayerTeamRequest extends Model
{
    protected $table = 'player_team_requests';

    protected $fillable = [
        'team_id',
        'player_user_id',
        'status'
    ];

    public function player()
    {
        return $this->belongsTo(User::class,'player_user_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class,'team_id');
    }

    public function acceptTeamRequests($request){
        Helper::acceptTeamRequest($request);
        return Helper::apiSuccessResponse(true, 'Team requests accepted', new \stdClass());
    }

    public function rejectTeamRequests($request){

        $team_request = PlayerTeamRequest::find($request->request_id);

        if(!$team_request){
            return Helper::apiErrorResponse(false, 'Invalid ID', new \stdClass());
        }

        if($team_request->status==2){
            return  Helper::apiErrorResponse(false,'invalid request',new \stdClass());
        }
        $team_request->delete();
        return Helper::apiSuccessResponse(true, 'Team requests rejected', new \stdClass());
    }
    
    public function teamRequests($request)
    {
        $request->validate([
            'team_id' => 'required|integer'
        ]);

        $teamRequests = PlayerTeamRequest::with([
            'player' => function ($query)
            {
                $query->select('users.id', 'users.first_name', 'users.last_name', 'users.profile_picture');
            },
            'player.player' => function ($query)
            {
                $query->select('players.id', 'players.user_id');
            },
            'player.player.positions' => function ($query)
            {
                $query->select('positions.id', 'positions.name', 'positions.lines');
            },
            'team' => function ($query)
            {
                $query->select('teams.id', 'teams.team_name');
            }
        ])
        ->where('team_id', $request->team_id)
        ->whereNotIn('status', [2, 3])
        ->whereHas('player');
        
        $totalReamRequests = $teamRequests->count();

        $teamRequests = $teamRequests->get();

        if ($totalReamRequests > 0)
        {
            $filterTeamRequests = [];

            foreach ($teamRequests as $value)
            {
                $filterTeamRequests[] = [
                    'id' => $value->id,
                    'player_id' => $value->player->id,
                    'player_name' => $value->player->first_name.' '.$value->player->last_name,
                    'profile_picture' => $value->player->profile_picture ?? '',
                    'positions' => $value->player->player->positions ?? [],
                    'team' => $value->team->team_name ?? '',
                    'applied_team' => $value->team->team_name ?? ''
                ];
            }

            $response = Helper::apiSuccessResponse(true, 'Team requests found', $filterTeamRequests);
        }
        else
        {
            $response = Helper::apiSuccessResponse(true, 'No requests found', []);
        }

        return $response;
    }
}
