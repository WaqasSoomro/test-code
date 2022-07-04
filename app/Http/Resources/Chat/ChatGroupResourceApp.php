<?php

namespace App\Http\Resources\Chat;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ChatGroupResourceApp extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray()
    {

        /*if (($this->is_group == 'no' && $this->last_message) || $this->is_group == 'yes' && ($this->last_message || !$this->last_message))
        {
            $total_read_messages = DB::table('chat_read_messages')
            ->where('user_id', auth()->user()->id)
            ->where('group_id',$this->id)
            ->count();

            $total_messages = $this->messages->count();

            $data['id'] = $this->id;
            $data['name'] = $this->title;
            $data['picture'] = $this->picture;
            $data['last_message'] = $this->last_message ? $this->last_message : new \stdClass();
            $data['is_group'] = $this->is_group;
            $data['created_by']='';

            $group_members = $this->members->filter(function($member)
            {
                return $member->id!=auth()->user()->id;
            })
            ->values();

            if ($this->title == null && $group_members->count())
            {
                $group_name = implode(", ",$group_members->pluck('name')->toArray());

                $data['name']= $group_name;
            }


            $group_members= $group_members->map(function($member)
            {
                $follow_status = false;

                if (auth()->user()->followers()->find($member->id))
                {
                    $follow_status = true;
                }
                elseif (auth()->user()->followings()->find($member->id))
                {
                    $follow_status = true;
                }

                return [
                    'id' => $member->id,
                    'current_player_id' => auth()->user()->id,
                    'first_name' => $member->first_name,
                    'middle_name' => $member->middle_name,
                    'last_name' => $member->last_name,
                    'profile_picture' => $member->profile_picture,
                    'role' => $member->roles ? $member->roles[0]->name : "",
                    'follow_status' => $follow_status
                ];
            });

            if (isset($this->team))
            {
                //$data['name'] = $this->team->team_name;

                if (isset($this->club))
                {
                    $data['picture'] = $this->club->image;
                }
                else{
                    $data['picture'] = $this->team->image;
                }
            }
            elseif (count($group_members) == 1)
            {
                $data['picture'] = isset($group_members[0]) ? $group_members[0]['profile_picture'] : "";
            }

            if ($this->image)
            {
                $data['picture'] = $this->image;
            }

            if (count($group_members) > 1 && isset($this->created_by))
            {
                $data['created_by'] = $this->group_owner->first_name.' '.$this->group_owner->last_name;
            }

            if (isset($this->last_message))
            {
                unset($this->last_message->sender->roles);
            }

            $data['is_online'] = 0;

            //$data['is_read']=$this->last_message?$this->last_message->read_messages()->find(auth()->user()->id)?1:0:0;

            $data['is_read'] = $total_messages - $total_read_messages <= 0 ? 1 : 0;
            $data['members'] = $group_members;
            $data['total_unread_count'] = $this->messages->count()-$total_read_messages;
            $data['created_at'] = Carbon::parse($this->created_at)->format('Y-m-d H:i:s');

            return $data;
        }
        else
        {
            return new \stdClass();
        }*/

        return [];
    }
}