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
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reactable_type');
            $table->unsignedBigInteger('reactable_id');
            $table->unsignedTinyInteger('type');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(
                ['user_id', 'reactable_type', 'reactable_id'],
                'reactions_user_reactable_unique'
            );
            $table->index(
                ['reactable_type', 'reactable_id', 'type'],
                'reactions_reactable_lookup_index'
            );
            $table->index(
                ['reactable_type', 'reactable_id', 'created_at'],
                'reactions_reactable_created_at_index'
            );
            $table->index(
                ['user_id', 'type', 'created_at'],
                'reactions_user_type_created_at_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
