<?php

namespace Database\Seeders;

use App\Models\ApprovalHistory;
use App\Models\Reimbursement;
use App\Models\ReimbursementAttachment;
use App\Models\ReimbursementItem;
use App\Models\TravelRequest;
use App\Models\TravelRequestDestination;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Base Demo Users
        $employee = User::firstOrCreate(['email' => 'employee@example.com'], [
            'name' => 'Demo Employee',
            'password' => Hash::make('password'),
            'role' => 'employee',
        ]);

        $manager = User::firstOrCreate(['email' => 'manager@example.com'], [
            'name' => 'Account Manager',
            'password' => Hash::make('password'),
            'role' => 'manager',
        ]);

        $finance = User::firstOrCreate(['email' => 'finance@example.com'], [
            'name' => 'Account Finance',
            'password' => Hash::make('password'),
            'role' => 'finance',
        ]);

        $admin = User::firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Demo Admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Create Pengawas Accounts
        $pantro = User::firstOrCreate(['email' => 'pantro@example.com'], [
            'name' => 'Pantro Pander',
            'password' => Hash::make('password'),
            'role' => 'pengawas',
            'signature_path' => 'signatures/pantro_pander.svg',
        ]);

        $tungsen = User::firstOrCreate(['email' => 'tungsen@example.com'], [
            'name' => 'Tung Sen',
            'password' => Hash::make('password'),
            'role' => 'pengawas',
            'signature_path' => 'signatures/tung_sen.svg',
        ]);

        $billy = User::firstOrCreate(['email' => 'billy@example.com'], [
            'name' => 'Billy Gunawan',
            'password' => Hash::make('password'),
            'role' => 'pengawas',
            'signature_path' => 'signatures/billy_gunawan.svg',
        ]);

        $apriliansyah = User::firstOrCreate(['email' => 'apriliansyah@example.com'], [
            'name' => 'Apriliansyah',
            'password' => Hash::make('password'),
            'role' => 'pengawas',
            'signature_path' => 'signatures/apriliansyah.svg',
        ]);

        // 3. Create Sample Travel Request (Technology Category -> Fully Approved by 3 parties)
        $now = now();
        $today = $now->toDateString();

        $tr1 = TravelRequest::firstOrCreate(['request_number' => '001/WM-YBAR'], [
            'user_id' => $employee->id,
            'category' => 'Technology',
            'date' => '2024-11-20',
            'company' => 'PT Teknologi Cerdas Berdaulat Indonesia',
            'justification' => 'Attend international AI technology conference to represent the company.',
            'benefit' => 'Learn new integration frameworks, networking with vendors.',
            'supporting_label_1' => 'Invitation',
            'supporting_value_1' => true,
            'supporting_label_2' => 'Travel Invitation Letter',
            'supporting_value_2' => true,
            'status' => 'approved',
            'category_approver_id' => $billy->id,
            'category_approved_at' => $now,
            'manager_id' => $manager->id,
            'manager_approved_at' => $now,
            'pantro_id' => $pantro->id,
            'pantro_approved_at' => $now,
            'approved_by_user_id' => $billy->id,
            'submitted_at' => '2024-11-21 09:00:00',
            'approved_at' => '2024-11-22 14:00:00',
            'signed_date' => '2024-11-22',
        ]);

        if (TravelRequestDestination::where('travel_request_id', $tr1->id)->count() === 0) {
            TravelRequestDestination::create([
                'travel_request_id' => $tr1->id,
                'destination' => 'Singapore Exhibition Hall',
                'from' => '2024-11-23',
                'to' => '2024-11-28',
            ]);
        }

        // 4. Create Sample Travel Request 2 (Commercial Category -> Pending Multi-Role Approval)
        $tr2 = TravelRequest::firstOrCreate(['request_number' => '002/WM-YBAR'], [
            'user_id' => $employee->id,
            'category' => 'Commercial',
            'date' => '2026-08-10',
            'company' => 'PT Teknologi Cerdas Berdaulat Indonesia',
            'justification' => 'Meet with client representatives to finalize software contract.',
            'benefit' => 'Secure a long-term partnership with major client.',
            'supporting_label_1' => 'Meeting Agenda',
            'supporting_value_1' => true,
            'status' => 'pending_manager',
            'submitted_at' => now(),
        ]);

        if (TravelRequestDestination::where('travel_request_id', $tr2->id)->count() === 0) {
            TravelRequestDestination::create([
                'travel_request_id' => $tr2->id,
                'destination' => 'Surabaya Corporate Office',
                'from' => '2026-08-15',
                'to' => '2026-08-18',
            ]);
        }

        // 5. Create Cash Reimbursement 1 (Pending 5-Role Approval)
        $crf1 = Reimbursement::firstOrCreate(['request_number' => $tr1->request_number], [
            'user_id' => $employee->id,
            'travel_request_id' => $tr1->id,
            'reimbursement_type' => 'travel',
            'category' => $tr1->category,
            'date' => '2024-11-26',
            'company' => $tr1->company,
            'note' => 'Cloud server subscription and travel ticket reimbursement details.',
            'bank' => 'Bank Central Asia (BCA)',
            'account_number' => '8012345678',
            'transfer_to' => 'Demo Employee',
            'total' => 400000.00,
            'status' => 'pending_finance',
            'reimbursement_status' => 'not_reimbursed',
            'submitted_at' => now(),
        ]);

        if (ReimbursementItem::where('reimbursement_id', $crf1->id)->count() === 0) {
            ReimbursementItem::create([
                'reimbursement_id' => $crf1->id,
                'date' => '2024-11-11',
                'details' => 'AWS services period 1 June - 30 Nov',
                'amount' => 400000.00,
            ]);
        }

        if (ReimbursementAttachment::where('reimbursement_id', $crf1->id)->count() === 0) {
            ReimbursementAttachment::create([
                'reimbursement_id' => $crf1->id,
                'file_path' => 'receipts/sample_receipt.pdf',
                'original_name' => 'AWS_Invoice_Nov2024.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 124500,
                'receipt_date' => '2024-11-11',
            ]);
        }
    }
}
