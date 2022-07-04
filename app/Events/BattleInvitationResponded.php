<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BattleInvitationResponded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $invitation;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($invitation)
    {
        $this->invitation = $invitation;
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('battle_ai_lobby.'.$this->invitation->invited_by);
    }


    public function broadCastAs(){
        return 'BattleInvitationResponded';
    }

    public function broadCastWith(){
        return [
            'user_id'=>$this->invitation->host->id,
            'first_name'=>$this->invitation->host->first_name,
            'last_name'=>$this->invitation->host->last_name,
            'profile_picture'=>$this->invitation->host->profile_picture,
            'rank'=>rand(2,22),
            'status'=>$this->invitation->status,
            'battle_id'=>$this->invitation->battle->id,
            'game_channel_id'=>$this->invitation->battle->channel_id,
            'type'=>$this->invitation->battle->type,
        ];
    }
}
