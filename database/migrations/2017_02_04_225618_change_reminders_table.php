<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reminders', function (Blueprint $table) {
            $table->dropColumn(
                'deleted_at', 'people_id'
            );
        });
        Schema::table('reminders', function (Blueprint $table) {
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
        Schema::table('reminders', function (Blueprint $table) {
            $table->dropColumn(
                'contact_id'
            );
        });
        Schema::table('reminders', function (Blueprint $table) {
            $table->integer('people_id')->after('company_id');
            $table->softDeletes();
        });
    }
}
