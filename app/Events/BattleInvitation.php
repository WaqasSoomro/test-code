<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BattleInvitation implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    protected $battle;
    protected $user;
    protected $invitation;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('battle_ai_lobby.'.$this->invitation->user_id);
    }

    public function broadCastAs(){
        return 'BattleInvitation';
    }

    public function broadCastWith(){
        return [
            'id'=>$this->invitation->id,
            'battle_id'=>$this->invitation->battle_id,
            'battle_type'=>$this->invitation->battle->type,
            'rounds'=>$this->invitation->battle->rounds,
            'game_channel_id'=>$this->invitation->battle->channel_id,
        ];
    }
}
