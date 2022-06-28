<?php
namespace App;
use App\Http\Resources\Api\Dashboard\General\LanguagesResource;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Language extends Model
{
    use SoftDeletes;

    public function viewLanguages($request)
    {
        try
        {
            $languages = Language::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();
        
            $languages = LanguagesResource::collection($languages)->toArray($request);

            if (count($languages) > 0)
            {
                $response = Helper::apiSuccessResponse(true, 'Records found', $languages);
            }
            else
            {
                $response = Helper::apiNotFoundResponse(true, 'No records found', []);
            }
        }
        catch (\Exception $ex)
        {
            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', []);
        }

        return $response;
    }
}