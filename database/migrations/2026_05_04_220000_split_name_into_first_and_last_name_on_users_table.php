<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->default('');
            $table->string('last_name')->default('');
        });

        if (Schema::hasColumn('users', 'name')) {
            DB::table('users')
                ->select(['id', 'name'])
                ->orderBy('id')
                ->chunkById(100, function ($users): void {
                    foreach ($users as $user) {
                        $parts = preg_split('/\s+/', trim((string) $user->name), 2) ?: [];

                        DB::table('users')
                            ->where('id', $user->id)
                            ->update([
                                'first_name' => $parts[0] ?? '',
                                'last_name' => $parts[1] ?? '',
                            ]);
                    }
                });

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->default('');
        });

        DB::table('users')
            ->select(['id', 'first_name', 'last_name'])
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'name' => trim($user->first_name.' '.$user->last_name),
                        ]);
                }
            });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
