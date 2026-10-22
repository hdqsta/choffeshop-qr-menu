<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove the implicit "cashier" role default so a user created without an
     * explicitly assigned role holds no privileges at all. Staff accounts are
     * provisioned by an administrator through the staff management screen.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->whereNull('role')->update(['role' => 'cashier']);

        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->nullable(false)->default('cashier')->change();
        });
    }
};
