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
            $table->id();
            $table->unsignedBigInteger('workerId');
            $table->foreign('workerId')->references('workerId')->on('workers');
            $table->string('transactionReference')->unique();
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
