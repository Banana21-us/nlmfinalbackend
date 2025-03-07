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
        Schema::create('requestfiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userid');
            $table->string('description');
            $table->text('file');
            $table->dateTime('time');
            $table->timestamps();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requestfiles');
    }
};
