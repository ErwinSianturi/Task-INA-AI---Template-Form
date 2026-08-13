<?php

namespace Tests\Feature;

use App\Models\Reimbursement;
use App\Models\ReimbursementItem;
use App\Models\TravelRequest;
use App\Models\TravelRequestDestination;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_redirects_guest_users_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_employee_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
        ]);

        $response = $this->post('/login', [
            'email' => 'employee@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs(User::where('email', 'employee@example.com')->first());
    }

    public function test_travel_request_and_non_travel_crf_are_persisted_with_mysql_compatible_dates_and_null_relation(): void
    {
        $user = User::factory()->create([
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
        ]);

        $travelRequest = TravelRequest::create([
            'user_id' => $user->id,
            'request_number' => 'TR-2026-001',
            'category' => 'Technology',
            'date' => '2026-08-10',
            'company' => 'PT Test',
            'justification' => 'Training',
            'benefit' => 'Skill improvement',
            'status' => 'approved',
            'submitted_at' => now(),
        ]);

        TravelRequestDestination::create([
            'travel_request_id' => $travelRequest->id,
            'destination' => 'Singapore',
            'from' => '2026-08-15',
            'to' => '2026-08-18',
        ]);

        $reimbursement = Reimbursement::create([
            'user_id' => $user->id,
            'travel_request_id' => null,
            'request_number' => 'CRF-2026-001',
            'category' => 'Office Supplies',
            'date' => '2026-08-20',
            'company' => 'PT Test',
            'note' => 'Test reimbursement',
            'bank' => 'BCA',
            'account_number' => '123456',
            'transfer_to' => 'Demo User',
            'total' => 150000.00,
            'status' => 'draft',
            'reimbursement_type' => 'non_travel',
        ]);

        ReimbursementItem::create([
            'reimbursement_id' => $reimbursement->id,
            'date' => '2026-08-20',
            'details' => 'Printer ink',
            'amount' => 150000.00,
        ]);

        $this->assertDatabaseHas('travel_request_destinations', [
            'destination' => 'Singapore',
            'from' => '2026-08-15',
            'to' => '2026-08-18',
        ]);

        $this->assertDatabaseHas('reimbursements', [
            'request_number' => 'CRF-2026-001',
            'travel_request_id' => null,
            'reimbursement_type' => 'non_travel',
        ]);

        $this->assertDatabaseHas('reimbursement_items', [
            'reimbursement_id' => $reimbursement->id,
            'details' => 'Printer ink',
            'amount' => '150000.00',
        ]);

        $this->assertNull($reimbursement->fresh()->travel_request_id);
        $this->assertEquals(1, $reimbursement->items()->count());
    }
}
