<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ChatGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        /*$total_read_messages = DB::table('chat_read_messages')->where('user_id',auth()->user()->id)->where('group_id',$this->id)->count();
        $total_messages = $this->messages->count();
        $data['id']=$this->id;
        $data['name']=$this->name;
        $data['title']=$this->title;
        $data['picture']= $this->picture ?? $this->image;
        $data['last_message']=$this->last_message?$this->last_message:new \stdClass();
        $data['created_by']= $this->group_owner->first_name.' '.$this->group_owner->last_name;
        $group_members = $this->members->filter(function($member){
            return $member->id!=auth()->user()->id;
        })->values();
        if($this->title == null && $group_members->count()){
            $group_name =implode(",",$group_members->pluck('name')->toArray());
            $data['name']=$group_name;
        }
        if(isset($this->team)){
            $data['name']=$this->team->team_name;
            if(isset($this->club)){
                $data['picture']=$this->club->image;
            }
            else
            {
                $data['picture'] = $this->team->image;
            }
        }elseif(count($group_members)==1){
            $data['picture']=$group_members[0]->profile_picture;
        }

        if(count($group_members)>1 && isset($this->created_by)){
            $data['created_by']=$this->group_owner->first_name.' '.$this->group_owner->last_name;
        }

        if(isset($this->last_message)){
            unset($this->last_message->sender->roles);
        }
        
        


        $data['is_online'] = 0;
//        $data['is_read']=$this->last_message?$this->last_message->read_messages()->find(auth()->user()->id)?1:0:0;
        $data['is_read']=$total_messages-$total_read_messages<=0?1:0;
        $data['members']=$group_members;
        $data['total_unread_count']=$this->messages->count()-$total_read_messages;
        $data['created_at']=$this->created_at;*/
        
        return $request->all();
    }
}
