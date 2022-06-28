<?php

namespace App\Http\Controllers\Api;

use App\Comment;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\AddEditRequest;
use App\Http\Requests\Comment\DeleteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use stdClass;

/**
 * @authenticated
 * @group Comments
 *
 * APIs to manage comments
 */
class CommentController extends Controller
{
    /**
     *  add-edit
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Record has been saved",
     * "Result": {
     * "id": 20,
     * "post_id": 2,
     * "assignment_id": null,
     * "exercise_id": null,
     * "contact_id": 1,
     * "comment": "h2sO",
     * "status_id": null,
     * "created_at": "2020-08-20 17:53:04",
     * "updated_at": "2020-08-20 17:53:28",
     * "deleted_at": null,
     * "posted_at": "24 seconds ago",
     * "contact": {
     * "id": 1,
     * "nationality_id": 1,
     * "first_name": "muhammad",
     * "middle_name": null,
     * "last_name": "shahzaib",
     * "surname": null,
     * "email": null,
     * "phone": "+9233612274066",
     * "gender": null,
     * "language": null,
     * "address": null,
     * "profile_picture": "media/users/5f20343a56be11595946042.jpeg",
     * "date_of_birth": "1995-05-02 00:00:00",
     * "verification_code": "017003",
     * "verified_at": "2020-08-20 10:00:51",
     * "active": 0,
     * "status_id": 1,
     * "created_at": "2020-07-20 20:10:44",
     * "updated_at": "2020-08-20 10:00:51",
     * "deleted_at": null
     * }
     * }
     * }
     *
     * @return JsonResponse
     */
    public function addEdit(AddEditRequest $request)
    {
        $request->validated();

        $data = (new Comment())->addEdit($request,Auth::user());
        $comment = $data['comment'];
        $comment->contact;

        return Helper::apiSuccessResponse(true, 'Record has been saved', $comment);
    }

    /**
     * delete
     *
     * @response {
     *
     *  "Response": true,
     *  "StatusCode": 200,
     *  "Message": "Record has been deleted",
     *  "Result": {}
     *
     * }
     *
     * @response 404 {
     *
     *  "Response": false,
     *  "StatusCode": 404,
     *  "Message": "Record not found",
     *  "Result": {}
     *
     * }
     *
     */
    public function delete(DeleteRequest $request)
    {
        $response = (new Comment())->remove($request->comment_id,Auth::user()->id);

        if ($response['status']) {
            return Helper::apiSuccessResponse(true, $response['msg'], new stdClass());
        }

        return Helper::apiNotFoundResponse(false, $response['msg'], new stdClass());
    }

}
