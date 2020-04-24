<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreditCards extends Migration
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
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('billable_id');
            $table->foreign('billable_id')->references('id')->on($this->billableTableName);
            $table->string('alias', 100);
            $table->string('number', 10);
            $table->string('token');
            $table->unique(['billable_id', 'token']);
            $table->string('bank')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('credit_cards');
    }
}
