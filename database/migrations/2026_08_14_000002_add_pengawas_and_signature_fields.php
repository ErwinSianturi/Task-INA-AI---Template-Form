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
            $table->string('signature_path')->nullable()->after('role');
        });

        Schema::table('travel_requests', function (Blueprint $table) {
            $table->foreignId('approved_by_user_id')->nullable()->after('manager_id')->constrained('users')->onDelete('set null');
        });

        Schema::table('reimbursements', function (Blueprint $table) {
            $table->foreignId('approved_by_user_id')->nullable()->after('finance_id')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reimbursements', function (Blueprint $table) {
            $table->dropForeign(['approved_by_user_id']);
            $table->dropColumn('approved_by_user_id');
        });

        Schema::table('travel_requests', function (Blueprint $table) {
            $table->dropForeign(['approved_by_user_id']);
            $table->dropColumn('approved_by_user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('signature_path');
        });
    }
};
