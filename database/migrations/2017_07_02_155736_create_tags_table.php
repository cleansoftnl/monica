<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->string('name');
            $table->string('name_slug');
            $table->mediumText('description')->nullable();
            $table->timestamps();
        });
        Schema::create('contact_tag', function (Blueprint $table) {
            $table->integer('contact_id');
            $table->integer('tag_id');
            $table->integer('company_id');
            $table->timestamps();
        });
    }
}
