<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class uploads extends Model
{
    protected $table = 'uploads';
    protected $fillable = ['ext_upload_id'];

    public function uploadItems()
    {
        return $this->hasMany('\App\uploadFiles::class');
    }
}