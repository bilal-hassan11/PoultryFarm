<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_ledger', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('account_id');
            $table->unsignedBigInteger('shade_id')->nullable();
            $table->enum('type', ['Purchase', 'Sale', 'Purchase Return', 'Sale Return', 'Adjust In', 'Adjust Out'])->nullable();
            $table->unsignedBigInteger('medicine_invoice_id')->nullable();
            $table->integer('chick_invoice_id')->nullable();
            $table->integer('murghi_invoice_id')->nullable();
            $table->integer('feed_invoice_id')->nullable();
            $table->integer('other_invoice_id')->nullable();
            $table->integer('cash_id')->nullable();
            $table->integer('payment_id')->nullable();
            $table->integer('expense_id')->nullable();
            $table->integer('debit')->nullable();
            $table->integer('credit')->nullable();
            $table->string('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_ledger');
    }
};
