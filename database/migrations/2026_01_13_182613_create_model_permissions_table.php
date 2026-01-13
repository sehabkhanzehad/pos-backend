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
        Schema::create('model_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('assignable_id');
            $table->string('assignable_type');
            $table->timestamps();

            $table->unique(['permission_id', 'assignable_id', 'assignable_type'], 'assignable_permission_unique');
            $table->index(['assignable_id', 'assignable_type'], 'assignable_index');
            $table->index('permission_id', 'permission_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_permissions');
    }
};
