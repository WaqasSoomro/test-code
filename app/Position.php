<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\Api\Dashboard\Teams\Positions\IndexResource;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use App\Helpers\Helper;
use Exception;

class Position extends Model
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

    public function line()
    {
        return $this->belongsTo(Line::class, 'lines');
    }

    public function viewPositions($request, $columns = '*', $limit = 10, $offset = 0, $sortingColumn = 'created_at', $sortingType = 'asc', array $status = ['active', 'inactive'])
	{
		try
		{
			$records = $this::select($columns)
			->with([
				'line' => function ($query)
				{
					$query->select('id', 'name');
				}
			])
			->orderBy($sortingColumn, $sortingType)
			->limit($limit)
			->offset($offset);

			$totalRecords = $records->count();
			$positions = $records->get();

			if ($totalRecords > 0)
			{
				if ($request->path() == 'api/v4/dashboard/general/positions'
                    || $request->path() == 'api/v1/trainerapp/teams/positions'
                    || $request->path() == 'api/v1/trainerapp/teams/get-teams-and-positions')
				{
					$positions = IndexResource::collection($positions)->toArray($request);

					$response = Helper::apiSuccessResponse(true, 'Records found successfully', $positions);
				}
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

	public function generalColumns()
	{
		$columns = [
			'id',
			'name',
			'lines'
		];

		return $columns;
	}
}
