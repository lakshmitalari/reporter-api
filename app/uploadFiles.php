<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class uploadFiles extends Model
{
    //
    protected $table = 'upload_files';
    
    protected $fillable = [
        'ext_upload_item_id',
        'file_name',
        'file_type',
        'file_size',
        'upload_url',
    ];
}
