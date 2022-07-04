<?php

namespace App\Http\Controllers\Api\Stream;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Player;
use App\Stream;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group Streaming
 * APIs for straming
 */
class StreamController extends Controller
{
    /**
     * Start Stream
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Stream Created",
     * "Result": {
     * "id": 1,
     * "stream_token": 123456ABC,
     * "status": 1,
     * "start_at": "2020-07-20 20:10:44",
     * "end_At": null,
     * "created_at": "2020-07-20 20:10:44",
     * "updated_at": "2020-07-22 16:56:05"
     * }
     * }
     *
     * @return JsonResponse
     */
    public function start()
    {
        $player = Player::where('user_id', Auth::user()->id)->first();

        if ($player === null) {
            return Helper::apiNotFoundResponse(true, "Player not found", $player);
        }

        $stream = Stream::where('status', Stream::STATUS_LIVE)->where('player_id', $player->id)->first();

        if ($stream) {
            return Helper::apiSuccessResponse(true, "Stream is already live", $stream, 409, 409);
        }

        $stream = new Stream();
        $stream->createStream($player->id);

        return Helper::apiSuccessResponse(true, "Stream Created", $stream);
    }

    /**
     * Stop Stream
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Stream Stopped",
     * "Result": {
     * "id": 1,
     * "stream_token": 123456ABC,
     * "status": -1,
     * "start_at": "2020-07-20 20:10:44",
     * "end_At": "2020-07-20 20:10:44",
     * "created_at": "2020-07-20 20:10:44",
     * "updated_at": "2020-07-22 16:56:05"
     * }
     * }
     *
     * @return JsonResponse
     */
    public function stop()
    {
        $player = Player::where('user_id', Auth::user()->id)->first();

        if ($player === null) {
            return Helper::apiNotFoundResponse(true, "Player not found", $player);
        }

        $stream = Stream::where('status', Stream::STATUS_LIVE)->where('player_id', $player->id)->first();

        if (!$stream) {
            return Helper::apiNotFoundResponse(true, "Stream not found", $stream);
        }

        $stream->stopStream();

        return Helper::apiSuccessResponse(true, "Stream Stopped", $stream);
    }

    /**
     * Get Live Stream by Player
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Stream Created",
     * "Result": {
     * "id": 1,
     * "stream_token": 123456ABC,
     * "status": 1,
     * "start_at": "2020-07-20 20:10:44",
     * "end_At": null,
     * "created_at": "2020-07-20 20:10:44",
     * "updated_at": "2020-07-22 16:56:05"
     * }
     * }
     *
     * @urlParam player_id required
     *
     * @return JsonResponse
     */
    public function getLiveStreamByPlayer($player_id)
    {
        $stream = Stream::where('status', Stream::STATUS_LIVE)->where('player_id', $player_id)->first();

        if (!$stream) {
            return Helper::apiNotFoundResponse(true, "Player is not streaming", $stream);
        }

        return Helper::apiSuccessResponse(true, "Player is streaming", $stream);
    }
}
