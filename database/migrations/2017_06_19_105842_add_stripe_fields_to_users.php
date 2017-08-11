<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddStripeFieldsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function ($table) {
            $table->string('stripe_id')->after('api_key')->nullable();
            $table->string('card_brand')->after('stripe_id')->nullable();
            $table->string('card_last_four')->after('card_brand')->nullable();
            $table->timestamp('trial_ends_at')->after('card_last_four')->nullable();
        });
        Schema::create('subscriptions', function ($table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->string('name');
            $table->string('stripe_id');
            $table->string('stripe_plan');
            $table->integer('quantity');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }
}
