<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChatGroup extends Model
{
    //
    protected $table='chat_groups';


    public function members(){
        return $this->belongsToMany(User::class,'chat_group_members','group_id','user_id');
    }


    public function messages(){
        return $this->hasMany(ChatGroupMessage::class,'group_id');
    }

    public function last_message(){
        return $this->hasOne(ChatGroupMessage::class,'group_id')->orderBy('id','DESC');
    }



    public function group_owner(){
        return $this->belongsTo(User::class,'created_by');
    }


    public function team(){
        return $this->belongsTo(Team::class,'team_id');
    }


    public function club(){
        return $this->belongsTo(Club::class,'club_id');
    }

    public function admins()
    {
        return $this->belongsToMany(User::class, 'chat_group_admins', 'group_id', 'user_id');
    }

    public function saveGroup($request){
        
        if($request->title){
            $this->title = $request->title;
            if ($request->image) {
                $this->image = $request->image;
            }
        }
        if($request->team_id){
            $this->team_id = $request->team_id;
        }
        $club = DB::table('club_trainers')->where('trainer_user_id', Auth::user()->id)->first();
        $club_id = $club->club_id ?? 0;
        $this->club_id = (int)$club_id;
        $this->created_by = auth()->id();

        if ($request->hasFile('image'))
        {
            $this->image = Storage::putFile('media/chats/groups', $request->image);
        }
        $this->club_id = $request->club_id;

        $this->save();
        if(isset($request->members) && is_array($request->members)){
            $this->members()->syncWithoutDetaching($request->members);
            if (!$request->group_id)
            {
                $this->admins()->syncWithoutDetaching([auth()->user()->id]);
            }
        }
        return $this;
    }
}
