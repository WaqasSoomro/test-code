<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\{
    PlayerExercise,
    Post
};
use App\Helpers\{
    Helper,
};
use Illuminate\Support\Facades\{
    DB,
    Storage,
};
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function loadExercises($exercises){
        $res = [];
        foreach ($exercises as $key => $value) {
            $res[] = $value;
        }

        if (count($res) > 0) {
            $exercises_responses = $res;
        } else {
            $exercises_responses = 0;
        }
        return $exercises_responses;
    }

    public function saveVideoFile($request,$pl_ex){
        $video_file = "";
        if ($request->hasFile('video_file')) {
            $video_file = Storage::putFile(PlayerExercise::$media, $request->video_file);
        }

        $thumbnail = "";
        if ($request->hasFile('thumbnail')) {
            $thumbnail = Storage::putFile(PlayerExercise::$media, $request->thumbnail);
        }

        if ($video_file == "" || $thumbnail == "") {
            return Helper::apiNotFoundResponse(false, 'Failed to upload video or thumbnail', new stdClass());
        }

        $res = DB::transaction(function () use ($thumbnail, $video_file, $request, $pl_ex) {

            $pl_ex->thumbnail = $thumbnail;
            $pl_ex->video_file = $video_file;

            $pl_ex->save();

            $post = Post::wherePlayerExerciseId($request->player_exercise_id)->update([
                'thumbnail' => $thumbnail,
                'post_attachment' => $video_file
            ]);

            return (int)($post && $pl_ex);
        });
        return $res;
    }
}