<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerIsReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    private $game_channel;
    private  $user;
    public function __construct($user,$game_channel)
    {
        $this->game_channel= $game_channel;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('battle_ai.'.$this->game_channel->id);
    }

    public function broadCastAs(){
        return 'PlayerIsReady';
    }
    public function broadCastWith(){


        return [
            'id'=>$this->user->id,
            'first_name'=>$this->user->first_name,
            'last_name'=>$this->user->last_name,
            'picture'=>$this->user->profile_picture,
            'is_ready'=>1,
            'rank'=>rand(2,22)
        ];
    }
}
