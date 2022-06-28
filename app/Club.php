<?php
namespace App;
use App\Http\Resources\Api\TrainerApp\Club\TrainerAppClubListingResource;
use App\Setting;
use App\Http\Resources\Api\Dashboard\General\ClubsListingResource;
use App\Http\Resources\Api\Dashboard\Clubs\DetailsResource;
use App\Http\Resources\Api\Dashboard\Clubs\Trainers\ListingResource;
use App\Http\Resources\Api\Dashboard\Clubs\Trainers\DetailsResource as TrainerDetailsResource;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Exception;
use stdClass;

class Club extends Model
{
    use SoftDeletes;

    public static $media_path = 'media/clubs';

    public function trainers(){
        return $this->belongsToMany(User::class,'club_trainers','club_id','trainer_user_id','id','id');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'club_teams', 'club_id', 'team_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function updateClub($request)
    {
        DB::transaction(function () use ($request) {
            if (Storage::exists($this->image) && $request->hasFile('image')) {
                Storage::delete($this->image);
            }

            if ($request->hasFile('image')) {
                $this->image = Storage::putFile(self::$media_path, $request->image);
            }
            $this->title                = $request->club_name;
            $this->description          = $request->description;
            $this->email                = $request->email;
            $this->type                 = $request->club_type;
            $this->website              = $request->website;
            $this->foundation_date      = $request->foundation_date;
            $this->zip_code             = $request->zip_code;
            $this->registration_no      = $request->registration_no;
            $this->address              = $request->address;
            $this->country_id           = $request->country_id;
            $this->city                 = $request->city;
            $this->street_address       = $request->street_address;
            $this->save();
        });
        return $this;
    }

    public function viewClubs($request)
    {
        try
        {
            $clubs = $this::select('id', 'title', 'image', 'owner_id')
            ->where('privacy', 'open_to_invites')
            ->orderBy('created_at', 'desc')
            ->get();

            $records = ClubsListingResource::collection($clubs)->toArray($request);

            if (count($records) > 0)
            {
                $response = Helper::apiSuccessResponse(true, 'Records found', $records);
            }
            else
            {
                $response = Helper::apiNotFoundResponse(true, 'No records found', $records);
            }
        }
        catch (Exception $ex)
        {
            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', []);
        }

        return $response;
    }

    public function create($request, $id = NULL)
    {
        DB::beginTransaction();

        try
        {   
            if (!empty($id))
            {
                $club = $this::where('id', $id)
                ->where('owner_id', auth()->user()->id)
                ->first();

                if (!empty($club))
                {
                    $status = 1;

                    $club->updated_by = auth()->user()->id;
                }
                else
                {
                    $status = 2;
                }
            }
            else
            {
                $club = $this::where('owner_id', auth()->user()->id)
                ->first();

                if (!empty($club) && $request->path() == 'api/v4/dashboard/clubs/create')
                {
                    $status = 3;
                }
                else
                {
                    $status = 1;

                    $club = $this;

                    $club->owner_id = auth()->user()->id;
                    $club->created_by = auth()->user()->id;
                }
            }

            if ($status == 1)
            {
                if (!empty($request->userName))
                {
                    $club->user_name = strtolower($request->userName);
                }
                else
                {
                    $club->user_name = auth()->user()->first_name.' '.auth()->user()->last_name;
                }

                if (!empty($request->name))
                {
                    $club->title = $request->name;
                }
                else
                {
                    $club->title = auth()->user()->first_name.' '.auth()->user()->last_name;
                }

                $club->type = $request->type;

                if (!empty($request->primaryColor))
                {
                    $club->primary_color = $request->primaryColor;
                }
                else
                {
                    $club->primary_color = '#DBFF00';
                }

                if (!empty($request->secondaryColor))
                {
                    $club->secondary_color = $request->secondaryColor;
                }
                else
                {
                    $club->secondary_color = '#000000';
                }

                if (!empty($request->privacy))
                {
                    $club->privacy = $request->privacy;
                }
                else
                {
                    $club->privacy = 'open_to_invites';
                }

                if ($request->hasFile('image'))
                {
                    $club->image = Storage::putFile('media/clubs', $request->image);
                }

                $club->save();

                if (in_array('demo_trainer', auth()->user()->roles->pluck('name')->toArray()))
                {
                    auth()->user()->removeRole('demo_trainer');

                    auth()->user()->assignRole('trainer');
                }

                DB::commit();

                if (!empty($id))
                {
                    $club = (new ClubsListingResource($club))->resolve();

                    $response = Helper::apiSuccessResponse(true, 'You\'ve updated your club successfully', $club);
                }
                else
                {
                    $club = (new ClubsListingResource($club))->resolve();

                    $response = Helper::apiSuccessResponse(true, 'You\'ve created your club successfully', $club);
                }
            }
            else if ($status == 2)
            {
                $response = Helper::apiNotFoundResponse(false, 'Invalid club id', new stdClass());
            }
            else if ($status == 3)
            {
                $response = Helper::apiErrorResponse(false, 'You\'ve created your club already', new stdClass());
            }
            else
            {
                $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', new stdClass());
            }
        }
        catch (Exception $ex)
        {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', new stdClass());
        }

        return $response;
    }

    public function myCLubs($request, $apiType = 'dashboard')
    {
        try
        {
            $clubs = $this::select('id', 'title', 'image', 'owner_id', 'primary_color', 'secondary_color')
            ->where('owner_id', auth()->user()->id)
            ->orWhereHas('trainers', function ($query)
            {
                $query->where('trainer_user_id', auth()->user()->id)
                ->where('is_request_accepted', 'yes');
            })
            ->orderBy('created_at', 'desc')
            ->get();

            if ($apiType == 'trainerApp')
            {
                $records = TrainerAppClubListingResource::collection($clubs)->toArray($request);
            }
            else
            {
                $records = ClubsListingResource::collection($clubs)->toArray($request);
            }

            if (count($records) > 0)
            {
                $response = Helper::apiSuccessResponse(true, 'Records found', $records);
            }
            else
            {
                $response = Helper::apiNotFoundResponse(true, 'No records found', []);
            }
        }
        catch (Exception $ex)
        {
            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', []);
        }

        return $response;
    }

    public function editCLub($request, $id)
    {
        try
        {
            $club = $this::select('id', 'user_name', 'title', 'type', 'primary_color', 'secondary_color', 'privacy', 'image', 'is_verified')
            ->where('id', $id)
            ->where('owner_id', auth()->user()->id)
            ->first();

            if (!empty($club))
            {
                $record = (new DetailsResource($club))->resolve();

                $response = Helper::apiSuccessResponse(true, 'Record found', $record);
            }
            else
            {
                $response = Helper::apiNotFoundResponse(true, 'No record found', new stdClass());
            }
        }
        catch (Exception $ex)
        {
            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', new stdClass());
        }

        return $response;
    }

    public function verificationRequest($request)
    {
        try
        {
            $club = $this::select('id', 'user_name', 'title', 'type', 'primary_color', 'secondary_color', 'privacy', 'image')
            ->where('owner_id', auth()->user()->id)
            ->where('id', $request->id)
            ->first();

            if ($club)
            {
                $user = Setting::select('value')
                ->where('key', 'notifications_emails')
                ->first();
                
                if (isset($user->value) && count(unserialize($user->value)['EMAIL']) > 0)
                {
                    $mailData = [
                        'club' => $club
                    ];

                    foreach (unserialize($user->value)['EMAIL'] as $key => $email)
                    {
                        $user = new stdClass();
                        
                        $user->email = $email;
                        $user->first_name = 'Admin';
                        $user->last_name = 'Jogo';

                        //send otp on email
                        $sendEmail = Helper::sendMail('emails.requestClubVerification', 'Club Verification Request', $mailData, $user, 'blockLog');

                        /*if ($sendEmail == 'success')
                        {*/
                            $response = Helper::apiSuccessResponse(true, 'Your request to verify this club has sent successfully to Jogo admin', new stdClass());
                        /*}
                        else
                        {
                            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', new stdClass());

                            break;
                        }*/
                    }
                }
                else
                {
                    $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong, please contact admin', new stdClass());
                }
            }
            else
            {
                $response = Helper::apiNotFoundResponse(false, 'Invalid club id', new stdClass());
            }
        }
        catch (Exception $ex)
        {
            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', new stdClass());
        }

        return $response;
    }

    public function remove($request, $id)
    {
        DB::beginTransaction();

        try
        {   
            $club = $this::with([
                'teams' => function ($query)
                {
                    $query->select('club_id', 'team_id');
                },
                'trainers' => function ($query)
                {
                    $query->select('club_id', 'trainer_user_id');
                }
            ])
            ->select('id', 'owner_id')
            ->where('id', $request->id)
            ->first();

            if (!$club)
            {
                 $response = Helper::apiNotFoundResponse(false, 'Invalid club id', new stdClass());
            }
            else if ($club->owner_id != auth()->user()->id)
            {
                $response = Helper::apiErrorResponse(false, 'Only owner have the permissions to delete clubs', new stdClass());
            }
            else
            {
                $trainers = $club->trainers()->pluck('trainer_user_id')->toArray();

                if (count($club->teams) > 0)
                {
                    $jogoTeam = Team::with('trainers')
                    ->where('team_name', 'like', '%jogo%')
                    ->first();

                    if ($jogoTeam)
                    {
                        $jogoTeam->trainers()->syncWithoutDetaching($trainers);
                    }
                }

                if (count($club->trainers) > 0)
                {
                    $jogoClub = $this::with('trainers')
                    ->where('title', 'like', '%jogo%')
                    ->first();

                    if ($jogoClub)
                    {
                        $jogoClub->trainers()->syncWithoutDetaching($trainers);
                    }
                }

                $club->trainers()->sync([]);

                $teamsId = $club->teams->pluck('team_id')->toArray();

                $club->teams()->sync([]);

                Team::whereIn('id', $teamsId)
                ->delete();
                
                $club->delete();

                DB::commit();

                $response = Helper::apiSuccessResponse(true, 'You\'ve successfully deleted your club', new stdClass());
            }
        }
        catch (Exception $ex)
        {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', new stdClass());
        }

        return $response;
    }

    protected function trainersQuery($request, $limit, $offset, $sortingColumn, $sortingType, $status, $columns, $relationalColumns, $trainersType)
    {
        $club = $this::with($relationalColumns)
        ->select($columns)
        ->where('id', $request->clubId)
        ->whereHas('trainers');

        $totalRecords = $club->count();

        $club = $club->first();

        return [
            'totalRecords' => $totalRecords,
            'club' => $club
        ];
    }

    public function viewTrainers($request, $limit = 10, $offset = 0, $sortingColumn = 'created_at', $sortingType = 'asc', array $status = ['active'], array $columns = ['id', 'first_name', 'last_name', 'email', 'profile_picture', 'last_seen'], array $relationalColumns = [], $trainersType = 'yes')
    {
        try
        {
            $trainersQuery = $this->trainersQuery($request, $limit, $offset, $sortingColumn, $sortingType, $status, $columns, $relationalColumns, $trainersType);

            $totalRecords = $trainersQuery['totalRecords'];

            $club = $trainersQuery['club'];

            if ($totalRecords > 0 && $club->trainers->count() > 0)
            {
                $request->ownerId = $club->owner_id ?? NULL;
                
                $records = ListingResource::collection($club->trainers)->toArray($request);

                $response = Helper::apiSuccessResponse(true, 'Records found', $records);
            }
            else
            {
                $response = Helper::apiNotFoundResponse(false, 'No records found', []);
            }
        }
        catch (Exception $ex)
        {
            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', []);
        }

        return $response;
    }

    public function viewTrainer($request, array $status = [], array $columns = [], array $relationalColumns = [], $trainersType = 'yes')
    {
        try
        {
            $trainersQuery = $this->trainersQuery($request, 1, 1, 'created_at', 'desc', $status, $columns, $relationalColumns, $trainersType);

            $totalRecords = $trainersQuery['totalRecords'];

            $club = $trainersQuery['club'];

            if ($totalRecords > 0 && $club->trainers->count() > 0)
            {
                $request->ownerId = $club->owner_id ?? NULL;
                
                $request->owner_id = $club->owner_id;

                $record = (new TrainerDetailsResource($club->trainers[0]))->resolve($request);

                $response = Helper::apiSuccessResponse(true, 'Success', $record);
            }
            else
            {
                $response = Helper::apiNotFoundResponse(false, 'No record found', new stdClass());
            }
        }
        catch (Exception $ex)
        {
            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', new stdClass());
        }

        return $response;
    }

    public function userHaveClub($request,$club_id){
        $clubs = (new Club())->myCLubs($request);
        $club_ids = [];
        foreach ($clubs->original["Result"] as $club)
        {
            $club_ids[] = $club['id'];
        }
        if(!in_array($club_id,$club_ids)){
            return false;
        }

        return true;
    }
}