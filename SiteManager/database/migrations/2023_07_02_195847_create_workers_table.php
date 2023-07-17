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
            $table->id('workerId');
            $table->string('name');
            $table->string('phoneNumber');
            $table->timestamp('dateRegistered')->nullable();
            $table->string('payRate')->nullable();
            $table->unsignedBigInteger('siteManagerId');
            $table->foreign('siteManagerId')->references('siteManagerId')->on('siteManagers');
            $table->softDeletes();
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
