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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('email');
            $table->boolean('is_admin')->default(false)->after('is_active');
            $table->boolean('marketing_consent')->default(false)->after('is_admin');
            $table->timestamp('marketing_consented_at')->nullable()->after('marketing_consent');
            $table->index(['is_active', 'is_admin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'is_admin']);
            $table->dropColumn(['is_active', 'is_admin', 'marketing_consent', 'marketing_consented_at']);
        });
    }
};
