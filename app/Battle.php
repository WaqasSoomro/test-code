<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use App\Helpers\Helper;

class Battle extends Model
{
    protected $table = 'battles';
    public $timestamps = true;

    /**
     * The users that belong to the achievement.
     */
    
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function team()
    {
        return $this->belongsTo('App\Team', 'team_id');
    }

    public function rounds_exercises()
    {
        return $this->hasMany('App\BattleRound', 'battle_id');
    }

    public function results()
    {
        return $this->hasMany('App\BattleResult', 'battle_id');
    }

    public function battle_invites(){
        return $this->hasMany(BattleInvite::class,'battle_id');
    }
    public function players()
    {
        return $this->hasMany('App\UserBattle', 'battle_id');
    }

    public function roundResults($request)
    {
        $request->validate([
            'battle_id' => 'required|numeric',
            'round' => 'required|numeric'
        ]);

        $battle = Battle::with([
            'results' => function ($query) use($request)
            {
                $query->where('round', $request->round)
                ->orderBy('position','asc');
            },
            'results.user' => function ($query)
            {
                $query->select('id', 'first_name', 'last_name', 'profile_picture', 'online_status');
            }
        ])
        ->find($request->battle_id);

        return Helper::apiSuccessResponse(true, 'Success', $battle);
    }
}