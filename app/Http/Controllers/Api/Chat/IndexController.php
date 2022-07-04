<?php

namespace App\Http\Controllers\Api\Chat;

use App\ChatGroup;
use App\Contact;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Chat\ChatGroupResource;
use App\Http\Resources\Chat\ChatGroupResourceApp;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group  Chat
 * APIs for player chat
 */
class IndexController extends Controller
{
    //


    /**
     * Get Contacts
     *
     * @response
     * {
    "Response": true,
    "StatusCode": 200,
    "Message": "contacts",
    "Result": {
    "data": [
    {
    "id": 8,
    "name": "Fami Sultana, Fatima Sultana, abdul Haseeb, Saad Saleem, tr 2 rerum, Umer Shaikh",
    "picture": null,
    "last_message": {
    "id": 1089,
    "group_id": 8,
    "sender_id": 40,
    "reply_of": 1086,
    "message": "Ok",
    "image": null,
    "file": null,
    "file_orignal_name": null,
    "gif_url": null,
    "attachment_type": "null",
    "type": null,
    "ref_message_id": null,
    "created_at": "2021-06-07 15:08:57",
    "updated_at": "2021-06-07 15:08:57",
    "msg_identification": "1623078536354",
    "height": 0,
    "width": 0,
    "sender": {
    "id": 40,
    "name": "Umer Shaikh",
    "profile_picture": "media/users/OF4YBPrj3vkx7QtIztDKOAZod45t1sSsXQfc1cPE.jpeg"
    }
    },
    "is_group": "no",
    "created_by": "Umer Shaikh",
    "is_online": 0,
    "is_read": 0,
    "members": [
    {
    "id": 6,
    "current_player_id": 10,
    "first_name": "Fami",
    "middle_name": "''",
    "last_name": "Sultana",
    "profile_picture": null,
    "role": "player",
    "follow_status": false
    },
    {
    "id": 7,
    "current_player_id": 10,
    "first_name": "Fatima",
    "middle_name": "''",
    "last_name": "Sultana",
    "profile_picture": null,
    "role": "player",
    "follow_status": false
    },
    {
    "id": 9,
    "current_player_id": 10,
    "first_name": "abdul",
    "middle_name": "''",
    "last_name": "Haseeb",
    "profile_picture": null,
    "role": "player",
    "follow_status": false
    },
    {
    "id": 11,
    "current_player_id": 10,
    "first_name": "Saad",
    "middle_name": "''",
    "last_name": "Saleem",
    "profile_picture": "media/users/5f92d95717cbc1603459415.jpeg",
    "role": "player",
    "follow_status": false
    },
    {
    "id": 12,
    "current_player_id": 10,
    "first_name": "tr 2",
    "middle_name": "''",
    "last_name": "rerum",
    "profile_picture": null,
    "role": "trainer",
    "follow_status": false
    },
    {
    "id": 40,
    "current_player_id": 10,
    "first_name": "Umer",
    "middle_name": null,
    "last_name": "Shaikh",
    "profile_picture": "media/users/OF4YBPrj3vkx7QtIztDKOAZod45t1sSsXQfc1cPE.jpeg",
    "role": "trainer",
    "follow_status": false
    }
    ],
    "total_unread_count": 17,
    "created_at": "2021-03-22 11:14:18"
    }
    ],
    "meta": {
    "current_page": 1,
    "next_page": 2
    }
    }
    }
     *
     * @queryParam limit required integer
     * @queryParam page optional integer
     * @return JsonResponse
     */
    /*public function contacts(Request $request)
    {
        $request->validate([
            "limit"=>["required","min:1","integer"]
        ]);

        $groups = ChatGroup::with([
            'members' => function($q)
            {
                //$q->selectRaw("users.id, CONCAT(users.first_name,' ',users.last_name) as name, users.profile_picture");
                $q->selectRaw("users.id, users.first_name,users.middle_name,users.last_name,users.profile_picture,CONCAT(users.first_name,' ',users.last_name) as name");
                 $q->with('roles:id,name');
                
            },
            'last_message' => function($q)
            {
                $q->whereDoesntHave('deleted_messages',function($deleted_message)
                {
                    $deleted_message->where('deleted_by','=',auth()->user()->id);
                })
                ->with([
                    'sender'=>function($sender)
                    {
                        $sender->selectRaw("users.id, CONCAT(first_name,' ',last_name) as name, profile_picture");
                    }
                ]);
            }
        ])
        ->whereHas('members',function ($member)
        {
            $member->where('users.id',auth()->id());
            $member->where('clear_history','no');

        })
        ->whereNotNull('created_by')
        ->with('team')
        ->paginate($request->limit ?? 5);

        $groups_data = $groups->values()->all();
        $groups_meta = $groups->toArray();

        $data = ChatGroupResourceApp::collection($groups_data)->toArray($request);
        
        if (!$data)
        {
            return Helper::apiErrorResponse(false, 'no contacts found',new \stdClass());
        }

        $paginate_data["data"] = $data;
        $paginate_data["meta"] = [
            "current_page"=>$groups_meta["current_page"],
            "next_page"=>(int) (substr($groups_meta["next_page_url"],-1)),
        ];
        
        return Helper::apiSuccessResponse(true, 'contacts', $paginate_data);
    }*/

    /**
     * GSearch User/Groups
     *
     * @response {
    "Response": true,
    "StatusCode": 200,
    "Message": "success",
    "Result": [
    {
    "id": 1,
    "name": "Muhammed shahzaib",
    "picture": "media/users/5fa27263a93271604481635.jpeg",
    "type": "user"
    },
    {
    "id": 1,
    "name": "Group One",
    "picture": "https://lh3.googleusercontent.com/KNyKMfQqqVcLYAROYJ6KPW7nqmyMMcuc7npdzuzYI9KXhnZDJ3Wkfqy_apcQTDgq2QlNp9LzqQly06N5qsNxUOLT",
    "type": "group"
    }
    ]
    }
     * @urlParam search string optional
     *
     * @return JsonResponse
     */

    /*public function search(Request $request)
    {
        $user_contacts = auth()->user()->direct_contacts($request->search)->get()->pluck('id')->toArray();
        //get from followers and followings
        $keyword = $request->search;
        $contacts = Contact::where(function ($query) {
            $query->where(function ($q) {
                $q->where('user_id',auth()->user()->id);
            })->orWhere(function ($q) {
                $q->where('contact_user_id',auth()->user()->id);
            });
        })->get(['user_id', 'contact_user_id']);
        $recipients  = $contacts->pluck('user_id')->all();
        $senders     = $contacts->pluck('contact_user_id')->all();
        $result=  User::selectRaw('id, CONCAT(first_name," ",last_name) as name , profile_picture as picture, "user" as type')
            ->where('id', '!=', auth()->user()->id)
            ->whereNotIn('id', $user_contacts)
            ->whereIn('id', array_merge($recipients, $senders));

        if($keyword){
            $result = $result->where('first_name','LIKE',$keyword.'%')->orWhere('last_name','LIKE',$keyword.'%');
        }
        $result = $result->get();
        if($result->count()){
            return Helper::apiSuccessResponse(true, 'success', $result);
        }
        return Helper::apiErrorResponse(false, 'success', new \stdClass());
    }*/

}