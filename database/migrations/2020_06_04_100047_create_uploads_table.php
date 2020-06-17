<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateUploadsTable extends Migration
{
    /**
     * Run the migrations for uploads & upload_files table
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->string('ext_upload_id');
            $table->timestamps();
        });
        
        Schema::create('upload_files', function (Blueprint $table) {
            $table->id();
            $table->string('ext_upload_item_id');
            $table->bigInteger('upload_id')->unsigned();
            $table->string('file_name');
            $table->string('file_type');
            $table->bigInteger('file_size');
            $table->text('upload_url');
            $table->timestamps();
            $table->foreign('upload_id')->references('id')->on('uploads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upload_files');
    }
}
