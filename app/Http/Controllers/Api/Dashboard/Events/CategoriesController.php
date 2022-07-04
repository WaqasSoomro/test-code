<?php
namespace App\Http\Controllers\Api\Dashboard\Events;
use App\Http\Controllers\Controller;
use App\EventCategory;
use App\Http\Requests\Api\Dashboard\Events\CategoriesRequest;
use Illuminate\Http\Request;

/**
	@group Dashboard V4 / Events
*/

class CategoriesController extends Controller
{
	private $eventCategoryModel, $categoriesColumns, $limit, $offset, $sortingColumn, $sortingType, $status;

    public function __construct(Request $request)
    {
        $this->eventCategoryModel = EventCategory::class;

        $this->categoriesColumns = (new $this->eventCategoryModel)->generalColumns();

        $this->limit = $request->limit;

        $this->offset = $request->offset;

        $this->sortingColumn = 'created_at';

        $this->sortingType = 'desc';
		
		$this->status = ['active'];
    }

	/**
     	Categories listing

     	@response{
		    "Response": true,
		    "StatusCode": 200,
		    "Message": "Records found successfully",
		    "Result": [
		        {
		            "id": 4,
		            "title": "event",
		            "color": "#00ff9c",
		            "status": "active"
		        }
	        ]
        }

		@response 500{
		    "Response": false,
		    "StatusCode": 500,
		    "Message": "Something wen't wrong",
		    "Result": []
		}

		@response 404{
		    "Response": false,
		    "StatusCode": 404,
		    "Message": "No records found",
		    "Result": []
		}

		@queryParam limit required integer. Example: 10
		@queryParam offset required integer. Example: 0
    */
		
	public function index(CategoriesRequest $request)
	{
		$response = (new $this->eventCategoryModel)->viewCategories($request, $this->categoriesColumns, $this->limit, $this->offset, $this->sortingColumn, $this->sortingType, $this->status);

		return $response;
	}
}