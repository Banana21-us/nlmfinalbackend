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
        Schema::create('leave_reqs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userid');
            $table->unsignedBigInteger('leavetypeid');
            $table->date('from');
            $table->date('to');
            $table->string('reason');
            $table->string('DHead')->nullable();
            $table->enum('dept_head', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->enum('exec_sec', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->enum('president', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_reqs');
    }
};
