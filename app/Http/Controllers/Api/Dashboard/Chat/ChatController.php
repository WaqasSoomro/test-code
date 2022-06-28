<?php

namespace App\Http\Controllers\Api\Dashboard\Chat;

use App\ChatGroup;
use App\ChatGroupMessage;
use App\Events\GroupMessageSent;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Chat\ChatMessageResource;
use App\Jobs\NewMessageNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * @group Dashboard / Chat
 * APIs for dashboard chat________________________________
 * 1- For Messages broadcasting => Listen for new-message event on chat channel ||  syntax chat.{auth_user_id} eg: chat.2 _________________________________________________________
 * 2- To Listen Messages of specific group/room =>  Listen for new-message event on group channel || syntax group.{group_id} eg: group.5 ________________________________________________
 * listen for events on PORT NO 6001 eg : ws://localhost:6001/app/anyKey
 */

class ChatController extends Controller
{
    //

    /**
     * Send Message
     *
     * @response {
        "Response": true,
        "StatusCode": 200,
        "Message": "Message Sent",
        "Result": {
            "sender_id": 12,
            "message": "thanks for quick response",
            "msg_identification": "thanks for quick response",
            "reply_of": "8",
            "image": null,
            "file": null,
            "file_orignal_name": null,
            "gif_url": null,
            "group_id": 1,
            "updated_at": "2021-04-27 05:49:01",
            "created_at": "2021-04-27 05:49:01",
            "id": 9,
            "group": {
                "id": 1,
                "title": null,
                "created_by": 1,
                "team_id": null,
                "created_at": "2021-04-26 12:39:15",
                "updated_at": "2021-04-26 12:39:15",
                "club_id": null,
                "members": [
                    {
                        "id": 1,
                        "nationality_id": 1,
                        "first_name": "Shahzaib",
                        "middle_name": null,
                        "last_name": "Imran",
                        "surname": null,
                        "email": null,
                        "humanox_username": null,
                        "humanox_user_id": null,
                        "humanox_pin": null,
                        "humanox_auth_token": null,
                        "phone": "+923482302450",
                        "gender": null,
                        "language": null,
                        "address": null,
                        "profile_picture": "media/users/5fa27263a93271604481635.jpeg",
                        "date_of_birth": "1995-02-05",
                        "age": null,
                        "badge_count": 56,
                        "verification_code": "028478",
                        "verified_at": "2021-04-07 11:14:49",
                        "active": 0,
                        "status_id": 1,
                        "who_created": null,
                        "last_seen": "2021-04-26 13:07:34",
                        "online_status": "1",
                        "created_at": "2020-07-15 14:05:11",
                        "updated_at": "2021-04-27 05:49:02",
                        "deleted_at": null,
                        "pivot": {
                            "group_id": 1,
                            "user_id": 1
                        },
                        "user_devices": [
                            {
                                "id": 51,
                                "user_id": 1,
                                "device_type": "ios",
                                "device_token": "dasdasdsadasd",
                                "imei": null,
                                "udid": "21",
                                "mac_id": null,
                                "ip": "192.168.1.0",
                                "created_at": null,
                                "updated_at": "2020-11-04 09:07:19"
                            }
                        ]
                    },
                    {
                        "id": 12,
                        "nationality_id": 164,
                        "first_name": "Shahzaib",
                        "middle_name": "''",
                        "last_name": "Imran",
                        "surname": null,
                        "email": "shahzaib.imran@jogo.ai",
                        "humanox_username": null,
                        "humanox_user_id": null,
                        "humanox_pin": null,
                        "humanox_auth_token": null,
                        "phone": null,
                        "gender": null,
                        "language": null,
                        "address": null,
                        "profile_picture": null,
                        "date_of_birth": null,
                        "age": null,
                        "badge_count": 25,
                        "verification_code": null,
                        "verified_at": "2021-02-26 17:45:57",
                        "active": 0,
                        "status_id": 2,
                        "who_created": null,
                        "last_seen": "2021-04-27 05:49:01",
                        "online_status": "1",
                        "created_at": "2020-07-21 13:29:54",
                        "updated_at": "2021-04-27 05:49:01",
                        "deleted_at": null,
                        "pivot": {
                            "group_id": 1,
                            "user_id": 12
                        }
                    }
                ]
            },
            "sender": {
                "id": 12,
                "nationality_id": 164,
                "first_name": "Shahzaib",
                "middle_name": "''",
                "last_name": "Imran",
                "surname": null,
                "email": "shahzaib.imran@jogo.ai",
                "humanox_username": null,
                "humanox_user_id": null,
                "humanox_pin": null,
                "humanox_auth_token": null,
                "phone": null,
                "gender": null,
                "language": null,
                "address": null,
                "profile_picture": null,
                "date_of_birth": null,
                "age": null,
                "badge_count": 25,
                "verification_code": null,
                "verified_at": "2021-02-26 17:45:57",
                "active": 0,
                "status_id": 2,
                "who_created": null,
                "last_seen": "2021-04-27 05:49:01",
                "online_status": "1",
                "created_at": "2020-07-21 13:29:54",
                "updated_at": "2021-04-27 05:49:01",
                "deleted_at": null,
                "roles": [
                    {
                        "id": 2,
                        "name": "trainer",
                        "guard_name": "api",
                        "created_at": null,
                        "updated_at": null,
                        "pivot": {
                            "model_id": 12,
                            "role_id": 2,
                            "model_type": "App\\User"
                        }
                    },
                    {
                        "id": 4,
                        "name": "Lite",
                        "guard_name": "api",
                        "created_at": "2021-04-01 10:37:15",
                        "updated_at": "2021-04-01 10:37:15",
                        "pivot": {
                            "model_id": 12,
                            "role_id": 4,
                            "model_type": "App\\User"
                        }
                    }
                ]
            },
            "parent_message": {
                "id": 8,
                "group_id": 1,
                "sender_id": 12,
                "reply_of": null,
                "message": "thanks, I sent",
                "msg_identification": "",
                "image": null,
                "file": null,
                "file_orignal_name": null,
                "gif_url": null,
                "type": null,
                "ref_message_id": null,
                "created_at": "2021-04-26 12:59:45",
                "updated_at": "2021-04-26 12:59:45"
            }
        }
    }

     * @bodyParam chat_room_id integer required
     * @bodyParam reply_of integer optional id of those messages against you want to reply
     * @bodyParam message string optional required if image, file or gif input not exist 
     * @bodyParam msg_identification string required
     * @bodyParam attachment_type string optional 
     * @bodyParam image file optional accept only jpeg, jpg & gif
     * @bodyParam file file optional accept file
     * @bodyParam gif string optional url accept only
     * @bodyParam height integer optional 
     * @bodyParam width integer optional 
     * @return JsonResponse
     */

    /*public function sendMessage(Request $request)
    {
        $this->validate($request, [
            'chat_room_id' => 'required',
            'message' => 'required_without_all:image,file,gif',
            'msg_identification' => 'required',
            'attachment_type' => 'nullable|max:100',
            'gif' => 'nullable|url',
            'image' => 'nullable|file|mimes:jpeg,jpg,png',
            'file' => 'nullable|file',
            'height'=> 'nullable',
            'width' => 'nullable'
        ]);

        $chat_group = ChatGroup::whereHas('members', function($user)
        {
            $user->where('users.id', auth()->user()->id);
        })
        ->find($request->chat_room_id);

        if (!$chat_group)
        {
            return Helper::apiErrorResponse(false, 'Group not found', new \stdClass());
        }

        $image = NULL;
        $file = NULL;
        $file_orignal_name = NULL;
        $gif = NULL;

        if ($request->hasFile('image') && !empty($request->image))
        {
            $image = Storage::putFile('media/chats', $request->image);
        }
        else if ($request->hasFile('file') && empty($request->file))
        {
            $file = Storage::putFile('media/chats', $request->file);
            $file_orignal_name = $request->file->getClientOriginalName();
        }
        else
        {
            $gif = $request->gif;
        }
        if(!empty($request->height) && !empty($request->width))
        {
            $height=intval($request->height);
            $width=intval($request->width);
        }
        else
        {
            $height=0;
            $width=0;
        }


        $message = (new ChatGroupMessage())->setSender(auth()->user())->fill([
            'message' => $request->message,
            'msg_identification' => $request->msg_identification,
            'attachment_type' => $request->attachment_type,
            'reply_of' => $request->reply_of,
            'image' => $image,
            'file' => $file,
            'file_orignal_name' => $file_orignal_name,
            'gif_url' => $gif,
            'height' => $height,
            'width' => $width
        ]);

        $chat_group->messages()->save($message);

        broadcast(new GroupMessageSent(auth()->user(),$chat_group, $message, $message->parent_message))->toOthers();

        return Helper::apiSuccessResponse(true, 'Message Sent', $message);
    }*/

    /**
     * Get  Messages
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "success",
    "Result": {
        "group": {
            "id": 40,
            "name": "Jogo Group",
            "is_group": "no",
            "picture": null,
            "team": {}
        },
        "messages": [
            {
                "id": 395,
                "message": "you",
                "msg_identification": "",
                "attachment_type": "",
                "image": "",
                "file": "",
                "file_orignal_name": "",
                "gif": "",
                "height": 0,
                "width": 0,
                "is_read": 0,
                "created_at": "2021-04-05T17:34:32.000000Z",
                "reply_of": {},
                "sender": {
                    "id": 11,
                    "name": "Saad Saleem",
                    "profile_picture": "media/users/5f92d95717cbc1603459415.jpeg",
                    "role": "player"
                }
            },
            {
                "id": 394,
                "message": "fine shine",
                "msg_identification": "",
                "attachment_type": "",
                "image": "",
                "file": "",
                "file_orignal_name": "",
                "gif": "",
                "height": 0,
                "width": 0,
                "is_read": 0,
                "created_at": "2021-04-05T17:34:30.000000Z",
                "reply_of": {},
                "sender": {
                    "id": 11,
                    "name": "Saad Saleem",
                    "profile_picture": "media/users/5f92d95717cbc1603459415.jpeg",
                    "role": "player"
                }
            },
            {
                "id": 393,
                "message": "How are you",
                "msg_identification": "",
                "attachment_type": "",
                "image": "",
                "file": "",
                "file_orignal_name": "",
                "gif": "",
                "height": 0,
                "width": 0,
                "is_read": 0,
                "created_at": "2021-04-05T17:34:14.000000Z",
                "reply_of": {},
                "sender": {
                    "id": 2,
                    "name": "Fatima Sultana",
                    "profile_picture": "media/users/606c4314623c11617707796.jpeg",
                    "role": "player"
                }
            },
            {
                "id": 339,
                "message": "Hi",
                "msg_identification": "",
                "attachment_type": "",
                "image": "",
                "file": "",
                "file_orignal_name": "",
                "gif": "",
                "height": 0,
                "width": 0,
                "is_read": 0,
                "created_at": "2021-04-05T14:31:40.000000Z",
                "reply_of": {},
                "sender": {
                    "id": 2,
                    "name": "Fatima Sultana",
                    "profile_picture": "media/users/606c4314623c11617707796.jpeg",
                    "role": "player"
                }
            },
            {
                "id": 338,
                "message": "hello",
                "msg_identification": "",
                "attachment_type": "",
                "image": "",
                "file": "",
                "file_orignal_name": "",
                "gif": "",
                "height": 0,
                "width": 0,
                "is_read": 0,
                "created_at": "2021-04-05T14:23:40.000000Z",
                "reply_of": {},
                "sender": {
                    "id": 11,
                    "name": "Saad Saleem",
                    "profile_picture": "media/users/5f92d95717cbc1603459415.jpeg",
                    "role": "player"
                }
            }
        ],
        "members": [
            {
                "id": 2,
                "current_player_id": 40,
                "first_name": "Fatima",
                "middle_name": null,
                "last_name": "Sultana",
                "profile_picture": "media/users/606c4314623c11617707796.jpeg",
                "role": "player",
                "follow_status": true,
                "is_admin": 0
            },
            {
                "id": 11,
                "current_player_id": 40,
                "first_name": "Saad",
                "middle_name": "''",
                "last_name": "Saleem",
                "profile_picture": "media/users/5f92d95717cbc1603459415.jpeg",
                "role": "player",
                "follow_status": false,
                "is_admin": 0
            },
            {
                "id": 7,
                "current_player_id": 40,
                "first_name": "Fatima",
                "middle_name": "''",
                "last_name": "Sultana",
                "profile_picture": "",
                "role": "player",
                "follow_status": false,
                "is_admin": 0
            },
            {
                "id": 389,
                "current_player_id": 40,
                "first_name": "Famiiii",
                "middle_name": "''",
                "last_name": "2",
                "profile_picture": "media/users/605c7a3e1a79a1616673342.jpeg",
                "role": "player",
                "follow_status": false,
                "is_admin": 0
            }
        ]
    }
}
     *
     * @bodyParam chat_room_id integer required
     * @bodyParam offset integer required| start from 0 increment according to requirement
     * @bodyParam limit integer required  | represents total records to be fetched
     * @bodyParam club_id integer required
     * @return JsonResponse
     */
    /*public function getMessages(Request $request){
        $this->validate($request,[
            'chat_room_id'=>'required',
            'limit'=>'required',
            'offset'=>'required',
            'club_id'=>'required'
        ]);
        
        $chat_group = ChatGroup::whereHas('members',function($user){
            $user->where('users.id',auth()->user()->id);
        
        })
        ->where(function ($query) use($request)
        {
            $query->where('club_id', $request->club_id)
            ->orWhere('club_id', NULL);
        })
        ->find($request->chat_room_id);
        if(!$chat_group){
            return Helper::apiErrorResponse(false, 'Group not found',new \stdClass());
        }
        
        $messages = ChatGroupMessage::
        with('sender:id,first_name,last_name,profile_picture', 'parent_message')
            ->where('group_id',$chat_group->id)->offset($request->offset)->limit($request->limit)->orderBy('id','DESC')->get();
        
        $get_messages= (ChatMessageResource::collection($messages));
        //$sender_id=ChatGroupMessage::with('sender:id')->where('group_id',$chat_group->id)->value();
        
        if($get_messages->count()){
            return Helper::apiSuccessResponse(true, 'success',$get_messages);
        }


        return Helper::apiErrorResponse(false, 'messages not found',new \stdClass());
    }*/

    /**
     * Mark as Read
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "success",
    "Result": {}
    }
     *
     * @bodyParam chat_room_id integer required
     * @return JsonResponse
     */

    /*public function markAsRead(Request  $request)
    {
        $this->validate($request, [
            'chat_room_id' => 'required'
        ]);
        $chat_group = ChatGroup::find($request->chat_room_id);
        $get_read_messages = DB::table('chat_read_messages')->where('group_id',$chat_group->id)->where('user_id',auth()->user()->id)->pluck('message_id');
        $messages = ChatGroupMessage::where('group_id',$chat_group->id)->whereNotIn('id',$get_read_messages)->get();
       foreach ($messages as $message){
           DB::table('chat_read_messages')->insert([
               'message_id'=>$message->id,
               'group_id'=>$chat_group->id,
               'user_id'=>auth()->user()->id,
           ]);
       }
        return Helper::apiSuccessResponse(true, 'success',new \stdClass());
    }*/
}