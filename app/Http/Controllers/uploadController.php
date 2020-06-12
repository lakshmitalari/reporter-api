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
    public function ifExists(Request $request)
    {
        $existing_upload_id = implode($request->only(['ext_upload_id']));
        $existing_upload_items = json_decode($request->input('upload_items'), TRUE);

        $existingUploads = DB::table('uploads')
                                    ->where('ext_upload_id', $existing_upload_id)
                                    ->delete();
        
        foreach($existing_upload_items["data"] as $row)
        {
            $existing_ext_upload_item_id = $row['ext_upload_item_id'];
            
            $existingUploadItems = DB::table('upload_files')
                                        ->where('ext_upload_item_id', $existing_ext_upload_item_id)
                                        ->delete();
        }
    }

    public function signedURL($file_name)
    {
        $s3Client = new S3Client([
            'region'    => env('AWS_DEFAULT_REGION'),
            'version'   => 'latest',
        ]);

        $cmd = $s3Client->getCommand('GetObject', [
            'Bucket'    => env('AWS_BUCKET'),
            'Key'       => $file_name,
        ]);

        $requestURL = $s3Client->createPresignedRequest($cmd, '+10 minutes');

        $signedURL = (string)$requestURL->getUri();

        return $signedURL;
    }

    public function uploadFiles(Request $request)
    {
        $ext_upload_id = implode($request->only(['ext_upload_id']));
        $upload_items = json_decode($request->input('upload_items'), TRUE);
        $retry_upload = implode($request->only(['retry']));
        $uploadItemFound = false;
        $uploadFound = false;
        
        $extUploadID = DB::table('uploads')
                                ->where('ext_upload_id', $ext_upload_id)
                                ->value('ext_upload_id');

        if($ext_upload_id == $extUploadID && $retry_upload == "true") 
        {
            $this->ifExists($request);
            $uploadFound = false;
        }
        
        else if ($ext_upload_id == $extUploadID)
        {
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
                                        
            if ($ext_upload_item_id == $extUploadItemID) 
            {
                
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
       
        // Insert to uploadFiles
            
            $arruploadItems = array();
            
            foreach($upload_items["data"] as $row) {

                $file_name = $row['file_name'];
                
                $uploadURL = $this->signedURL($file_name);

                $uploadFiles = new uploadFiles;
                
                $uploadFiles -> ext_upload_item_id  = $row['ext_upload_item_id'];
                $uploadFiles -> file_name           = $row['file_name'];
                $uploadFiles -> file_type           = $row['file_type'];
                $uploadFiles -> file_size           = $row['file_size'];
                $uploadFiles -> upload_url          = $uploadURL;
                
                $uploadFiles -> save();

                $arruploadItems[] = $uploadFiles;
            }
        }
        
        //Return after DB store

        return response()->json([
            'uploads'       => $Uploads,
            'upload_files'  => $arruploadItems,
        ]);
    }
}
