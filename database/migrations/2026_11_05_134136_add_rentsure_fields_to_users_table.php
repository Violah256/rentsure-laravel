<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'tenant',
                'landlord',
                'agent',
                'administrator',
            ])->default('tenant')->after('email');

            $table->string('phone', 30)
                ->nullable()
                ->after('role');

            $table->string('neighbourhood')
                ->nullable()
                ->after('phone');

            $table->enum('account_status', [
                'active',
                'suspended',
            ])->default('active')->after('neighbourhood');

            $table->timestamp('last_login_at')
                ->nullable()
                ->after('account_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'phone',
                'neighbourhood',
                'account_status',
                'last_login_at',
            ]);
        });
    }
};