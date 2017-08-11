<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(
                'people_id', 'deleted_at'
            );
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('contact_id')->after('company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(
                'contact_id'
            );
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('people_id')->after('company_id');
            $table->softDeletes();
        });
    }
}
