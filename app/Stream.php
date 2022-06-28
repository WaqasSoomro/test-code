<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Stream extends Model
{
    const STATUS_LIVE = 1;
    const STATUS_OFFLINE = -1;

    protected $table = 'player_streams';

    public $timestamps = true;

    protected $fillable = [
        'player_id', 'status', 'stream_token'
    ];

    public function player()
    {
        return $this->belongsTo('App\Player');
    }

    public function createStream($player_id)
    {
        DB::transaction(function () use ($player_id) {
            $this->player_id    = $player_id;
            $this->stream_token = uniqid();
            $this->status       = self::STATUS_LIVE;
            $this->start_at     = new \DateTime();
            $this->save();
        });

        return $this;
    }

    public function stopStream()
    {
        DB::transaction(function () {
            $this->status = self::STATUS_OFFLINE;
            $this->end_at = new \DateTime();
            $this->save();
        });

        return $this;
    }
}
