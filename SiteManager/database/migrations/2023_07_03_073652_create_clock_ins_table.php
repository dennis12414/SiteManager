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
        Schema::create('clockIns', function (Blueprint $table) {
            $table->id('clockId');
            $table->timestamp('date')->nullable();
            $table->timestamp('clockInTime')->nullable();
            $table->unsignedBigInteger('workerId');
            $table->unsignedBigInteger('projectId');
            $table->unsignedBigInteger('siteManagerId');
            $table->foreign('workerId')->references('workerId')->on('workers');
            $table->foreign('projectId')->references('projectId')->on('projects');
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
        Schema::dropIfExists('clockIns');
    }
};
