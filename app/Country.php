<?php
namespace App;
use App\Http\Resources\Api\Dashboard\General\CountriesResource;
use App\Http\Resources\Api\Dashboard\General\CountryCodesResource;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes;
    
    public $locale;

    private $defaultLocale;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->locale = App::getLocale();
        $this->defaultLocale = 'en';
    }

    public function getNameAttribute($value){

        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    public function getFlagAttribute($value){
        $iso = strtolower($value);
        return "https://flagcdn.com/w160/".$iso. ".png";
    }

    public function viewCountries($request)
    {
        try
        {
            $countries = $this::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

            $countries = CountriesResource::collection($countries)->toArray($request);
            
            if (count($countries) > 0)
            {
                $response = Helper::apiSuccessResponse(true, 'Records found', $countries);
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

    public function viewCountryCodes($request)
    {
        try
        {
            $countryCodes = $this::select('id', 'phone_code')
            ->orderBy('name', 'asc')
            ->get();
        
            $countryCodes = CountryCodesResource::collection($countryCodes)->toArray($request);

            if (count($countryCodes) > 0)
            {
                $response = Helper::apiSuccessResponse(true, 'Records found', $countryCodes);
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
}