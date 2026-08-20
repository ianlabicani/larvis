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
        Schema::create('routine_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_id')->constrained()->cascadeOnDelete();
            $table->date('local_date');
            $table->timestamp('scheduled_for')->index();
            $table->string('status')->default('pending')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['routine_id', 'local_date']);
            $table->index(['routine_id', 'status', 'local_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routine_occurrences');
    }
};
