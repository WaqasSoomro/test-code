<?php

namespace App\Jobs;

use App\Helpers\Helper;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class NewMessageNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $conversation;
    public $sender;
    public $group;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user,$group,$conversation)
    {
        //
        $this->conversation = $conversation;
        $this->sender = $user;
        $this->group = $group;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data=[];
        foreach ($this->group->members as $user) {
            $data['from_user_id'] =$this->sender->id;
            $data['to_user_id'] = $user->id;
            $data['model_type'] = 'new-message';
            $data['model_type_id'] = $this->sender->id;
            $data['click_action'] = 'OthersProfile';
            $data['message']=[
                'en'=>$this->sender->first_name . ' ' . $this->sender->last_name . ': '. $this->conversation->message,
                'nl'=>$this->sender->first_name . ' ' . $this->sender->last_name . ': '. $this->conversation->message,
            ];
//            //todo apply conditions for other status
            $data['message'] = json_encode($data['message']);
            $devices = $user->user_devices;
            $tokens = [];
            foreach ($devices as $device) {
                if ($device->device_token) {
                    array_push($tokens, $device->device_token);
                }
            }
            $data['badge_count'] = $user->badge_count + 1;
            Helper::sendNotification($data, $tokens);
            $user->badge_count = $data['badge_count'];
            $user->save();
        }
    }
}
