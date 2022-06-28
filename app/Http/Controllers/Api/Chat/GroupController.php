<?php

namespace App\Http\Controllers\Api\Chat;

use App\ChatGroup;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use stdClass;
use DB;

/**
 * @group Chat
 * APIs for player chat
 */

class GroupController extends Controller
{
    /**
        Get User Groups

        @response
        {
            "Response": true,
            "StatusCode": 500,
            "Message": "chat group already exist",
            "Result": {
                "id": 3,
                "title": "Test Group Three",
                "created_by": 91,
                "team_id": null,
                "created_at": "2021-02-22 21:32:59",
                "updated_at": "2021-02-22 16:32:59"
            }
        }
    **/

    /*public function userGroups(){
        $groups = ChatGroup::whereHas('members',function($user){
            $user->where('users.id',auth()->id())->where('clear_history','no');

        })->get();
        if($groups->count()){
            return Helper::apiSuccessResponse(true, 'success',$groups);
        }
        return Helper::apiErrorResponse(false, 'groups not found',new \stdClass());
    }*/

    /**
        Create / Update Group

        @response
        {
            "Response": true,
            "StatusCode": 200,
            "Message": "success",
            "Result": {}
        }

        @bodyParam title string optional
        @bodyParam group_id integer optional
        @bodyParam members array required eg: [1,2,3,4]
        @bodyParam image file optional accept only jpeg, jpg & gif

        @return JsonResponse
    **/

    /*public function saveGroup(Request $request)
    {
        $this->validate($request, [
            'members' => 'nullable|array',
            'members.*' => 'nullable|exists:users,id',
            'image' => 'nullable|file|mimes:jpeg,jpg,png'
        ]);

        $members = $request->members;
        $members[] = auth()->user()->id;

        $request->merge([
            'members' => $members,
        ]);

        if (isset($request->team_id))
        {
            if ($request->group_id)
            {
                $chat_group_exists = ChatGroup::where('team_id', $request->team_id)
                ->first();
            }
            else
            {
                $chat_group_exists = ChatGroup::where('id', $request->group_id)
                ->first();
            }

            if ($chat_group_exists)
            {
                return Helper::apiSuccessResponse(true, 'Chat group already exists for this team', $chat_group_exists);
            }
            else
            {
                $members = DB::table('player_team')
                ->where('team_id',$request->team_id)
                ->pluck('user_id')
                ->toArray();

                $group = new ChatGroup();

                $save_group = $group->saveGroup($request);

                $save_group->is_group = 'yes';
                $save_group->save();

                $obj = new stdClass();
                
                $obj = $save_group;
                $obj->name = $save_group->title;
                $obj->picture = $save_group->image;
                $obj->teams = !empty($save_group->team) ? $save_group->team : new stdClass();
                
                return Helper::apiSuccessResponse(true, 'success', $obj);
            }
        }

        if (!isset($request->members) || !is_array($request->members) || !count($request->members))
        {
            return Helper::apiErrorResponse(false, 'select members for group', new stdClass());
        }

        if (!$request->group_id)
        {
            $chat_group_exists = ChatGroup::with([
                'team'
            ])
            ->whereHas('members', function ($query) use($request)
            {
                $query->whereIn('user_id', $request->members)
                ->havingRaw('count(chat_group_members.id) = '.count($request->members).'');
            })
            ->first();

            if ($chat_group_exists)
            {
                //$chat_group = ChatGroup::find($chat_group_exists->group_id);

                $obj = new stdClass();

                $obj = $chat_group_exists;
                $obj->name = $chat_group_exists->title;
                $obj->picture = $chat_group_exists->image;
                $obj->teams = !empty($chat_group_exists->team) ? $chat_group_exists->team : new stdClass();

                return Helper::apiSuccessResponse(true, 'chat group already exist', $obj);
            }
            else
            {
                $group = new ChatGroup();

                $save_group = $group->saveGroup($request);

                $save_group->is_group = 'yes';
                $save_group->save();

                $obj = new stdClass();

                $obj = $save_group;
                $obj->name = $save_group->title;
                $obj->picture = $save_group->image;
                $obj->teams = !empty($save_group->team) ? $save_group->team : new stdClass();

                return Helper::apiSuccessResponse(true, 'success', $obj);
            }
        }
        else
        {
            $group = ChatGroup::find($request->group_id);

            if (!$group)
            {
                $group = new ChatGroup();
            }

            $save_group = $group->saveGroup($request);

            $save_group->is_group = 'yes';
            $save_group->save();

            $obj = new stdClass();

            $obj = $save_group;
            $obj->name = $save_group->title;
            $obj->picture = $save_group->image;
            $obj->teams = !empty($save_group->team) ? $save_group->team : new stdClass();

            return Helper::apiSuccessResponse(true, 'success', $obj);
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
                "Response": true,
                "StatusCode": 200,
                "Message": "success",
                "Result": {
                    "id": 1,
                    "title": "Group with three two members",
                    "image": "media/chats/groups/I9iP2SUFVk0ulwEIdOIQ5RU9t1DFMd5fWPO2w3ax.png",
                    "created_by": 1,
                    "team_id": null,
                    "created_at": "2021-05-12T07:42:44.000000Z",
                    "updated_at": "2021-05-12T07:42:44.000000Z",
                    "club_id": null,
                    "members": [
                        {
                            "id": 12,
                            "first_name": "Shahzaib",
                            "last_name": "Imran",
                            "profile_picture": null,
                            "is_admin": 0
                        },
                        {
                            "id": 2,
                            "first_name": "Fatima",
                            "last_name": "Sultana",
                            "profile_picture": "media/users/606c4314623c11617707796.jpeg",
                            "is_admin": 0
                        },
                        {
                            "id": 1,
                            "first_name": "Shahzaib",
                            "last_name": "Imran",
                            "profile_picture": "media/users/5fa27263a93271604481635.jpeg",
                            "is_admin": 1
                        }
                    ]
                }
            }
        }
    **/

    /*public function getGroupMembers(Request  $request){
        $this->validate($request,[
            'group_id'=>'required'
        ]);

        $group = ChatGroup::find($request->group_id);

        if (!$group)
        {
            return Helper::apiErrorResponse(false, 'group not found', new stdClass());
        }

        $record = new stdClass();

        $record->id = $group->id;
        $record->title = $group->title;
        $record->image = $group->image;
        $record->created_by = $group->created_by;
        $record->team_id = $group->team_id;
        $record->created_at = $group->created_at;
        $record->updated_at = $group->updated_at;
        $record->club_id = $group->club_id;

        $members = [];

        foreach ($group->members as $value)
        {
            $obj = new stdClass();

            $obj->id = $value->id;
            $obj->first_name = $value->first_name;
            $obj->last_name = $value->last_name;
            $obj->profile_picture = $value->profile_picture;
            $obj->is_admin = count($group->admins) > 0 && in_array($value->id, $group->admins->pluck('id')->toArray()) ? 1 : 0;

            $members[] = $obj;
        }

        $record->members = $members;

        return Helper::apiSuccessResponse(true, 'success', $record);
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

                    //$record->members()->detach([$request->user_id]);

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