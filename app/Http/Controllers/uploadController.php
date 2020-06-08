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
            $Uploads = new uploads;
            $Uploads -> ext_upload_id = $request->ext_upload_id;
            $Uploads -> save();
        }

        $upload_items = json_decode($request->input('upload_items'), TRUE);

        foreach($upload_items["data"] as $row)
        {
            $ext_upload_item_id = $row['ext_upload_item_id'];
            
            $extUploadItemID = DB::table('upload_files')
                                        ->where('ext_upload_item_id', $ext_upload_item_id)
                                        ->value('ext_upload_item_id');

            if ($ext_upload_item_id == $extUploadItemID) {
                return response()->json([
                    'ext_upload_item_id' => $extUploadItemID,
                    'message'            => 'error in upload files, files already present',
                ]);
            }
            else {
                $uploadFiles = new uploadFiles;
                
                $uploadFiles -> ext_upload_item_id = $row['ext_upload_item_id'];
                $uploadFiles -> file_name = $row['file_name'];
                $uploadFiles -> file_type = $row['file_type'];
                $uploadFiles -> file_size = $row['file_size'];
                $uploadFiles -> upload_url= $row['upload_url'];
                
                $uploadFiles -> save();
            }
        }
        return response()->json([
            'uploads'       => $Uploads,
            'upload_files'  => $uploadFiles,
        ]);
    }
}
