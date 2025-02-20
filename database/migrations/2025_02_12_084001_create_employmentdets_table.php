<?php

use App\Models\position;
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
        Schema::create('employmentdets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userid');
            $table->string('position');
            $table->string('organization');
            $table->date('dateofemp');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employmentdets');
    }
};
