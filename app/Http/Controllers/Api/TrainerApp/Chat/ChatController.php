<?php

namespace App\Http\Controllers\Api\TrainerApp\Chat;

use App\Http\Resources\Api\TrainerApp\TrainerAppPlayersListingResource;
use App\Http\Resources\Chat\ChatGroupResource;
use App\Team;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\ChatGroup;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * @group TrainerApp / Chat
 *
 * API FOR TRAINERAPP CHAT
 */

class ChatController extends Controller
{
    /**
     * Create New Group
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "success",
    "Result":
    {
    “created_by”:null,
    “updated_by”:”2021-05-06 07:11:31”,
    “created_at”:”2021-05-06 07:11:31”,
    “id”:55
    }
    }
     *
     * @bodyParam title string optiona
     * @bodyParam members array required  eg: [1,2,3,4],
     * @bodyParam image file optional accept only jpeg, jpg & gif
     * @bodyParam team_id  optional
     * @bodyParam group_id integer used when editing an existing group
     * @bodyParam club_id integer required
     */
    /*public function saveGroup(Request $request)
    {
        {
            $this->validate($request,[
                'members' => 'nullable|array',
                'image' => 'nullable|file|mimes:jpeg,jpg,png',
                'group_id' => 'nullable',
                'club_id'  => 'required'
            ]);
            if(isset($request->team_id) && (isset($request->members) && count($request->members))){
                return Helper::apiErrorResponse(false, 'You cant select team and members at same time',new \stdClass());
            }
            if(!isset($request->team_id) && (isset($request->members) && count($request->members))){
                if(in_array(auth()->user()->id,$request->members)){
                    return Helper::apiErrorResponse(false, 'You can not add yourself in chat group',new \stdClass());
                }
            }
            if(isset($request->team_id))
            {
                $team_valid = DB::table('club_teams')->where('club_id',$request->club_id)->where('team_id',$request->team_id)->value('team_id');

                if(empty($team_valid))
                {
                    return Helper::apiErrorResponse(false, 'Team does not belong to the provided club',new \stdClass());
                }

            }

            $members = $request->members;
            if(isset($request->team_id)){
                $chat_group_exists = ChatGroup::where('team_id',$request->team_id)->first();
                if($chat_group_exists){
                    if (!empty($request->group_id))
                    {
                        $group = ChatGroup::where('id', $request->group_id)
                            ->first();

                        if (!$group)
                        {
                            $response = Helper::apiNotFoundResponse(false, 'No group found', new stdClass());
                        }
                    }
                    else
                    {
                        return Helper::apiErrorResponse(false, 'Chat group already exists for this team, use group_id in addition for editing', $chat_group_exists);
                    }

                }else{
                    $members = DB::table('player_team')->where('team_id',$request->team_id)->pluck('user_id')->toArray();
                    $members[] = auth()->user()->id;
                    $request->merge([
                        'members' => $members,
                    ]);
                    $group = new ChatGroup();
                }

                $save_group = $group->saveGroup($request);
                return Helper::apiSuccessResponse(true, 'success',$save_group);

            }
            if(!isset($request->members) || !is_array($request->members) || !count($request->members)){
                return Helper::apiErrorResponse(false, 'select members for group',new \stdClass());
            }
            $members = $request->members;
            $members[] = auth()->user()->id;
            $request->merge([
                'members' => $members,
            ]);

            $total_members = count($members);
            $chat_group_exists = DB::table('chat_groups')
                ->join('chat_group_members','chat_groups.id','chat_group_members.group_id')
                ->whereIn('chat_group_members.user_id',$members)->whereNull('chat_groups.team_id')
                ->groupByRaw("chat_group_members.group_id")->havingRaw("COUNT(*) = $total_members")->first();
            if($chat_group_exists){
                $chat_group = ChatGroup::find($chat_group_exists->group_id);
                if (!empty($request->group_id))
                {
                    $group = ChatGroup::where('id', $request->group_id)
                        ->first();

                    if (!$group)
                    {
                        $response = Helper::apiNotFoundResponse(false, 'No group found', new stdClass());
                    }
                }
                else{

                    return Helper::apiErrorResponse(false, 'Chat group exists, use group_id in addition if editing',$chat_group);
                }
            }else{

                $group = new ChatGroup();
            }
            $save_group = $group->saveGroup($request);
            $save_group->is_group = 'yes';
            $save_group->save();
            return Helper::apiSuccessResponse(true, 'success',$save_group);
        }
    }*/

    /**
     * Get Contacts
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "contacts",
    "Result": [
    {
    "id": 1,
    "name": "Group One",
    "picture": "https://lh3.googleusercontent.com/KNyKMfQqqVcLYAROYJ6KPW7nqmyMMcuc7npdzuzYI9KXhnZDJ3Wkfqy_apcQTDgq2QlNp9LzqQly06N5qsNxUOLT",
    "last_message": {
    "id": 8,
    "group_id": 1,
    "sender_id": 5,
    "message": "Hello world",
    "type": null,
    "ref_message_id": null,
    "created_at": "2021-03-02 19:17:05",
    "updated_at": "2021-03-02 19:17:05",
    "sender": {
    "id": 5,
    "name": "Alex",
    "profile_picture": null,
    "role": "trainer"
    }
    },
    "members": [
    {
    "id": 5,
    "name": "Alex",
    "profile_picture": null,
    "pivot": {
    "group_id": 1,
    "user_id": 5
    }
    },
    {
    "id": 1,
    "name": "Muhammed",
    "profile_picture": "media/users/5fa27263a93271604481635.jpeg",
    "pivot": {
    "group_id": 1,
    "user_id": 1
    }
    }
    ]
    },
    {
    "id": 2,
    "name": "Group 2",
    "picture": null,
    "last_message": {},
    "members": [
    {
    "id": 5,
    "name": "Alex",
    "profile_picture": null,
    "pivot": {
    "group_id": 2,
    "user_id": 5
    }
    }
    ]
    }
    ]
    }
     *
     * @return JsonResponse
     */
    /*public function contacts(Request $request){
        $groups = ChatGroup::with(['members'=>function($q){
            $q->selectRaw("users.id, CONCAT(users.first_name,' ',users.last_name) as name, users.profile_picture");
        },'last_message.sender'=>function($q){
            $q->selectRaw("users.id, CONCAT(first_name,' ',last_name) as name, profile_picture");
        }])->whereHas('members',function ($member){
            $member->where('users.id',auth()->id());
        })->with('team')->get()->sortByDesc(function ($f){
            return @$f->last_message->id;
        })->values();
        $data = ChatGroupResource::collection($groups);
        if(!$data){
            return Helper::apiErrorResponse(false, 'no contacts found',new \stdClass());
        }
        return Helper::apiSuccessResponse(true, 'contacts',$data);
    }*/

    /**
     * Get Players
     *
     * @response
     *
     * {
    "Response": true,
    "StatusCode": 200,
    "Message": "Records  found",
    "Result": {
    "data": [
    {
    "id": 128,
    "player_name": null,
    "profile_picture": "media/users/5f99cb1c40f871603914524.jpeg",
    "age": null,
    "gender": null,
    "position": [
    "Striker"
    ],
    "teams": [
    {
    "id": 2,
    "team_name": "Ajax U16",
    "image": "https://bloximages.newyork1.vip.townnews.com/gazettextra.com/content/tncms/assets/v3/editorial/e/a7/ea782551-1a85-5921-82f2-4730effe67cc/5b5744d4e67c7.image.jpg",
    "pivot": {
    "user_id": 128,
    "team_id": 2,
    "created_at": "2020-10-28 19:47:33"
    }
    }
    ]
    },
    {
    "id": 134,
    "player_name": null,
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [],
    "teams": []
    },
    {
    "id": 136,
    "player_name": null,
    "profile_picture": "media/users/5f9d8b664cb3f1604160358.jpeg",
    "age": null,
    "gender": null,
    "position": [
    "Striker"
    ],
    "teams": []
    },
    {
    "id": 137,
    "player_name": null,
    "profile_picture": null,
    "age": null,
    "gender": "woman",
    "position": [],
    "teams": []
    },
    {
    "id": 140,
    "player_name": null,
    "profile_picture": null,
    "age": null,
    "gender": null,
    "position": [
    "Left Midfield"
    ],
    "teams": []
    }
    ],
    "meta": {
    "current_page": 1,
    "first_page_url": "http://127.0.0.1:8000/api/v1/trainerapp/chat/players?page=1",
    "from": 1,
    "last_page": 28,
    "last_page_url": "http://127.0.0.1:8000/api/v1/trainerapp/chat/players?page=28",
    "next_page_url": "http://127.0.0.1:8000/api/v1/trainerapp/chat/players?page=2",
    "per_page": 5,
    "prev_page_url": null,
    "total": 137
    }
    }
    }
     *
     * @queryParam limit required integer records per page
     * @queryParam page integer for page number
     * @return JsonResponse
     * @queryParam limit required integer records per page
     * @queryParam page required integer for page number
     */
    /*public function club_players(Request $request){
        $request->validate([
            "limit"=>"required|integer|min:1",
            "page"=>"required|integer|min:1"
        ]);

        try{
            $clubs = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->pluck('club_id');
            $current_trainer_team_id = Auth::user()->teams_trainers?auth()->user()->teams_trainers->pluck('id')->toArray():[0];
            $players = User::role('player')
                ->select('users.id', 'users.first_name', 'users.middle_name', 'users.last_name', 'users.profile_picture', 'users.age', 'users.gender')
                ->with([
                    'teams' => function ($q) {
                        $q->select('teams.id', 'teams.team_name', 'teams.image');
                    },
                ])
                ->whereHas('teams', function ($q) use ($current_trainer_team_id) {
                    $q->whereIn('teams.id', $current_trainer_team_id);
                })
                ->whereHas('clubs_players', function ($q) use ($clubs) {
                    $q->whereIn('club_id', $clubs);
                })
                ->with(['position'=>function($position){
                    $position->select("name");
                }])
                ->orderBy('first_name','asc')
                ->where("first_name","!=",null)
                ->paginate($request->limit ?? 5);
            $meta = $players->toArray();
            if (count($players->values()->all()) > 0){
                $response = [
                    'data' => TrainerAppPlayersListingResource::collection($players->values()->all())->toArray($request),
                    'meta' => [
                        'current_page' => $meta['current_page'],
                        'first_page_url' => $meta['first_page_url'],
                        'from' => $meta['from'],
                        'last_page' => $meta['last_page'],
                        'last_page_url' => $meta['last_page_url'],
                        'next_page_url' => $meta['next_page_url'],
                        'per_page' => $meta['per_page'],
                        'prev_page_url' => $meta['prev_page_url'],
                        'total' => $meta['total']
                    ]
                ];
                return Helper::apiSuccessResponse(true, 'Records  found', $response);
            }
        }
        catch (\Exception $Ex){
            return Helper::apiErrorResponse(false,"Something Went Wrong",$Ex->getMessage());
        }
        return Helper::apiNotFoundResponse(false, 'Records Not found', []);
    }*/
}
