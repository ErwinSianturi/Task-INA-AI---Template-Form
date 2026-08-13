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
            $table->string('supporting_label_1')->nullable();
            $table->string('supporting_label_2')->nullable();
            $table->string('supporting_label_3')->nullable();
            $table->string('supporting_label_4')->nullable();
            $table->boolean('supporting_value_1')->default(false);
            $table->boolean('supporting_value_2')->default(false);
            $table->boolean('supporting_value_3')->default(false);
            $table->boolean('supporting_value_4')->default(false);
        });

        Schema::table('reimbursements', function (Blueprint $table) {
            $table->string('reimbursement_type')->default('travel'); // travel or non_travel
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_requests', function (Blueprint $table) {
            $table->dropColumn([
                'supporting_label_1',
                'supporting_label_2',
                'supporting_label_3',
                'supporting_label_4',
                'supporting_value_1',
                'supporting_value_2',
                'supporting_value_3',
                'supporting_value_4',
            ]);
        });

        Schema::table('reimbursements', function (Blueprint $table) {
            $table->dropColumn('reimbursement_type');
        });
    }
};
