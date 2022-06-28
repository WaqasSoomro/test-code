<?php
namespace App\Http\Controllers\Api\Chat;
use App\ChatGroup;
use App\ChatGroupMessage;
use App\Events\GroupMessageSent;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Chat\ChatGroupResource;
use App\Http\Resources\Chat\ChatMessageResource;
use App\Jobs\NewMessageNotification;
use App\User;
use App\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use stdClass;

/**
 * @group  Chat
 * APIs for player chat________________________________________
 * 1- For Messages broadcasting => Listen for new-message event on chat channel ||  syntax chat.{auth_user_id} eg: chat.5 _______________________________________________
 * 2- To Listen Messages of specific group/room =>  Listen for new-message event on group channel || syntax group.{group_id} eg: group.5
 * listen for events on PORT NO 6001 eg : ws://localhost:6001/app/anyKey
 */

class ChatController extends Controller
{
    /**
     * Send Message
     *
     * @response {
        "Response": true,
        "StatusCode": 200,
        "Message": "Message Sent",
        "Result": {
            "sender_id": 1,
            "message": "thanks for quick response, yup it's working fine for me",
            "msg_identification": "thanks for quick response, yup it's working fine for me",
            "reply_of": "9",
            "group_id": 1,
            "updated_at": "2021-04-27 06:25:12",
            "created_at": "2021-04-27 06:25:12",
            "id": 25,
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
                        "last_seen": "2021-04-27 06:25:12",
                        "online_status": "1",
                        "created_at": "2020-07-15 14:05:11",
                        "updated_at": "2021-04-27 06:25:12",
                        "deleted_at": null,
                        "pivot": {
                            "group_id": 1,
                            "user_id": 1
                        }
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
                        "badge_count": 41,
                        "verification_code": null,
                        "verified_at": "2021-02-26 17:45:57",
                        "active": 0,
                        "status_id": 2,
                        "who_created": null,
                        "last_seen": "2021-04-27 05:51:02",
                        "online_status": "1",
                        "created_at": "2020-07-21 13:29:54",
                        "updated_at": "2021-04-27 06:25:13",
                        "deleted_at": null,
                        "pivot": {
                            "group_id": 1,
                            "user_id": 12
                        },
                        "user_devices": [
                            {
                                "id": 149,
                                "user_id": 12,
                                "device_type": "web",
                                "device_token": "dfsdffsdfsdfdffd",
                                "imei": null,
                                "udid": null,
                                "mac_id": "1234567890",
                                "ip": "192.168.0.1",
                                "created_at": "2020-07-30 13:09:32",
                                "updated_at": "2021-04-07 11:18:27"
                            }
                        ]
                    }
                ]
            },
            "sender": {
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
                "last_seen": "2021-04-27 06:25:12",
                "online_status": "1",
                "created_at": "2020-07-15 14:05:11",
                "updated_at": "2021-04-27 06:25:12",
                "deleted_at": null,
                "roles": [
                    {
                        "id": 1,
                        "name": "player",
                        "guard_name": "api",
                        "created_at": null,
                        "updated_at": null,
                        "pivot": {
                            "model_id": 1,
                            "role_id": 1,
                            "model_type": "App\\User"
                        }
                    }
                ]
            },
            "parent_message": {
                "id": 9,
                "group_id": 1,
                "sender_id": 12,
                "reply_of": 8,
                "message": "hope it works for you",
                "msg_identification": "",
                "image": null,
                "file": null,
                "file_orignal_name": null,
                "gif_url": null,
                "type": null,
                "ref_message_id": null,
                "created_at": "2021-04-27 10:56:06",
                "updated_at": "2021-04-27 05:49:01"
            }
        }
    }

     * @bodyParam chat_room_id integer required when available
     * @bodyParam receiver integer required chat room id in not avb first time
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

    /*public function sendMessage(Request  $request)
    {
        $this->validate($request,[
            'message' => 'required_without_all:image,file,gif',
            'msg_identification' => 'required',
            'attachment_type' => 'nullable|max:100',
            'gif' => 'nullable|url',
            'image' => 'nullable|file|mimes:jpeg,jpg,png,gif',
            'file' => 'nullable|file',
            'height'=> 'nullable',
            'width' => 'nullable' 
        ]);

        if(!$request->chat_room_id && !$request->receiver)
        { 
            //if no chat room id and no members are given

            return Helper::apiErrorResponse(false, 'specify chat room id or select chat user', new \stdClass());
        }

        if($request->chat_room_id && $request->receiver)
        { 
            //if  chat room id and  members both are given

            return Helper::apiErrorResponse(false, 'provide any one chatroom id or receiver ', new \stdClass());
        }

        $chat_room_id = $request->chat_room_id;

        if (!$request->chat_room_id)
        {
            //check if room is already created for auth user and other member

            $chat_group_exists = DB::table('chat_group_members')
            ->selectRaw("group_id, GROUP_CONCAT(DISTINCT user_id ORDER BY user_id) AS members")
            ->groupBy("group_id")
            ->having("members","=",auth()->user()->id.','.$request->receiver)
            ->orHaving("members","=",$request->receiver.','.auth()->user()->id)
            ->first();

            if ($chat_group_exists)
            {
                $chat_room_id = $chat_group_exists->group_id;
            }
            else
            {
                //create room for users

                $request->merge([
                    'members' => [auth()->user()->id, $request->receiver],
                ]);

                $group = new ChatGroup();
                $save_group = $group->saveGroup($request);
                $chat_room_id = $save_group->id;
            }
        }

        if (!$chat_room_id)
        {
            return Helper::apiErrorResponse(false, 'specify chat room id or select chat users',new \stdClass());
        }

        $chat_group = ChatGroup::whereHas('members', function($user)
        {
            $user->where('users.id',auth()->user()->id);
        })
        ->find($chat_room_id);

        if (!$chat_group)
        {
            return Helper::apiErrorResponse(false, 'Group not found',new \stdClass());
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

        broadcast(new GroupMessageSent(auth()->user(), $chat_group, $message, $message->parent_message))->toOthers();
        DB::table('chat_group_members')->where('group_id',$request->chat_room_id)->where('user_id',auth()->user()->id)->update(['clear_history'=>'no']);
        //$notify= dispatch((new NewMessageNotification(auth()->user(),$chat_group,$message))->delay(1));

        return Helper::apiSuccessResponse(true, 'Message Sent', $message);
    }*/

    /**
     * Get  Messages
     *
     * @response
     * {
    "Response": true,
    "StatusCode": 200,
    "Message": "success",
    "Result": {
    "data": {
    "group": {
    "id": 8,
    "name": "Fami Sultana, Fatima Sultana, abdul Haseeb, Saad Saleem, tr 2 rerum, Umer Shaikh",
    "is_group": "no",
    "picture": "https://bloximages.newyork1.vip.townnews.com/gazettextra.com/content/tncms/assets/v3/editorial/e/a7/ea782551-1a85-5921-82f2-4730effe67cc/5b5744d4e67c7.image.jpg",
    "team": {
    "id": 2,
    "team_name": "Ajax U16",
    "image": "https://bloximages.newyork1.vip.townnews.com/gazettextra.com/content/tncms/assets/v3/editorial/e/a7/ea782551-1a85-5921-82f2-4730effe67cc/5b5744d4e67c7.image.jpg"
    }
    },
    "messages": [
    {
    "id": 1089,
    "message": "Ok",
    "msg_identification": "1623078536354",
    "attachment_type": "null",
    "image": "",
    "file": "",
    "file_orignal_name": "",
    "gif": "",
    "height": 0,
    "width": 0,
    "is_read": 0,
    "created_at": "2021-06-07T15:08:57.000000Z",
    "reply_of": {
    "id": 1086,
    "message": "Hi",
    "msg_identification": "1623078067430",
    "image": "",
    "file": "",
    "file_orignal_name": "",
    "gif": "https://media3.giphy.com/media/6R2mLi910HL4VXFwOG/giphy.gif?cid=806fc6faotyin0vdfzzg3g9fum25s9api7sy4ysxi04s1x6l&rid=giphy.gif&ct=g",
    "is_read": 0,
    "created_at": "2021-06-07T15:01:08.000000Z",
    "sender": {
    "id": 40,
    "name": "Umer Shaikh",
    "profile_picture": "media/users/OF4YBPrj3vkx7QtIztDKOAZod45t1sSsXQfc1cPE.jpeg",
    "role": "trainer"
    }
    },
    "sender": {
    "id": 40,
    "name": "Umer Shaikh",
    "profile_picture": "media/users/OF4YBPrj3vkx7QtIztDKOAZod45t1sSsXQfc1cPE.jpeg",
    "role": "trainer"
    }
    }
    ],
    "members": [
    {
    "id": 6,
    "current_player_id": 10,
    "first_name": "Fami",
    "middle_name": "''",
    "last_name": "Sultana",
    "profile_picture": "",
    "club_logo": [],
    "role": "player",
    "follow_status": false,
    "team_id": null,
    "team_name": null,
    "position_id": 5,
    "position_name": "Center Midfield",
    "is_admin": 0
    },
    {
    "id": 7,
    "current_player_id": 10,
    "first_name": "Fatima",
    "middle_name": "''",
    "last_name": "Sultana",
    "profile_picture": "",
    "club_logo": [],
    "role": "player",
    "follow_status": false,
    "team_id": null,
    "team_name": null,
    "position_id": null,
    "position_name": null,
    "is_admin": 0
    },
    {
    "id": 9,
    "current_player_id": 10,
    "first_name": "abdul",
    "middle_name": "''",
    "last_name": "Haseeb",
    "profile_picture": "",
    "club_logo": [],
    "role": "player",
    "follow_status": false,
    "team_id": null,
    "team_name": null,
    "position_id": 2,
    "position_name": "Right Back",
    "is_admin": 0
    },
    {
    "id": 11,
    "current_player_id": 10,
    "first_name": "Saad",
    "middle_name": "''",
    "last_name": "Saleem",
    "profile_picture": "media/users/5f92d95717cbc1603459415.jpeg",
    "club_logo": [],
    "role": "player",
    "follow_status": false,
    "team_id": 50,
    "team_name": "Rahat 18",
    "position_id": 5,
    "position_name": "Center Midfield",
    "is_admin": 0
    },
    {
    "id": 12,
    "current_player_id": 10,
    "first_name": "tr 2",
    "middle_name": "''",
    "last_name": "rerum",
    "profile_picture": "",
    "club_logo": [
    "media/clubs/zaQhgOYMatJmbHsLdUZUqvorR5dl3XDBXzebgeWw.jpg"
    ],
    "role": "trainer",
    "follow_status": false,
    "team_id": null,
    "team_name": null,
    "position_id": null,
    "position_name": null,
    "is_admin": 0
    },
    {
    "id": 40,
    "current_player_id": 10,
    "first_name": "Umer",
    "middle_name": null,
    "last_name": "Shaikh",
    "profile_picture": "media/users/OF4YBPrj3vkx7QtIztDKOAZod45t1sSsXQfc1cPE.jpeg",
    "club_logo": [],
    "role": "trainer",
    "follow_status": false,
    "team_id": null,
    "team_name": null,
    "position_id": null,
    "position_name": null,
    "is_admin": 0
    }
    ]
    },
    "meta": {
    "current_page": 1,
    "next_page": 2
    }
    }
    }
     *
     * @queryParam chat_room_id integer optional
     * @queryParam receiver integer optional
     * @queryParam limit integer required  | represents total records to be fetched
     * @queryParam page optional integer
     * @return JsonResponse
     */

    /*public function getMessages(Request $request){
        $this->validate($request,[
            'limit'=>'required',
        ]);
        if(!$request->chat_room_id && !$request->receiver){ //if no chat room id and no members are given
            return Helper::apiErrorResponse(false, 'specify chat room id or select chat user',new \stdClass());
        } if($request->chat_room_id && $request->receiver){ //if  chat room id and  members both are given
            return Helper::apiErrorResponse(false, 'provide any one chatroom id or receiver ',new \stdClass());
        }
        if($request->receiver == auth()->user()->id){
            return Helper::apiErrorResponse(false, 'invalid receiver id ',new \stdClass());
        }
        $chat_room_id = $request->chat_room_id;
        if(!$request->chat_room_id) {
            //check if room is already created for auth user and other member
            $chat_group_exists = DB::table('chat_group_members')
                ->selectRaw("group_id, GROUP_CONCAT(DISTINCT user_id ORDER BY user_id) AS members")
                ->groupBy("group_id")
                ->having("members","=",auth()->user()->id.','.$request->receiver)
                ->orHaving("members","=",$request->receiver.','.auth()->user()->id)
                ->first();
            if ($chat_group_exists) {
                $chat_room_id = $chat_group_exists->group_id;
            }else{
                //create new chat room
                $request->merge([
                    'members' => [auth()->user()->id, $request->receiver],
                ]);
                $chat_group = new ChatGroup();
                $chat_group = $chat_group->saveGroup($request);
                $chat_group->load('members');
                $chat_room_id = $chat_group->id;
            }

        }
        $chat_group = ChatGroup::with(['members'=>function($q){
            $q->with(["clubs_players"=> function($player){
                $player->select("image");
            }]);
            $q->selectRaw("users.id, users.first_name,users.middle_name,users.last_name, users.profile_picture,CONCAT(users.first_name,' ',users.last_name) as name")->with('roles')   
            ->with(['teams' => function($team){
                $team->select('team_id','team_name','user_id');
            },'position' => function($position){
                $position->select('user_id','name','position_id');
            }])
            ->Where('user_id', '!=', auth()->user()->id);
        },'team:id,team_name,image'])->whereHas('members',function($user){
            $user->where('users.id',auth()->user()->id);
        })->find($chat_room_id);
        if (!$chat_group)
        {
            //return Helper::apiErrorResponse(false, 'no chat room found',new \stdClass());

            //create new chat room
            $request->merge([
                'members' => [auth()->user()->id, $request->receiver],
            ]);
            $chat_group = new ChatGroup();
            $chat_group = $chat_group->saveGroup($request);
            $chat_group->load('members');
            $chat_room_id = $chat_group->id;

            $chat_group = ChatGroup::with([
                'members' => function($q)
                {
                    $q->with(["clubs_players"=> function($player){
                        $player->select("image");
                    }]);
                    $q->selectRaw("users.id, users.first_name,users.middle_name,users.last_name, users.profile_picture,CONCAT(users.first_name,' ',users.last_name) as name")->with('roles')
                    ->with(['teams' => function($team){
                        $team->select('team_id','team_name','user_id');
                    },'position' => function($position){
                        $position->select('user_id','name','position_id');
                    }])
                    ->Where('user_id', '!=', auth()->user()->id);
            },'team:id,team_name,image'])->whereHas('members',function($user){
                $user->where('users.id',auth()->user()->id);
            })->find($chat_room_id);
        }

        $messages = ChatGroupMessage::with('parent_message')
        ->whereDoesntHave('deleted_messages', function($deleted_message)
        {
            $deleted_message->where('deleted_by','=',auth()->user()->id);
        })
        ->with('sender:id,first_name,last_name,profile_picture')
        ->where('group_id',$chat_room_id)
        ->orderBy('id','DESC')
            ->paginate($request->limit ?? 5);

        $messages_data = $messages->values()->all();
        $messages_meta = $messages->toArray();

        $get_messages= (ChatMessageResource::collection($messages_data));
        $group = $chat_group;
        
        $group_members = $group->members;

        $group_info = [
            'id' => $group->id,
            'name' => $group->title,
            'is_group' => $group->is_group
        ];
        if($group->title == null && $group_members->count()){
            $group_name =implode(", ", $group_members->pluck('name')->toArray());
            $group_info['name'] = $group_name;
        }
        $group_info['picture'] = null;
        $group_info['team'] = $group->team ?? new stdClass();


        $group_members= $group_members->map(function($member) use($group){
            $follow_status = false;
            if(auth()->user()->followers()->find($member->id)){
                $follow_status = true;
            }elseif(auth()->user()->followings()->find($member->id)){
                $follow_status = true;
            }
            return [
                'id'=>$member->id,
                "current_player_id"=>auth()->user()->id,
                'first_name'=>$member->first_name,
                'middle_name'=>$member->middle_name,
                'last_name'=>$member->last_name,
                'profile_picture'=>$member->profile_picture ?? "",
                'club_logo'=>count($member->clubs_players) > 0 ? $member->clubs_players->pluck("image") : [],
                'role'=>$member->roles?$member->roles[0]->name:"",
                "follow_status"=>$follow_status,
                "team_id"=>$member->teams[0]->team_id??null,
                "team_name"=>$member->teams[0]->team_name??null,
                "position_id"=>$member->position[0]->position_id??null,
                "position_name"=>$member->position[0]->name??null,
                'is_admin' => count($group->admins) > 0 && in_array($member->id, $group->admins->pluck('id')->toArray()) ? 1 : 0
            ];
        });

        if (count($group_members) == 1)
        {
            $group_info['picture'] = isset($group_members[0]) ? $group_members[0]['profile_picture'] : "";
            //$group_info['picture']=$group_members[0]->profile_picture;
        }
        if(isset($group->team)){
            $group_info['picture'] = $group->team->image;
        }

        if ($group->image)
        {
            $group_info['picture'] = $group->image;
        }

        $records["data"] = [
              "group" =>$group_info,
              'messages'=>$get_messages,
              'members'=>$group_members
        ];
        $records["meta"] = [
            "current_page"=>$messages_meta["current_page"],
            "next_page"=>(int) (substr($messages_meta["next_page_url"],-1)),
        ];

        return Helper::apiSuccessResponse(true, 'success',$records);
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
        //mark notifications as read
        $notifications = UserNotification::where('to_user_id', '=', Auth::user()->id)->where('model_type','new-message')
            ->update(['status_id'=>9]);

        return Helper::apiSuccessResponse(true, 'success',new \stdClass());
    }*/

    /**
     * Mark as Unread
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

    /*public function markAsUnread(Request $request){
        $this->validate($request, [
            'chat_room_id' => 'required'
        ]);

        DB::table('chat_read_messages')->where([
            'group_id'=>$request->chat_room_id,
            'user_id'=>auth()->user()->id,
        ])->orderBy('message_id','DESC')->limit(1)->delete();
        return Helper::apiSuccessResponse(true, 'success',new \stdClass());
    }*/

    /**
     * Delete Message
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

    /*public function deleteMessage(Request  $request){
        $this->validate($request,[
            'chat_room_id'=>'required'
        ]);
        
        $messages = ChatGroupMessage::where('group_id',$request->chat_room_id)->pluck('id');
        auth()->user()->deleted_messages()->syncWithoutDetaching($messages);
        DB::table('chat_group_members')->where('group_id',$request->chat_room_id)->where('user_id',auth()->user()->id)->update(['clear_history'=>'yes']);
        return Helper::apiSuccessResponse(true, 'success',new \stdClass());
    }*/
}