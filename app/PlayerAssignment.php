<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlayerAssignment extends Model
{
    use SoftDeletes;
    protected $fillable = ['assignment_id','player_user_id','status_id'];
    protected $table = 'player_assignments';
    public $timestamps = true;

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function assignment(){
        return $this->belongsTo(Assignment::class);
    }
}
