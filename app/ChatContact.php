<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatContact extends Model
{
    //

    protected $table='chat_contacts';
    protected $fillable = [
        'sender_id','receiver_id','is_blocked','blocked_by'
    ];

    public function saveContact($receiver){
        $this->receiver_id = $receiver->id;
        $this->sender_id = auth()->user()->id;
        $this->save();
        return $this;
    }


    public function scopeWhereReceiver($query, $model)
    {
        return $query->where('receiver_id', $model->id);
    }

    public function scopeWhereSender($query, $model)
    {
        return $query->where('sender_id', $model->id);
    }

    public function scopeBetweenModels($query, $sender, $receiver)
    {
        $query->where(function ($queryIn) use ($sender, $receiver){
            $queryIn->where(function ($q) use ($sender, $receiver) {
                $q->whereSender($sender)->whereReceiver($receiver);
            })->orWhere(function ($q) use ($sender, $receiver) {
                $q->whereSender($receiver)->whereReceiver($sender);
            });
        });
    }
}
