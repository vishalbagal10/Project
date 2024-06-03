<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblSharedCvTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_shared_cv', function (Blueprint $table) {
            $table->id();
            $table->integer('cv_id')->comment('Id from tbl_cvs');
            $table->string('email', 255);
            $table->string('validity_time', 25);
            $table->string('share_link_token', 255);
            $table->string('shared_by', 25)->comment('Id/name from tbl_users');
            $table->integer('view_count')->nullable();
            $table->string('view_date', 25)->nullable()->comment('Last view date will be store');
            $table->integer('is_active')->default(0);
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
        Schema::dropIfExists('tbl_shared_cv');
    }
}
