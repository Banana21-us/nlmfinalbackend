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
        Schema::create('yearsofservices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userid');
            $table->string('organization');
            $table->date('start_date');
            $table->date('end_date')->nullable();;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yearsofservices');
    }
};
