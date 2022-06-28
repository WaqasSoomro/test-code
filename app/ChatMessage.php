<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    //

    //
    protected $table='chat_messages';
    protected $fillable = [
        'sender_id','receiver_id','message','type','deleted_by_sender','deleted_by_receiver'
    ];


    public function sender(){
        return $this->belongsTo(User::class,'sender_id','id');
    }

    public function receiver(){
        return $this->belongsTo(User::class,'receiver_id');
    }


    public function setReceiver($recipient)
    {
        return $this->fill([
            'receiver_id' => $recipient->id,
        ]);
    }
    public function setSender($sender)
    {
        return $this->fill([
            'sender_id' => $sender->id,
        ]);
    }



    public function scopeWhereRecipient($query, $model)
    {
        return $query->where('receiver_id', $model->id);
    }


    public function scopeWhereSender($query, $model)
    {
        return $query->where('sender_id', $model->id);
    }



    public function scopeBetweenModels($query, $sender, $recipient)
    {
        $query->where(function ($queryIn) use ($sender, $recipient){
            $queryIn->where(function ($q) use ($sender, $recipient) {
                $q->whereSender($sender)->whereRecipient($recipient);
            })->orWhere(function ($q) use ($sender, $recipient) {
                $q->whereSender($recipient)->whereRecipient($sender);
            });
        });
    }
}
