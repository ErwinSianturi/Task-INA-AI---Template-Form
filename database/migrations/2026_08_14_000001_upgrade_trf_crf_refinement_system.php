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
            $table->foreignId('manager_id')->nullable()->after('user_id')->constrained('users')->onDelete('set null');
            $table->text('manager_comment')->nullable()->after('status');
            $table->date('signed_date')->nullable()->after('approved_at');
        });

        Schema::table('reimbursements', function (Blueprint $table) {
            $table->foreignId('finance_id')->nullable()->after('user_id')->constrained('users')->onDelete('set null');
            $table->text('finance_comment')->nullable()->after('status');
            $table->date('signed_date')->nullable()->after('verified_at');
            $table->string('reimbursement_status')->default('not_reimbursed')->after('signed_date'); // not_reimbursed, reimbursed
            $table->timestamp('reimbursed_at')->nullable()->after('reimbursement_status');
            $table->foreignId('reimbursed_by')->nullable()->after('reimbursed_at')->constrained('users')->onDelete('set null');
            $table->decimal('paid_amount', 15, 2)->nullable()->after('reimbursed_by');
            $table->string('payment_method')->nullable()->after('paid_amount');
            $table->string('transaction_reference')->nullable()->after('payment_method');
        });

        Schema::create('reimbursement_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reimbursement_id')->constrained()->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->date('receipt_date');
            $table->timestamps();
        });

        Schema::create('approval_histories', function (Blueprint $table) {
            $table->id();
            $table->string('approvable_type');
            $table->unsignedBigInteger('approvable_id');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('role');
            $table->string('action'); // submitted, approved, rejected, reimbursed
            $table->text('comment')->nullable();
            $table->date('signed_date')->nullable();
            $table->timestamps();

            $table->index(['approvable_type', 'approvable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_histories');
        Schema::dropIfExists('reimbursement_attachments');

        Schema::table('reimbursements', function (Blueprint $table) {
            $table->dropForeign(['finance_id']);
            $table->dropForeign(['reimbursed_by']);
            $table->dropColumn([
                'finance_id',
                'finance_comment',
                'signed_date',
                'reimbursement_status',
                'reimbursed_at',
                'reimbursed_by',
                'paid_amount',
                'payment_method',
                'transaction_reference',
            ]);
        });

        Schema::table('travel_requests', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn([
                'manager_id',
                'manager_comment',
                'signed_date',
            ]);
        });
    }
};
