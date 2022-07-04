<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

use App\ChatGroup;
use Illuminate\Support\Facades\DB;

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('test', function ($user) {
    return $user;
});
Broadcast::channel('my-chat', function ($user) {
    return [
        'id'=>$user->id,
        'name'=>$user->first_name.' '.$user->last_name
    ];
});

BroadCast::channel('chat.{id}',function ($user,$id){
    return (int) $user->id === (int) $id;
});

Broadcast::channel('group-chats.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('group.{group}', function ($user, $group) {
    $is_member = \Illuminate\Support\Facades\DB::table('chat_group_members')
        ->where('group_id',$group)
        ->where('user_id',$user->id)
        ->first();
    if($is_member){
        return true;
    }else{
        return  false;
    }
});

Broadcast::channel('battle_ai.{id}', function ($user, $id) {
    return [
        'first_name'=>$user->first_name,
        'last_name'=>$user->last_name,
        'picture'=>$user->profile_picture,
        'id'=>$user->id,
        'is_ready'=>0,
        'rank'=>rand(2,25)
    ];
});

Broadcast::channel('battle_ai_lobby.{id}', function ($user) {
    return [
        'first_name'=>$user->first_name,
        'last_name'=>$user->last_name,
        'picture'=>$user->profile_picture,
        'id'=>$user->id,
        'is_ready'=>0,
        'rank'=>rand(2,25)
    ];
});




