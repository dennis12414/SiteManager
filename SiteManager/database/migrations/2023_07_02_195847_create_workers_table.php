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
        Schema::create('workers', function (Blueprint $table) {
            $table->id('worker_id');
            $table->string('name');
            $table->string('phone_number')->unique();
            $table->timestamp('date_registered')->nullable();
            $table->string('pay_rate')->nullable();
            $table->unsignedBigInteger('site_manager_id');
            $table->foreign('site_manager_id')->references('site_manager_id')->on('site_managers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};
