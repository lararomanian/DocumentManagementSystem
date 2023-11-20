<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string("title")->nullable();
            $table->string("description")->nullable()->nullable();
            $table->string("slug")->nullable();
            $table->string("file")->nullable();
            $table->longText("ocr_text")->nullable()->nullable();
            $table->boolean("status")->nullable()->nullable();
            $table->unsignedBigInteger("project_id")->nullable();
            $table->foreign("project_id")->references("id")->on("projects")->nullable();
            $table->unsignedBigInteger("created_by")->nullable();
            $table->unsignedBigInteger("updated_by")->nullable();
            $table->foreign("created_by")->references("id")->on("users")->onDelete("cascade");
            $table->foreign("updated_by")->references("id")->on("users")->onDelete("cascade");

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
}
