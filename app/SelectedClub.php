<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SelectedClub extends Model
{
    //
    protected $table = 'selected_clubs';

    protected $fillable = ["club_id","trainer_user_id"];

    public $timestamps = false;
}
