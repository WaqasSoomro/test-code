<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class ChatGroupMessage extends Model
{
    protected $table ='chat_group_messages';

    protected $fillable = [
        'group_id',
        'sender_id',
        'reply_of',
        'message',
        'msg_identification',
        'Hy',
        'attachment_type',
        'image',
        'file',
        'file_orignal_name',
        'gif_url',
        'height',
        'width'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class,'sender_id')->with('roles');
    }

    public function group()
    {
        return $this->belongsTo(ChatGroup::class,'group_id');
    }

    public function setSender($sender)
    {
        return $this->fill([
            'sender_id' => $sender->getKey(),
        ]);
    }

    public function read_messages()
    {
        return $this->belongsToMany(User::class,'chat_read_messages','message_id','user_id');
    }

    public function deleted_messages()
    {
        return $this->belongsToMany(User::class,'chat_deleted_messages','message_id','deleted_by');
    }

    public function parent_message()
    {
        return $this->belongsTo($this, 'reply_of');
    }
}