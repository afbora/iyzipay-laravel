<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Transactions extends Migration
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('billable_id');
            $table->foreign('billable_id')->references('id')->on($this->billableTableName);
            $table->unsignedInteger('credit_card_id');
            $table->foreign('credit_card_id')->references('id')->on('credit_cards');
            $table->unsignedInteger('subscription_id')->nullable();
            $table->foreign('subscription_id')->references('id')->on('subscriptions');
            $table->double('amount');
            $table->string('currency', 3)->default('TRY');
            $table->longText('products');
            $table->string('iyzipay_key');
            $table->longText('refunds')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
