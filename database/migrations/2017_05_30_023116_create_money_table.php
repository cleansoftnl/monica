<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateMoneyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('debts', function ($table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->integer('contact_id');
            $table->string('in_debt')->default('no');
            $table->string('status')->default('inprogress');
            $table->integer('amount');
            $table->longText('reason')->nullable();
            $table->timestamps();
        });
    }
}
