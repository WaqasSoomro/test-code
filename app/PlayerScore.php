<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlayerScore extends Model
{
    protected $fillable = ['user_id', 'exercise_id', 'level_id', 'skill_id', 'score'];
    protected $table = 'player_scores';
    public $timestamps = true;

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function createPlayerScore($pl_ex,$player_id,$value){
        PlayerScore::create([
            'user_id' => $player_id,
            'exercise_id' => $pl_ex->exercise_id,
            'level_id' => $pl_ex->level_id,
            'skill_id' => $value['skill_id'],
            'score' => $value['score']
        ]);
    }
}
