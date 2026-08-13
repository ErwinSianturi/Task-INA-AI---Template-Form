<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\TravelRequest;
use App\Models\TravelRequestDestination;
use App\Models\Reimbursement;
use App\Models\ReimbursementItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Demo Users
        $employee = User::create([
            'name' => 'Demo Employee',
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
        ]);

        $manager = User::create([
            'name' => 'Pantro Pander',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
        ]);

        $finance = User::create([
            'name' => 'Tung Sen',
            'email' => 'finance@example.com',
            'password' => Hash::make('password'),
            'role' => 'finance',
        ]);

        $admin = User::create([
            'name' => 'Demo Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Create Travel Request 1 (Approved)
        $tr1 = TravelRequest::create([
            'user_id' => $employee->id,
            'request_number' => '001/WM-YBAR',
            'category' => 'Technology',
            'date' => '2024-11-20',
            'company' => 'PT Teknologi Cerdas Berdaulat Indonesia',
            'justification' => 'Attend international AI technology conference to represent the company.',
            'benefit' => 'Learn new integration frameworks, networking with vendors.',
            'supporting_label_1' => 'Invitation',
            'supporting_value_1' => true,
            'supporting_label_2' => 'Travel Invitation Letter',
            'supporting_value_2' => true,
            'supporting_label_3' => 'Hotel Confirmation',
            'supporting_value_3' => false,
            'supporting_label_4' => '',
            'supporting_value_4' => false,
            'status' => 'approved',
            'submitted_at' => '2024-11-21 09:00:00',
            'approved_at' => '2024-11-22 14:00:00',
        ]);

        TravelRequestDestination::create([
            'travel_request_id' => $tr1->id,
            'destination' => 'Singapore Exhibition Hall',
            'from' => '2024-11-23', // Clean dates
            'to' => '2024-11-28',   // Clean dates
        ]);

        // 3. Create Travel Request 2 (Pending Manager)
        $tr2 = TravelRequest::create([
            'user_id' => $employee->id,
            'request_number' => '002/WM-YBAR',
            'category' => 'Business Development',
            'date' => '2026-08-10',
            'company' => 'PT Teknologi Cerdas Berdaulat Indonesia',
            'justification' => 'Meet with client representatives to finalize software contract.',
            'benefit' => 'Secure a long-term partnership with major client.',
            'supporting_label_1' => 'Meeting Agenda',
            'supporting_value_1' => true,
            'supporting_label_2' => 'Client Invitation',
            'supporting_value_2' => false,
            'supporting_label_3' => '',
            'supporting_value_3' => false,
            'supporting_label_4' => '',
            'supporting_value_4' => false,
            'status' => 'pending_manager',
            'submitted_at' => now(),
        ]);

        TravelRequestDestination::create([
            'travel_request_id' => $tr2->id,
            'destination' => 'Surabaya Corporate Office',
            'from' => '2026-08-15',
            'to' => '2026-08-18',
        ]);

        // 4. Create Cash Reimbursement 1 (Pending Finance) - linked to TR 1 (Approved Travel)
        $crf1 = Reimbursement::create([
            'user_id' => $employee->id,
            'travel_request_id' => $tr1->id,
            'request_number' => $tr1->request_number,
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
            'submitted_at' => now(),
        ]);

        ReimbursementItem::create([
            'reimbursement_id' => $crf1->id,
            'date' => '2024-11-11',
            'details' => 'AWS services period 1 June - 30 Nov',
            'amount' => 400000.00,
        ]);

        // 5. Create Cash Reimbursement 2 (Draft Non-Travel)
        $crf2 = Reimbursement::create([
            'user_id' => $employee->id,
            'travel_request_id' => null,
            'request_number' => '004/WM-YBAR',
            'reimbursement_type' => 'non_travel',
            'category' => 'Office Supplies',
            'date' => '2026-08-12',
            'company' => 'PT Teknologi Cerdas Berdaulat Indonesia',
            'note' => 'Replacement of office printer ink cartridge.',
            'bank' => 'Bank Central Asia (BCA)',
            'account_number' => '8012345678',
            'transfer_to' => 'Demo Employee',
            'total' => 150000.00,
            'status' => 'draft',
        ]);

        ReimbursementItem::create([
            'reimbursement_id' => $crf2->id,
            'date' => '2026-08-11',
            'details' => 'Printer Ink cartridge',
            'amount' => 150000.00,
        ]);
    }
}
