<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Translation;
use Illuminate\Http\Request;

/**
 * @group Translation
 * For APP and Dashboard
 */
class TranslationController extends Controller
{
    /**
    Get Translation

    @response
    {
        "Response": true,
        "StatusCode": 200,
        "Message": "Success",
        "Result": [
            "An exercise name",
            "Something to do in the exercises"
        ]
    }
     * @bodyParam label array
     **/

    public function getTranslation(Request $request){
        $this->validate($request, [
            'label' => 'required|array',
            'label.*' => 'exists:translations,label'
        ]);

        $translations = Translation::whereIn('label',$request->label)->get()->pluck('translation');

        return Helper::apiSuccessResponse(true, 'Success', $translations);
    }
}
