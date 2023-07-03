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
        Schema::create('clock_ins', function (Blueprint $table) {
            $table->id('clock_id');
            $table->timestamp('date')->nullable();
            $table->timestamp('clock_in_time')->nullable();
            $table->timestamp('clock_out_time')->nullable();
            $table->unsignedBigInteger('worker_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('site_manager_id');
            $table->foreign('worker_id')->references('worker_id')->on('workers');
            $table->foreign('project_id')->references('project_id')->on('projects');
            $table->foreign('site_manager_id')->references('site_manager_id')->on('site_managers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clock_ins');
    }
};
