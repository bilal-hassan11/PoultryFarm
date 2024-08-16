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
        Schema::create('other_invoices', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('shade_id');
            $table->unsignedBigInteger('invoice_no');
            $table->unsignedBigInteger('account_id');
            $table->string('ref_no')->nullable();
            $table->string('description')->nullable();
            $table->unsignedBigInteger('item_id');
            $table->decimal('purchase_price', 16, 2);
            $table->decimal('sale_price', 16, 2);
            $table->decimal('quantity', 16, 2)->default(0.00);
            $table->decimal('amount', 16, 2)->default(0.00);
            $table->decimal('discount_in_rs', 16, 2)->default(0.00);
            $table->decimal('discount_in_percent', 16, 2)->default(0.00);
            $table->decimal('total_cost', 16, 2)->default(0);
            $table->decimal('net_amount', 16, 2)->default(0);
            $table->date('expiry_date')->nullable();
            $table->enum('type', ['Purchase', 'Sale', 'Purchase Return', 'Sale Return', 'Adjust In', 'Adjust Out'])->nullable();
            $table->enum('stock_type', ['In', 'Out'])->default('In');
            $table->enum('is_draft', [1, 0])->default(0);
            $table->enum('whatsapp_status', ['Sent', 'Not Sent'])->default('Sent');
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('other_invoices');
    }
};
