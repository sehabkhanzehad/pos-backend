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
        Schema::create('model_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('assignable_id');
            $table->string('assignable_type');
            $table->timestamps();

            $table->unique(['role_id', 'assignable_id', 'assignable_type'], 'assignable_role_unique');

            $table->index(['assignable_id', 'assignable_type'], 'assignable_index');
            $table->index('role_id', 'role_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_roles');
    }
};
