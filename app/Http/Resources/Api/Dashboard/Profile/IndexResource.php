<?php
namespace App\Http\Resources\Api\Dashboard\Profile;
use Spatie\Permission\Models\Role;
use App\Club;
use App\Helpers\Helper;
use App\Http\Resources\Api\Dashboard\General\CountryCodesResource;
use App\Http\Resources\Api\Dashboard\General\CountriesResource;
use App\Http\Resources\Api\Dashboard\General\LanguagesResource;
use Illuminate\Http\Resources\Json\JsonResource;
use stdClass;

class IndexResource extends JsonResource
{
    public function toArray($request)
    {
        $permissions = [];

        $myClubs = (new Club())->myCLubs($request)->original['Result'];

        $myRoles = $this->roles->pluck('name')->toArray();

        if ($request->path() == 'api/v4/dashboard/auth/sign-in')
        {
            $selectedClubId = $myClubs[0]['id'] ?? 0;
        }
        else
        {
            $selectedClubId = $request->clubId ?? 0;
        }

        if (!in_array('demo_trainer', $myRoles))
        {
            $accessType = Helper::checkTeamUpgradation($selectedClubId);

            $permissions = !empty($accessType) ? Helper::getPermissions($accessType) : [];
        }

        if (in_array('demo_trainer', $myRoles) && empty($this->phone))
        {
            $nextScreen = '/register/information';
        }
        else if (count($this->clubs_trainers) < 1 && count($myClubs) < 1 && (in_array('demo_trainer', $myRoles) || in_array('trainer', $myRoles)))
        {
            $nextScreen = '/register/club';
        }
        else if (count($this->clubs_trainers) > 0 && count($myClubs) < 1 && (in_array('demo_trainer', $myRoles) || in_array('trainer', $myRoles)))
        {
            $nextScreen = '/register/requested';
        }
        else
        {
            $nextScreen = '';
        }
        
        return [
            'id' => $this->id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
            'countryCode' => $this->country_code ? (new CountryCodesResource($this->country_code))->resolve() : new stdClass(),
            'phoneNo' => $this->phone,
            'nationality' => $this->nationality ? (new CountriesResource($this->nationality))->resolve() : new stdClass(),
            'language' => $this->user_language ? (new LanguagesResource($this->user_language))->resolve() : new stdClass(),
            'image' => $this->profile_picture ?? "",
            'nextScreen' => $nextScreen,
            'permissions' => $permissions,
            'token' => $this->token ?? "",
            'myClubs' => $myClubs
        ];
    }
}