<?php

namespace App\Http\Controllers\Api\Dashboard\Chat;

use App\ChatGroup;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * @group Dashboard V4 / Chat
 * APIs for dashboard chat
 */

class GroupController extends Controller
{
    /**
        Get User Groups

        @response
        {
            "Response": true,
            "StatusCode": 200,
            "Message": "success",
            "Result": [
                {
                    "id": 1,
                    "title": "First Group",
                    "image": "media/chats/groups/HufjNY2GCOgV6upYtSmwq9oLc2pQWId7WaMMK1JI.jpg",
                    "created_by": 1,
                    "team_id": null,
                    "created_at": "2021-04-27 13:02:08",
                    "updated_at": "2021-04-27 13:02:08",
                    "club_id": null
                },
                {
                    "id": 3,
                    "title": "Third Group",
                    "image": null,
                    "created_by": 1,
                    "team_id": null,
                    "created_at": "2021-04-27 13:02:32",
                    "updated_at": "2021-04-27 13:02:32",
                    "club_id": null
                }
            ]
        }
    **/

    /*public function userGroups(){
        $groups = ChatGroup::whereHas('members',function($user){
            $user->where('users.id',auth()->id());
        })->get();
        if($groups->count()){
            return Helper::apiSuccessResponse(true, 'success',$groups);
        }
        return Helper::apiErrorResponse(false, 'groups not found',new \stdClass());
    }*/


    /**
     * Create / Edit  Group
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "success",
    "Result": {}
    }
     *
     * @bodyParam title string optiona
     * @bodyParam members array required  eg: [1,2,3,4],
     * @bodyParam image file optional accept only jpeg, jpg & gif
     * @bodyParam team_id  optional
     * @bodyParam group_id integer used when editing an existing group
     * @bodyParam club_id integer required
     * @return JsonResponse
     */

    /*public function saveGroup(Request $request){
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
        }
    }*/

    /**
     * Add group Members
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "success",
    "Result": {}
    }
     *
     * @bodyParam group_id integer required
     * @bodyParam members array required [1,2,3,4]
     * @return JsonResponse
     */

    /*public function saveGroupMembers(Request $request){
        $this->validate($request,[
            'group_id'=>'required',
            'members'=>'required|array'
        ]);
        $group = ChatGroup::find($request->group_id);
        if(!$group){
            return Helper::apiErrorResponse(false, 'group not found',new \stdClass());
        }
        $group->members()->syncWithoutDetaching($request->members);
        return Helper::apiSuccessResponse(true, 'success',new \stdClass());
    }*/

    /**
        Remove Group Member

        @response
        {
            "Response": true,
            "StatusCode": 200,
            "Message": "Record has deleted successfully",
            "Result": {}
        }

        @response 500
        {
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid Id",
            "Result": {}
        }

        @queryParam group_id required integer. Example: 1
        @queryParam member_id required integer. Example: 1
    **/

    /*public function removeMember(Request $request)
    {
        try
        {
            $this->validate($request, [
                'group_id' => 'required',
                'member_id' => 'required'
            ]);

            $record = ChatGroup::where('id', $request->group_id)
            ->whereHas('admins', function ($query)
            {
                $query->where('user_id', auth()->user()->id);
            })
            ->first();

            if (!$record)
            {
                return Helper::apiErrorResponse(false, 'Invalid Id', new stdClass());
            }

            $record->members()->detach([$request->member_id]);

            return Helper::apiSuccessResponse(true, 'Record has deleted successfully', new stdClass());
        }
        catch (Exception $e)
        {
            return Helper::apiErrorResponse(false, 'Something wen\'t wrong', new stdClass());
        }
    }*/


    /**
        Get Group Members

        @response
        {
            "Response": true,
            "StatusCode": 200,
            "Message": "success",
            "Result": {
                "id": 1,
                "title": "First Group",
                "image": "media/chats/groups/HufjNY2GCOgV6upYtSmwq9oLc2pQWId7WaMMK1JI.jpg",
                "created_by": 1,
                "team_id": null,
                "created_at": "2021-04-27 13:02:08",
                "updated_at": "2021-04-27 13:02:08",
                "club_id": null,
                "members": [
                    {
                        "id": 12,
                        "first_name": "Shahzaib",
                        "last_name": "Imran",
                        "profile_picture": null,
                        "pivot": {
                            "group_id": 1,
                            "user_id": 12
                        }
                    },
                    {
                        "id": 1,
                        "first_name": "Shahzaib",
                        "last_name": "Imran",
                        "profile_picture": "media/users/5fa27263a93271604481635.jpeg",
                        "pivot": {
                            "group_id": 1,
                            "user_id": 1
                        }
                    }
                ],
                "admins": [
                    {
                        "id": 1,
                        "first_name": "Shahzaib",
                        "last_name": "Imran",
                        "profile_picture": "media/users/5fa27263a93271604481635.jpeg",
                        "pivot": {
                            "group_id": 1,
                            "user_id": 1
                        }
                    }
                ]
            }
        }
    **/

    /*public function getGroupMembers(Request  $request){
        $this->validate($request,[
            'group_id'=>'required'
        ]);
        $group = ChatGroup::with([
            'members:id,first_name,last_name,profile_picture',
            'admins' => function ($query)
            {
                $query->select('users.id', 'first_name', 'last_name', 'profile_picture');
            }
        ])
        ->find($request->group_id);
        if(!$group){
            return Helper::apiErrorResponse(false, 'group not found',new \stdClass());
        }
        return Helper::apiSuccessResponse(true, 'success',$group);
    }*/

    /**
        Delete Group

        @response
        {
            "Response": true,
            "StatusCode": 200,
            "Message": "Record has deleted successfully",
            "Result": {}
        }

        @response 500
        {
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid Id",
            "Result": {}
        }
    **/

    /*public function deleteGroup($id)
    {
        try
        {
            $record = ChatGroup::where('id', $id)
            ->whereHas('admins', function ($query)
            {
                $query->where('user_id', auth()->user()->id);
            });

            $record = $record->first();

            if ($record)
            {
                if ($record->delete())
                {
                    $record = Helper::apiSuccessResponse(true, 'Record has deleted successfully', new stdClass());
                }
                else
                {
                    $record = Helper::apiErrorResponse(false, 'Something wen\'t wrong', new stdClass());
                }
            }
            else
            {
                $record = Helper::apiNotFoundResponse(false, 'Invalid Id', new stdClass());
            }
        }
        catch (Exception $e)
        {
            $record = Helper::apiErrorResponse(false, 'Something wen\'t wrong', new stdClass());
        }

        return $record;
    }*/

    /**
        Add Group Admin

        @response
        {
            "Response": true,
            "StatusCode": 200,
            "Message": "Record has added successfully",
            "Result": {}
        }

        @response 500
        {
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid Id",
            "Result": {}
        }

        @queryParam group_id required integer. Example: 1
        @queryParam user_id required integer. Example: 1
    **/

    /*public function addAdmin(Request $request)
    {
        try
        {
            $record = ChatGroup::where('id', $request->group_id)
            ->whereHas('admins', function ($query)
            {
                $query->where('user_id', auth()->user()->id);
            })
            ->whereHas('members', function ($query) use($request)
            {
                $query->where('user_id', $request->user_id);
            })
            ->first();

            if ($record)
            {
                $record->admins()->syncWithoutDetaching([$request->user_id]);

                $record = Helper::apiSuccessResponse(true, 'Record has added successfully', new stdClass());
            }
            else
            {
                $record = Helper::apiNotFoundResponse(false, 'Invalid Id', new stdClass());
            }
        }
        catch (Exception $e)
        {
            $record = Helper::apiErrorResponse(false, 'Something wen\'t wrong', new stdClass());
        }

        return $record;
    }*/

    /**
        Remove Group Admin

        @response
        {
            "Response": true,
            "StatusCode": 200,
            "Message": "Record has deleted successfully",
            "Result": {}
        }

        @response 500
        {
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }

        @response 404
        {
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid Id",
            "Result": {}
        }

        @queryParam group_id required integer. Example: 1
        @queryParam user_id required integer. Example: 1
    **/

    /*public function removeAdmin(Request $request)
    {
        try
        {
            $record = ChatGroup::where('id', $request->group_id)
            ->whereHas('admins', function ($query)
            {
                $query->where('user_id', auth()->user()->id);
            })
            ->whereHas('members', function ($query) use($request)
            {
                $query->where('user_id', $request->user_id);
            })
            ->first();

            if ($record)
            {
                $adminExist = $record->admins()
                ->where('user_id', $request->user_id)
                ->first();

                if ($adminExist)
                {
                    $record->admins()->detach([$request->user_id]);

                    $record->members()->detach([$request->user_id]);

                    $record = Helper::apiSuccessResponse(true, 'Record has deleted successfully', new stdClass());
                }
                else
                {
                    $record = Helper::apiNotFoundResponse(false, 'Invalid Id', new stdClass());
                }
            }
            else
            {
                $record = Helper::apiNotFoundResponse(false, 'Invalid Id', new stdClass());
            }
        }
        catch (Exception $e)
        {
            $record = Helper::apiErrorResponse(false, 'Something wen\'t wrong', new stdClass());
        }

        return $record;
    }*/
}