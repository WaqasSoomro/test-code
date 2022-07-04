<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UploadController extends Controller
{
    public function upload(Request $request){
        Validator::make($request->all(), [
            'filename' => 'required',
            'filepath' => 'required|file',
        ])->validate();
        try{
            $file = $request->file('filepath');
            $file_name = $request->filename;
            $fileData = file_get_contents($file);
            Storage::put($file_name, $fileData);

            return Helper::apiSuccessResponse(true, 'CSV Uploaded successfully', "https://jogobucket-1.s3.eu-west-2.amazonaws.com/".$file_name);
        }catch(\Exception $exception){
            return Helper::apiErrorResponse(false, $exception->getMessage(), new \stdClass());
        }
    }
}
