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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('projectId');
            $table->foreign('projectId')->references('projectId')->on('projects')->onDelete('cascade');
            $table->unsignedBigInteger('siteManagerId');
            $table->foreign('siteManagerId')->references('siteManagerId')->on('siteManagers')->onDelete('cascade');
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
