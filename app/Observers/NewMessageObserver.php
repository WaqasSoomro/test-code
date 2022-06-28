<?php

namespace App\Observers;
use App\ChatGroupMessage;
use App\Helpers\Helper;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class NewMessageObserver
{

    public function created(ChatGroupMessage $message){
        //mark message as read
        DB::table('chat_read_messages')->insert([
            'message_id'=>$message->id,
            'group_id'=>$message->group_id,
            'user_id'=>$message->sender_id,
        ]);


        //send notifications
        foreach ($message->group->members as $user) {
            if($message->sender_id!=$user->id) {
                $data['from_user_id'] = $message->sender_id;
                $data['to_user_id'] = $user->id;
                $data['model_type'] = 'new-message';
                $data['model_type_id'] = $message->group_id;
                $data['click_action'] = 'Chat';

                $data['message'] = [
                    'en' => $message->message,
                    'nl' => $message->message,
                ];
//            //todo apply conditions for other status
                $data['message'] = json_encode($data['message']);

                $title= [
                    'en' => $message->sender->first_name . ' ' . $message->sender->last_name . ' has sent you message',
                    'nl' => $message->sender->first_name . ' ' . $message->sender->last_name . ' heeft je bericht gestuurd',
                ];
                $data['title'] = $title[App::getLocale() ?? 'en'];
                $devices = $user->user_devices;
                
                $data['badge_count'] = $user->badge_count + 1;
                
                foreach ($user->user_devices as $device) {
                    Helper::sendNotification($data, $device->onesignal_token,$device->device_type);
                }
                $user->badge_count = $data['badge_count'];
                $user->save();
            }
        }
    }
    //
}
