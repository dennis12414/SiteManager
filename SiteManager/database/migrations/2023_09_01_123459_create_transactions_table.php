<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Transactions', function (Blueprint $table) {
            $table->id('Id');
            $table->string('payType')->nullable();
            $table->string('statusCode')->nullable();
            $table->string('partnerReferenceID')->nullable();
            $table->string('transactionID')->nullable(); //generate by backend pay
            $table->string('message')->nullable();
            $table->string('narration')->nullable();
            $table->string('partnerTransactionID')->nullable(); //unique id pay from callback
            $table->string('payerTransactionID')->nullable();//unique id pay
            $table->string('receiptNumber')->nullable();
            $table->string('siteManagerId')->nullable();
            $table->integer('workerId')->nullable();
            $table->string('workDate')->nullable();
            $table->integer('projectId')->nullable();
            $table->decimal('payRate', 10, 2)->nullable();
            $table->string('phoneNumber')->nullable();
            $table->decimal('transactionAmount', 10, 2);
            $table->string('transactionStatus')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Transactions');
    }
};
