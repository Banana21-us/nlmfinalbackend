<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('employmenteducs', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->unsignedBigInteger('userid'); // Foreign key to the users table
            $table->string('levels')->nullable(); // Elementary school, nullable
            $table->date('year')->nullable(); // High school, nullable
            $table->string('school')->nullable(); // College, nullable
            $table->string('degree')->nullable(); // Graduate school, nullable
            $table->timestamps(); // Created at and updated at timestamps

         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employmenteducs');
    }
};
