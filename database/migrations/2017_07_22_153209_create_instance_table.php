<?php
use App\Instance;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instances', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid');
            $table->string('current_version');
            $table->string('latest_version')->nullable();
            $table->mediumText('latest_release_notes')->nullable();
            $table->integer('number_of_versions_since_current_version')->nullable();
            $table->timestamps();
        });
        $instance = new Instance;
        $instance->current_version = config('monica.app_version');
        $instance->latest_version = config('monica.app_version');
        $instance->uuid = uniqid();
        $instance->save();
    }
}
