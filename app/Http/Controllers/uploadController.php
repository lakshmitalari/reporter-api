<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use App\uploads;
use App\uploadFiles;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class uploadController extends Controller
{
    public function uploadFiles(Request $request)
    {
        $ext_upload_id = implode($request->only(['ext_upload_id']));

        $extUploadID = DB::table('uploads')
                                ->where('ext_upload_id', $ext_upload_id)
                                ->value('ext_upload_id');

        if ($ext_upload_id == $extUploadID) {
            return response()->json([
                'ext_upload_id' => $extUploadID,
                'message'       => 'ext_upload_id already present',
            ]);
        }
        else {
            
        }
        }
}
