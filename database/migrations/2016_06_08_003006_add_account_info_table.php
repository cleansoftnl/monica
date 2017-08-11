<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddAccountInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('company_id')->after('remember_token');
            $table->string('send_sms_alert')->default('false')->after('company_id');
            $table->integer('phone_number')->nullable()->after('send_sms_alert');
            $table->integer('amazon_store_country_id')->nullable()->after('phone_number');
            $table->string('timezone')->nullable()->after('amazon_store_country_id');
            $table->string('locale')->default('en')->after('timezone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function ($table) {
            $table->dropColumn(['company_id', 'send_sms_alert', 'phone_number', 'amazon_store_country_id']);
        });
    }
}
