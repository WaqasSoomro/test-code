<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\Api\Dashboard\Events\CategoriesResource;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\SoftDeletes;
use Exception;
use Illuminate\Support\Facades\App;

class EventCategory extends Model
{
	use SoftDeletes;

    protected $locale;

    protected $appends = [
        "engTitle"
    ];

    private $defaultLocale = 'en';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->locale = App::getLocale();
    }

    public function getTitleAttribute($value)
    {
        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    public function getEngTitleAttribute()
    {
        $title = json_decode($this->getAttributes()['title']);
        $title = strtolower($title->en);

        return $title;
    }

	public function viewCategories($request, $columns = '*', $limit = 10, $offset = 0, $sortingColumn = 'created_at', $sortingType = 'asc', array $status = ['active', 'inactive'])
	{
		try
		{
			$records = EventCategory::select($columns)
			->whereIn('status', $status)
			->orderBy($sortingColumn, $sortingType)
			->limit($limit)
			->offset($offset);

			$totalRecords = $records->count();
			$categories = $records->get();

			if ($totalRecords > 0)
			{
				if ($request->path() == 'api/v4/dashboard/events/categories' || $request->path() == 'api/v1/trainerapp/events/categories')
				{
					$categories = CategoriesResource::collection($categories)->toArray($request);

					$response = Helper::apiSuccessResponse(true, 'Records found successfully', $categories);
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
			'title',
			'color',
			'status'
		];

		return $columns;
	}
}