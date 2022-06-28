<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Player extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'user_id','height','weight',
    ];


    public $timestamps = false;


    /*
	* If you need to set all columns as fillable, do this in the model:
	**/

    /** protected $guarded = []; **/


    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function customaryFoot()
    {
        return $this->belongsTo(CustomaryFoot::class,'customary_foot_id');
    }

    public function streams()
    {
        return $this->hasMany('App\Stream');
    }

    public function positions()
    {
        return $this->belongsToMany(Position::class, 'player_positions', 'player_id', 'position_id');
    }

    public function players ($request, $teamId)
    {
        $players = $this::select("id", "user_id")
        ->whereHas("user", function ($query)
        {
            $query->where("users.status_id", 1);
        })
        ->whereHas("user.teams", function ($query) use($teamId)
        {
            $query->where("player_team.team_id", $teamId);
        })
        ->get()
        ->toArray();

        return $players;
    }
}