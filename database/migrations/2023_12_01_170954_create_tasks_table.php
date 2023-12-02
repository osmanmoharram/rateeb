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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('assigner_id')->nullable();
            $table->unsignedBigInteger('assignee_id')->nullable();
            $table->string('status')->default('أنشأت');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('delivery_date')->nullable();
            $table->timestamps();

            $table->foreign('assigner_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assignee_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
