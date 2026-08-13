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
        Schema::create('reimbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('travel_request_id')->nullable()->constrained()->onDelete('set null');
            $table->string('request_number');
            $table->string('category');
            $table->date('date');
            $table->string('company');
            $table->text('note')->nullable();
            $table->string('bank');
            $table->string('account_number');
            $table->string('transfer_to');
            $table->decimal('total', 15, 2)->default(0.00);
            $table->string('status')->default('draft'); // draft, pending_finance, verified, rejected
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reimbursements');
    }
};
