<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Subscriptions extends Migration
{

    protected $billableTableName;

    /**
     * Our billable model's table name must be set in here for usage of tables.
     */
    public function __construct()
    {
        $billableModelName       = config('iyzipay.billableModel');
        $this->billableTableName = (new $billableModelName)->getTable();
    }

    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('billable_id');
            $table->foreign('billable_id')->references('id')->on($this->billableTableName);
            $table->double('next_charge_amount')->default(0);
            $table->string('currency')->default('try');
            $table->timestamp('next_charge_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->longText('plan');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
