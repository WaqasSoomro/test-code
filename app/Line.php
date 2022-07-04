<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\Api\Dashboard\General\LinesListingResource;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;

class Line extends Model
{
    use SoftDeletes;

    public $locale;

    private $defaultLocale = 'en';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->locale = App::getLocale();
    }

    public function getNameAttribute($value){
        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    public function viewLines($request, array $columns = ['id', 'name'], $limit = 10, $offset = 0, $sortingColumn = 'created_at', $sortingType = 'asc')
    {
        try
        {
            $lines = $this::select($columns)
            ->orderBy($sortingColumn, $sortingType)
            ->limit($limit)
            ->offset($offset);

            $totalLines = $lines->count();
            $lines = $lines->get();

            if ($totalLines > 0)
            {
                $lines = LinesListingResource::collection($lines)->toArray($request);

                $response = Helper::apiSuccessResponse(true, 'Records found successfully', $lines);
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
}