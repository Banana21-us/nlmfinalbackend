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
        Schema::create('empfamilies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userid');
            $table->string('children')->nullable();
            $table->string('dateofbirth')->nullable();
            $table->string('career')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empfamilies');
    }
};
