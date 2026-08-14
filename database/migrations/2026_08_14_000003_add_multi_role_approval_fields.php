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
        Schema::table('travel_requests', function (Blueprint $table) {
            $table->foreignId('category_approver_id')->nullable()->after('approved_by_user_id')->constrained('users')->onDelete('set null');
            $table->dateTime('category_approved_at')->nullable()->after('category_approver_id');
            $table->dateTime('manager_approved_at')->nullable()->after('category_approved_at');
            $table->foreignId('pantro_id')->nullable()->after('manager_approved_at')->constrained('users')->onDelete('set null');
            $table->dateTime('pantro_approved_at')->nullable()->after('pantro_id');
        });

        Schema::table('reimbursements', function (Blueprint $table) {
            $table->foreignId('manager_id')->nullable()->after('user_id')->constrained('users')->onDelete('set null');
            $table->foreignId('category_approver_id')->nullable()->after('approved_by_user_id')->constrained('users')->onDelete('set null');
            $table->dateTime('category_approved_at')->nullable()->after('category_approver_id');
            $table->dateTime('manager_approved_at')->nullable()->after('category_approved_at');
            $table->dateTime('finance_approved_at')->nullable()->after('manager_approved_at');
            $table->foreignId('pantro_id')->nullable()->after('finance_approved_at')->constrained('users')->onDelete('set null');
            $table->dateTime('pantro_approved_at')->nullable()->after('pantro_id');
            $table->foreignId('tungsen_id')->nullable()->after('pantro_approved_at')->constrained('users')->onDelete('set null');
            $table->dateTime('tungsen_approved_at')->nullable()->after('tungsen_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reimbursements', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropForeign(['category_approver_id']);
            $table->dropForeign(['pantro_id']);
            $table->dropForeign(['tungsen_id']);
            $table->dropColumn([
                'manager_id',
                'category_approver_id', 'category_approved_at',
                'manager_approved_at', 'finance_approved_at',
                'pantro_id', 'pantro_approved_at',
                'tungsen_id', 'tungsen_approved_at'
            ]);
        });

        Schema::table('travel_requests', function (Blueprint $table) {
            $table->dropForeign(['category_approver_id']);
            $table->dropForeign(['pantro_id']);
            $table->dropColumn([
                'category_approver_id', 'category_approved_at',
                'manager_approved_at',
                'pantro_id', 'pantro_approved_at'
            ]);
        });
    }
};
