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
        Schema::create('paymentTransactions', function (Blueprint $table) {
            $table->id('paymentTransactionId');
            $table->integer('resultCode');
            $table->string('resultDesc');
            $table->string('originatorConversationId');
            $table->string('conversationId');
            $table->string('transactionId');
            $table->decimal('transactionAmount', 10, 2);
            $table->string('transactionReceipt');
            $table->string('receiverName');
            $table->string('receiverPhoneNumber');
            $table->dateTime('transactionCompletedDateTime');
            $table->decimal('utilityAccountAvailableFunds', 10, 2);
            $table->decimal('workingAccountAvailableFunds', 10, 2);
            $table->string('recipientRegistered');
            $table->decimal('chargesPaidAvailableFunds', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paymentTransactions');
    }
};
