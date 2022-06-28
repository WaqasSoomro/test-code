<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\PlayerScore;

class PlayerExercise extends Model
{
    use SoftDeletes;
    protected $fillable = ['user_id','exercise_id', 'level_id', 'start_time', 'end_time','thumbnail', 'video_file',
        'completion_time', 'status_id', 'assignment_id'];

    protected $table = 'player_exercise';
    public $timestamps = true;
    public static $media = 'media/player_exercises';
    
    protected $appends = ['scores'];

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function getScoresAttribute()
    {
        $record = PlayerScore::select('score')
        ->where('user_id', $this->user_id)
        ->where('exercise_id', $this->exercise_id)
        ->orderBy('created_at', 'desc')
        ->first();

        return $record;
    }

    public function updatePlayerExercise($request,$player_id){
        $pl_ex = PlayerExercise::where('id', $request->player_exercise_id)
            ->where('user_id', $player_id)
            ->first();

        $pl_ex->completion_time = $request->completion_time;
        $pl_ex->end_time = now();
        $pl_ex->status_id = $status->id ?? null;
        $pl_ex->save();

        return $pl_ex;
    }
}
