<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameIsReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $game;
    private $battle;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($battle,$game)
    {
        $this->battle = $battle;
        $this->game = $game;
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('battle_ai.'.$this->game);
    }

    public function broadCastAs(){
        return 'ReadyToStart';
    }
    public function broadCastWith(){
        return [
            'battle'=>$this->battle,
        ];
    }
}
