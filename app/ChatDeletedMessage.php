<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatDeletedMessage extends Model
{

    protected $table='chat_deleted_messages';




    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function message(){
        return $this->belongsTo(ChatGroupMessage::class,'message_id');
    }

    public function chat_group(){
        return $this->belongsTo(ChatGroup::class,'group_id');
    }

    //
}
