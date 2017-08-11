<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePeopleToContactForKids extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kids', function (Blueprint $table) {
            $table->integer('child_of_contact_id')->after('company_id');
        });
        Schema::table('kids', function (Blueprint $table) {
            $table->dropColumn(
                'child_of_people_id'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kids', function (Blueprint $table) {
            $table->integer('child_of_people_id')->after('company_id');
        });
        Schema::table('kids', function (Blueprint $table) {
            $table->dropColumn(
                'child_of_contact_id'
            );
        });
    }
}
