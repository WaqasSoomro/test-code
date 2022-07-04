<?php
namespace App\Events;
use App\Http\Resources\Chat\ChatMessageResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use stdClass;

class GroupMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversation;
    public $sender;
    public $group;
    public $parent_message;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public function __construct($user, $group, $conversation, $parent_message = NULL)
    {
        $this->conversation = $conversation;
        $this->sender = $user;
        $this->group = $group;
        $this->parent_message = isset($parent_message) ? $parent_message : new stdClass();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
    */

    /*public function broadcastOns()
    {
        return new PrivateChannel('groups.' . $this->conversation->group_id);
    }*/

    public function broadcastOn()
    {
        $channels = [];

        foreach ($this->group->members as $user)
        {
            //if($user->id!=auth()->user()->id){
                array_push($channels, new PresenceChannel('chat.'.$user->id));
            //}
        }

        array_push($channels, new PresenceChannel('group.'.$this->conversation->group_id));

        return $channels;
    }

    public function broadcastAs()
    {
        return 'new-message';
    }

    public function broadcastWith()
    {
        $message = new ChatMessageResource($this->conversation);

        return [
            'message' => $message,
            'group' => $this->group
        ];
    }
}