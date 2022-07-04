<?php
namespace App\Http\Resources\Chat;
use Illuminate\Http\Resources\Json\JsonResource;
use stdClass;

class ChatMessageResource extends JsonResource
{
    public function toArray($request)
    {
        /*return [
            'id' => $this->id,
            'message' => $this->message,
            'msg_identification' => $this->msg_identification ?? "",
            'attachment_type' => $this->attachment_type ?? "",
            'image' => $this->image ?? "",
            'file' => $this->file ?? "",
            'file_orignal_name' => $this->file_orignal_name ?? "",
            'gif' => $this->gif_url ?? "",
            'height' => $this->height ?? 0,
            'width' => $this->width ?? 0,
            'is_read' => @$this->read_messages()->find(auth()->user()->id) ? 1 : 0,
            'created_at' => $this->created_at,
            'reply_of' => $this->parent_message ? [
                'id' => $this->parent_message->id,
                'message' => $this->parent_message->message,
                'msg_identification' => $this->parent_message->msg_identification ?? "",
                'image' => $this->parent_message->image ?? "",
                'file' => $this->parent_message->file ?? "",
                'file_orignal_name' => $this->parent_message->file_orignal_name ?? "",
                'gif' => $this->parent_message->gif_url ?? "",
                'is_read' => @$this->parent_message->read_messages()->find(auth()->user()->id) ? 1 : 0,
                'created_at' => $this->parent_message->created_at,
                'sender' => [
                    'id' => $this->parent_message->sender->id,
                    'name' => $this->parent_message->sender->first_name.' '.$this->parent_message->sender->last_name,
                    'profile_picture' => $this->parent_message->sender->profile_picture ?? "",
                    'role' => $this->parent_message->sender->roles[0]->name
                ]
            ] : new stdClass(),
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->first_name.' '.$this->sender->last_name,
                'profile_picture' => $this->sender->profile_picture ?? "",
                'role' => $this->sender->roles[0]->name
            ]
        ];*/

        return $request->all();
    }
}