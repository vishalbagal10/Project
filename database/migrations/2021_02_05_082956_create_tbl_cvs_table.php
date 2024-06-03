<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblCvsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_cvs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 25);
            $table->integer('status')->default(0);
            $table->integer('brand_id')->nullable()->comment('Id from tbl_brand');
            $table->string('cv_date', 10)->comment('MM/YYYY');
            $table->integer('industry_id')->nullable()->comment('Id from tbl_industry');
            $table->string('music_taste_ids', 255)->nullable()->comment('Ids from tbl_music_taste will be stored by separator like (, or |)	');
            $table->string('recognition_titles', 2500)->nullable();
            $table->string('recognition_numbers', 2500)->nullable();
            $table->longText('recognition_descriptions')->nullable();
            $table->string('experience_b1_title', 255)->nullable();	
            $table->string('experience_b1_ids', 255)->nullable()->comment('Ids from tbl_experience will be stored by separator like (, or |)	');
            $table->string('experience_b1_numbers', 255)->nullable()->comment('Numbers will be stored by separator like (, or |)');
            $table->string('experience_b2_title', 255)->nullable();	
            $table->string('experience_b2_ids', 255)->nullable()->comment('Ids from tbl_experience will be stored by separator like (, or |)	');
            $table->string('experience_b2_numbers', 255)->nullable()->comment('Numbers will be stored by separator like (, or |)');
            $table->string('qualitative_title', 255)->nullable();	
            $table->string('qualitative_ids', 255)->nullable()->comment('Ids from tbl_qualitative will be stored by separator like (, or |)');
            $table->string('qualitative_numbers', 255)->nullable()->comment('Numbers will be stored by separator like (, or |)');
            $table->text('blocks_titles')->nullable()->comment('Titles will be stored by separator');
            $table->longText('blocks_descriptions')->nullable()->comment('Descriptions will be stored by separator');
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
        Schema::dropIfExists('tbl_cvs');
    }
}
