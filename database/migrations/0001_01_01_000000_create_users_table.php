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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('birthdate'); // Use 'date' for birthdate
            $table->string('birthplace'); // Use 'string' for birthplace
            $table->bigInteger('phone_number');
            $table->enum('gender', ['Male', 'Female']); // Use 'enum' for gender
            $table->enum('status', ['Single', 'Married', 'Single Parent', 'Widow'])->default('Single');
            $table->string('address'); // Use 'string' for birthplace
            $table->string('department')->nullable(); // Use 'string' for birthplace
            $table->string('position')->nullable(); // Use 'string' for birthplace
            $table->string('designation')->nullable(); // Use 'string' for birthplace
            $table->string('work_status')->nullable();
            $table->string('category')->nullable();  
            $table->string('img')->nullable();
            $table->string('acc_code')->nullable();
            $table->date('reg_approval')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
