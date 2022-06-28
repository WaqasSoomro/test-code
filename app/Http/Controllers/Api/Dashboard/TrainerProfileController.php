<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Country;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\PlayerTeam;
use App\Trainer;
use App\User;
use App\UserCoupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use stdClass;


/**
 * @authenticated
 * @group Dashboard / Trainer Profile
 *
 * APIs to manage Trainer Profile
 */
class TrainerProfileController extends Controller
{

    /**
     * GetTrainerProfile
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Successfully retrieved trainer profile!",
     * "Result": {
     * "user_details": {
     * "id": 40,
     * "nationality_id": 1,
     * "first_name": "umer",
     * "middle_name": "ut",
     * "last_name": "shaikh..",
     * "surname": "tempora",
     * "email": "umer@jogo.ai",
     * "phone": "923361227498",
     * "gender": null,
     * "language": null,
     * "address": null,
     * "profile_picture": null,
     * "date_of_birth": null,
     * "badge_count": 0,
     * "verification_code": "665186",
     * "verified_at": "2020-07-31 02:31:43",
     * "active": 0,
     * "status_id": 2,
     * "created_at": "2020-07-31 02:26:43",
     * "updated_at": "2020-10-01 11:49:02",
     * "deleted_at": null,
     * "total_assignments": 4,
     * "team_data": [
     * {
     * "id": 3,
     * "team_name": "ManUtd U18",
     * "image": "https://lh3.googleusercontent.com/KNyKMfQqqVcLYAROYJ6KPW7nqmyMMcuc7npdzuzYI9KXhnZDJ3Wkfqy_apcQTDgq2QlNp9LzqQly06N5qsNxUOLT",
     * "description": "The greatest club in the world",
     * "created_at": "2020-07-24 15:44:11",
     * "updated_at": "2020-07-24 15:44:11",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 3,
     * "created_at": null
     * }
     * },
     * {
     * "id": 2,
     * "team_name": "Ajax U16",
     * "image": "https://bloximages.newyork1.vip.townnews.com/gazettextra.com/content/tncms/assets/v3/editorial/e/a7/ea782551-1a85-5921-82f2-4730effe67cc/5b5744d4e67c7.image.jpg",
     * "description": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
     * "created_at": "2020-07-17 21:17:06",
     * "updated_at": "2020-07-17 21:17:06",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 2,
     * "created_at": null
     * }
     * }
     * ],
     * "total_players": 0,
     * "trainer": {
     * "id": 1,
     * "user_id": 40,
     * "country": "1",
     * "jersey_number": "10",
     * "created_at": null,
     * "updated_at": null,
     * "deleted_at": null
     * },
     * "teams_trainers": [
     * {
     * "id": 3,
     * "team_name": "ManUtd U18",
     * "image": "https://lh3.googleusercontent.com/KNyKMfQqqVcLYAROYJ6KPW7nqmyMMcuc7npdzuzYI9KXhnZDJ3Wkfqy_apcQTDgq2QlNp9LzqQly06N5qsNxUOLT",
     * "description": "The greatest club in the world",
     * "created_at": "2020-07-24 15:44:11",
     * "updated_at": "2020-07-24 15:44:11",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 3,
     * "created_at": null
     * }
     * },
     * {
     * "id": 2,
     * "team_name": "Ajax U16",
     * "image": "https://bloximages.newyork1.vip.townnews.com/gazettextra.com/content/tncms/assets/v3/editorial/e/a7/ea782551-1a85-5921-82f2-4730effe67cc/5b5744d4e67c7.image.jpg",
     * "description": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
     * "created_at": "2020-07-17 21:17:06",
     * "updated_at": "2020-07-17 21:17:06",
     * "deleted_at": null,
     * "pivot": {
     * "trainer_user_id": 40,
     * "team_id": 2,
     * "created_at": null
     * }
     * }
     * ],
     * "nationality": {
     * "id": 1,
     * "name": "Netherlands",
     * "iso": "NL",
     * "phone_code": 31,
     * "created_at": null,
     * "updated_at": null,
     * "deleted_at": null
     * }
     * },
     * "countries": [
     * {
     * "id": 1,
     * "name": "Netherlands"
     * },
     * {
     * "id": 2,
     * "name": "Netherlands Antilles"
     * },
     * {
     * "id": 3,
     * "name": "Pakistan"
     * }
     * ]
     * }
     * }
     *
     * @return JsonResponse
     */
    public function getTrainerProfile()
    {
        $user = User::whereId(Auth::user()->id)->with(['trainer', 'teams_trainers', 'nationality'])
            ->withCount('assignments as total_assignments')
            ->first();

        if (!$user) {
            return Helper::apiNotFoundResponse(false, 'Records not found!', new stdClass());
        }
        $data = $user;
        $data['team_data'] = $user->teams_trainers ?? null;
        $data['total_players'] = PlayerTeam::whereIn('team_id', $user->teams_trainers->pluck('id'))->count();
        $data['access_type'] = UserCoupon::whereUserId($user->id)->count() > 0 ?  'lite' : 'freemium';

        $response['user_details'] = $data;
        $response['countries'] = Country::select('id', 'name')->get();

        return Helper::apiSuccessResponse(true, "Successfully retrieved trainer profile!", $response);
    }

    /**
     * UpdatesTrainerProfile
     *
     * @response {
     * "Response": true,
     * "StatusCode": 200,
     * "Message": "Successfully updated trainer profile!",
     * "Result": {
     * "id": 40,
     * "nationality_id": null,
     * "first_name": "umers",
     * "middle_name": null,
     * "last_name": "shaikh",
     * "surname": "rt",
     * "email": "umer@jogo.ai",
     * "phone": "923361228789",
     * "gender": null,
     * "language": null,
     * "address": null,
     * "profile_picture": "media/users/7QwLHhPCVwD9WWkjGkE3AFxK17KYius21qOzEBaN.jpeg",
     * "date_of_birth": null,
     * "badge_count": 0,
     * "verification_code": "665186",
     * "verified_at": "2020-07-31 02:31:43",
     * "active": 0,
     * "status_id": 2,
     * "created_at": "2020-07-31 02:26:43",
     * "updated_at": "2020-10-05 15:44:04",
     * "deleted_at": null
     * }
     * }
     *
     * @bodyParam first_name string required First name max 191 chars
     * @bodyParam last_name string optional Last name max 191 chars
     * @bodyParam middle_name string optional Middle name max 191 chars
     * @bodyParam surname string required Surname max 191 chars
     * @bodyParam email string required unique Email
     * @bodyParam phone string required Phone
     * @bodyParam nationality_id string optional
     * @bodyParam jersey_number string optional
     * @bodyParam profile_picture formData optional
     *
     * @return JsonResponse
     */
    public function updateTrainerProfile(Request $request)
    {
        //Id of current trainer
        $current_id = Auth::user()->id;
        //Validate Data
        Validator::make($request->all(), [
            'first_name' => 'required|max:191',
            'last_name' => 'max:191',
            'middle_name' => 'max:191',
            'surname' => 'required|max:191',
            'email' => 'required|unique:users,email,' . $current_id,
            'phone' => 'required',

        ])->validate();

        if (Storage::exists(Auth::user()->profile_picture) && $request->hasFile('profile_picture')) {
            Storage::delete(Auth::user()->profile_picture);
        }

        $path = "";
        if ($request->hasFile('profile_picture')) {
            $path = Storage::putFile(User::$media_path, $request->profile_picture);
        }

        //Update profile with new data
        $trainer_profile = User::whereId($current_id)->first();

        $trainer_profile->nationality_id = $request->nationality_id;
        $trainer_profile->first_name = $request->first_name;
        $trainer_profile->middle_name = $request->middle_name;
        $trainer_profile->last_name = $request->last_name;
        $trainer_profile->surname = $request->surname;
        $trainer_profile->email = $request->email;
        $trainer_profile->phone = $request->phone;

        if($path != ""){
            $trainer_profile->profile_picture = $path;
        }
        if($trainer_profile->isDirty('phone')){
            $otp_code = Helper::generateOtp();
            try {
                Mail::send('emails.send_otp', ['user' => $trainer_profile, 'otp_code' => $otp_code], function ($m) use ($trainer_profile) {
                    $m->to($trainer_profile->email, $trainer_profile->first_name)->subject('JOGO OTP-Code');
                });
                $trainer_profile->verified_at = null;
                $trainer_profile->verification_code = $otp_code;
            } catch (\Exception $e) {
                activity()->causedBy($trainer_profile)->performedOn($trainer_profile)->log($e->getMessage());
            }
        }
        if ($trainer_profile->save()) {
            Trainer::whereUserId($trainer_profile->id)->update([
                'jersey_number' => $request->jersey_number
            ]);
            return Helper::apiSuccessResponse(true, "Successfully updated trainer profile!", $trainer_profile);
        } else {
            return Helper::apiNotFoundResponse(false, 'There was problem updating trainer profile, please try again later!', new stdClass());
        }

    }

}
