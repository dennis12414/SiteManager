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
        Schema::table('paymentTransactions', function (Blueprint $table) {
            $table->dropColumn(['resultCode', 'resultDesc', 'originatorConversationId', 'conversationId', 'transactionAmount', 'transactionReceipt', 'receiverName', 'receiverPhoneNumber', 'transactionCompletedDateTime', 'utilityAccountAvailableFunds', 'workingAccountAvailableFunds', 'recipientRegistered', 'chargesPaidAvailableFunds']);
            $table->string('workDate')->nullable();
            $table->integer('siteManagerId')->nullable();
            $table->integer('workerId')->nullable();
            $table->integer('projectId')->nullable();
            $table->decimal('payRate', 10, 2)->nullable();
            $table->string('partnerTransactionID')->nullable();
            $table->string('receiptNumber')->nullable();
            $table->string('payerTransactionID')->nullable();
            $table->string('statusCode')->nullable();
            $table->string('message')->nullable();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paymentTransactions', function (Blueprint $table) {
            $table->dropColumn(['workDate', 'siteManagerId', 'workerId', 'projectId', 'payRate', 'partnerTransactionID', 'receiptNumber', 'transactionStatus']);
        });
    }
    
};

