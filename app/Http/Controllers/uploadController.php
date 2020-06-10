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
    public function signedURL()
    {
        
        $s3Client = new S3Client([
            'region'    => env('AWS_DEFAULT_REGION'),
            'version'   => 'latest',
        ]);

        $cmd = $s3Client->getCommand('GetObject', [
            'Bucket'    => env('AWS_BUCKET'),
            'Key'       => 'file_name.ext',
        ]);

        $requestURL = $s3Client->createPresignedRequest($cmd, '+10 minutes');

        $signedURL = (string)$requestURL->getUri();
    }

    public function uploadFiles(Request $request)
    {
        $ext_upload_id = implode($request->only(['ext_upload_id']));
        $upload_items = json_decode($request->input('upload_items'), TRUE);
        $uploadItemFound = false;
        $uploadFound = false;
        
        // Check if uploads available;
        $extUploadID = DB::table('uploads')
                                ->where('ext_upload_id', $ext_upload_id)
                                ->value('ext_upload_id');
        if($ext_upload_id == $extUploadID) {
            $uploadFound = true;
            return response()->json([
                'ext_upload_id'     => $extUploadID,
                'message'           => 'error in uploads, upload id already present',
            ]);
        }

        // Check if upload_items available;

        foreach($upload_items["data"] as $row)
        {
            $ext_upload_item_id = $row['ext_upload_item_id'];
            
            $extUploadItemID = DB::table('upload_files')
                                        ->where('ext_upload_item_id', $ext_upload_item_id)
                                        ->value('ext_upload_item_id');
                                        
            if ($ext_upload_item_id == $extUploadItemID) {
                
                $uploadItemFound = true;
                return response()->json([
                    'ext_upload_item_id' => $extUploadItemID,
                    'message'            => 'error in upload files, upload item id already present',
                ]);
            }
        }

        // Insert to uploads

        if(!$uploadFound && !$uploadItemFound) {
            $Uploads = new uploads;
            $Uploads -> ext_upload_id = $request->ext_upload_id;
            $Uploads -> save();
        }

        // Insert to uploadFiles

        if(!$uploadFound && !$uploadItemFound)
        {
            foreach($upload_items["data"] as $row) {
                
                $uploadFiles = new uploadFiles;
                
                $uploadFiles -> ext_upload_item_id  = $row['ext_upload_item_id'];
                $uploadFiles -> file_name           = $row['file_name'];
                $uploadFiles -> file_type           = $row['file_type'];
                $uploadFiles -> file_size           = $row['file_size'];
                $uploadFiles -> upload_url          = $row['upload_url'];
                
                $uploadFiles -> save();
            }
        }
        
        // Return after DB store

        return response()->json([
            'uploads'       => $Uploads,
            'upload_files'  => $upload_items,
        ]);
    }
}
