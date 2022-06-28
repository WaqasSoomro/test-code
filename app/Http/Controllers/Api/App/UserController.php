<?php

namespace App\Http\Controllers\Api\App;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use stdClass;

/**
 * @group User
 *
 * APIs for users
 */
class UserController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }
    
    /**
     * Update Profile
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Profile updated",
     * "Result": {
     * "id": 1,
     * "nationality_id": 1,
     * "first_name": "Muhammad",
     * "middle_name": null,
     * "last_name": "Shahzaib",
     * "surname": null,
     * "email": null,
     * "phone": "+923361227406",
     * "gender": null,
     * "language": null,
     * "address": null,
     * "profile_picture": "media/users/5f185eaf7703c1595432623.jpeg",
     * "cover_photo": "media/users/5f185eaf7703c1595432623.jpeg",
     * "date_of_birth": "1995-05-01",
     * "verification_code": "536713",
     * "verified_at": "2020-07-22 16:50:25",
     * "active": 0,
     * "status_id": 1,
     * "created_at": "2020-07-20 20:10:44",
     * "updated_at": "2020-07-22 16:56:05",
     * "deleted_at": null,
     * "nationality": {
     * "id": 1,
     * "name": "Netherlands"
     * },
     * "roles": [
     * {
     * "id": 1,
     * "name": "player",
     * "pivot": {
     * "model_id": 1,
     * "role_id": 1,
     * "model_type": "App\\User"
     * }
     * }
     * ],
     * "player_details": {
     * "user_id": 1,
     * "height": 5.9,
     * "weight": 60,
     * "jersey_number": "11",
     * "position_id": 2,
     * "customary_foot_id": 2,
     * "positions": [
        {
            "id": 3,
            "name": "Goal Keeper",
            "lines": 2,
            "pivot": {
                "player_id": 1,
                "position_id": 3
            },
            "line": {
                "id": 2,
                "name": "GoalKeepers"
            }
        }
    ],
     * "customary_foot": {
     * "id": 2,
     * "name": "Right"
     * }
     * }
     * }
     * }
     *
     * @bodyParam first_name string required First name max 191 chars
     * @bodyParam last_name string required Last name max 191 chars
     * @bodyParam date_of_birth date required date format eg: 1995-05-05
     * @bodyParam nationality_id integer nullable
     * @bodyParam customary_foot_id integer nullable
     * @bodyParam positionsId array nullable
     * @bodyParam height double nullable
     * @bodyParam weight double nullable
     * @bodyParam jersey_number string nullable
     * @bodyParam profile_picture file nullable file types: jpeg, jpg, png
     * @bodyParam cover_photo file nullable file types: jpeg, jpg, png
     *
     * @return JsonResponse
     */
    public function updateProfile(Request $request)
    {
        Validator::make($request->all(), [
            'first_name' => 'required|max:191',
            'last_name' => 'required|max:191',
            'nationality_id' => 'nullable|exists:countries,id',
            'height' => 'nullable',
            'weight' => 'nullable',
            'positionsId' => 'nullable|array',
            'positionsId.*' => 'numeric|exists:positions,id',
            'customary_foot_id' => 'nullable|exists:customary_feet,id',
            'jersey_number' => 'nullable',
            'profile_picture' => 'nullable|file|mimes:jpeg,jpg,png',
            'cover_photo' => 'nullable|file|mimes:jpeg,jpg,png',
            'gender' => 'nullable|in:male,female,other',
            'username' => 'required|max:24'
        ])
        ->validate();

        $user = User::find(Auth::user()->id);

        $user->updatePlayerProfile($request);

        $user = User::with([
            'nationality' => function ($query)
            {
                $query->select('id', 'name','iso as flag');
            },
            'roles' => function ($query)
            {
                $query->select('id', 'name');
            },
            'player_details' => function ($query)
            {
                $query->select('id', 'user_id', 'height', 'weight', 'jersey_number', 'position_id', 'customary_foot_id');
            },
            'player_details.customaryFoot' => function ($query)
            {
                $query->select('id', 'name');
            },
            'player_details.positions' => function ($query)
            {
                $query->select('positions.id', 'name', 'lines');
            },
            'player_details.positions.line' => function ($query)
            {
                $query->select('lines.id', 'name');
            }
        ])
        ->find(Auth::user()->id);

        return Helper::apiSuccessResponse(true, "Profile updated", $user);
    }

    /**
     * Update Profile Picture
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Profile picture updated",
     * "Result": {
     * "profile_picture": "media/users/B6x2tE3qGvrqmC4eAqQqUo3d4UvU6M1DqRP2UDdb.jpeg"
     * }
     * }
     *
     * @bodyParam profile_picture string required allowed mimes jpeg and png
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfilePicture(Request $request)
    {
        Validator::make($request->all(), [
            'profile_picture' => 'required'
        ])->validate();

        $user = User::find(Auth::user()->id);

        if (Storage::exists($user->profile_picture)) {
            Storage::delete($user->profile_picture);
        }

        $path = Helper::uploadBase64File($request->profile_picture, User::$media_path);

        if ($path == "") {
            return Helper::apiErrorResponse(false, "Failed to update profile picture", new stdClass());
        }

        $user->profile_picture = $path;
        $data['profile_picture'] = $user->profile_picture;
        $user->save();

        return Helper::apiSuccessResponse(true, "Profile picture updated", $data);
    }

    /**
     * Contact
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Contact successful",
     * "Result": {}
     * }
     *
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function contact()
    {
        $user = Auth::user();
        try {
            Mail::send('emails.contact', ['user' => $user], function ($m) use ($user) {
                $m->to('support@jogo.ai')->subject($user->first_name . ' ' . $user->last_name);
            });
        } catch (Exception $e) {
            activity()->causedBy($user)->log($e->getMessage());
        }

        return Helper::apiSuccessResponse(true, "Contact successful", new stdClass());
    }

    /**
        Delete player profile

        @response{
            "Response": true,
            "StatusCode": 200,
            "Message": "You've successfully deleted your account",
            "Result": {}
        }

        @response 404{
            "Response": false,
            "StatusCode": 404,
            "Message": "Invalid player id",
            "Result": {}
        }

        @response 500{
            "Response": false,
            "StatusCode": 500,
            "Message": "Something wen't wrong",
            "Result": {}
        }
    */

    protected function delete(Request $request, $id)
    {
        $id = auth()->user()->id;
        
        $response = $this->userModel->remove($request, $id);

        return $response;
    }
}
