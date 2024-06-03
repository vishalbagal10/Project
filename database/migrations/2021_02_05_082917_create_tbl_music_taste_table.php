<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblMusicTasteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_music_taste', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('media_url', 255);
            $table->text('description');
            $table->integer('is_active')->default(0);
            $table->timestamps();
            $table->string('created_by', 25);
            $table->string('edited_by', 25);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_music_taste');
    }
}
