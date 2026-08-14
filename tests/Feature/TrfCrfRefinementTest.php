<?php

namespace Tests\Feature;

use App\Models\Reimbursement;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrfCrfRefinementTest extends TestCase
{
    use RefreshDatabase;

    protected User $employee;
    protected User $manager;
    protected User $finance;
    protected User $pantro;
    protected User $tungsen;
    protected User $billy;
    protected User $apriliansyah;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employee = User::factory()->create(['role' => 'employee']);
        $this->manager = User::factory()->create(['role' => 'manager', 'name' => 'Account Manager', 'email' => 'manager@example.com']);
        $this->finance = User::factory()->create(['role' => 'finance', 'name' => 'Account Finance', 'email' => 'finance@example.com']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        // Testing accounts for 'pengawas'
        $this->pantro = User::factory()->create([
            'name' => 'Pantro Pander',
            'email' => 'pantro@example.com',
            'role' => 'pengawas',
            'password' => Hash::make('password'),
        ]);

        $this->tungsen = User::factory()->create([
            'name' => 'Tung Sen',
            'email' => 'tungsen@example.com',
            'role' => 'pengawas',
            'password' => Hash::make('password'),
        ]);

        $this->billy = User::factory()->create([
            'name' => 'Billy Gunawan',
            'email' => 'billy@example.com',
            'role' => 'pengawas',
            'password' => Hash::make('password'),
        ]);

        $this->apriliansyah = User::factory()->create([
            'name' => 'Apriliansyah',
            'email' => 'apriliansyah@example.com',
            'role' => 'pengawas',
            'password' => Hash::make('password'),
        ]);
    }

    public function test_crf_5_role_multi_approval_workflow()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');

        $crf = Reimbursement::create([
            'user_id' => $this->employee->id,
            'request_number' => 'CRF-MULTI-001',
            'reimbursement_type' => 'non_travel',
            'category' => 'Technology',
            'date' => '2026-08-14',
            'company' => 'PT Teknologi Cerdas Berdaulat Indonesia',
            'bank' => 'Bank Central Asia (BCA)',
            'account_number' => '1234567890',
            'transfer_to' => 'Demo Employee',
            'total' => 300000,
            'status' => 'pending_finance',
            'reimbursement_status' => 'not_reimbursed',
        ]);

        // 1. Billy Gunawan (Category Approver for Tech) approves
        $this->actingAs($this->billy)->post("/reimbursements/{$crf->id}/verify")->assertStatus(302);
        $crf->refresh();
        $this->assertEquals('pending_finance', $crf->status); // Not yet fully approved

        // 2. Manager approves
        $this->actingAs($this->manager)->post("/reimbursements/{$crf->id}/verify")->assertStatus(302);
        $crf->refresh();
        $this->assertEquals('pending_finance', $crf->status);

        // 3. Finance approves
        $this->actingAs($this->finance)->post("/reimbursements/{$crf->id}/verify")->assertStatus(302);
        $crf->refresh();
        $this->assertEquals('pending_finance', $crf->status);

        // 4. Pantro Pander approves
        $this->actingAs($this->pantro)->post("/reimbursements/{$crf->id}/verify")->assertStatus(302);
        $crf->refresh();
        $this->assertEquals('pending_finance', $crf->status);

        // 5. Tung Sen approves -> NOW ALL 5 COMPLETED -> status becomes APPROVED!
        $this->actingAs($this->tungsen)->post("/reimbursements/{$crf->id}/verify")->assertStatus(302);
        $crf->refresh();
        $this->assertEquals('approved', $crf->status);
        $this->assertNotNull($crf->verified_at);
        $this->assertNotNull($crf->signed_date);
    }

    public function test_trf_3_role_multi_approval_workflow()
    {
        $tr = TravelRequest::create([
            'user_id' => $this->employee->id,
            'request_number' => 'TRF-MULTI-001',
            'category' => 'Commercial',
            'date' => '2026-08-14',
            'company' => 'PT Teknologi Cerdas Berdaulat Indonesia',
            'justification' => 'Multi role test',
            'benefit' => 'Multi role test',
            'status' => 'pending_manager',
            'submitted_at' => now(),
        ]);

        // 1. Apriliansyah (Category Approver for Commercial) approves
        $this->actingAs($this->apriliansyah)->post("/travel-requests/{$tr->id}/approve")->assertStatus(302);
        $tr->refresh();
        $this->assertEquals('pending_manager', $tr->status);

        // 2. Manager approves
        $this->actingAs($this->manager)->post("/travel-requests/{$tr->id}/approve")->assertStatus(302);
        $tr->refresh();
        $this->assertEquals('pending_manager', $tr->status);

        // 3. Pantro Pander approves -> NOW ALL 3 COMPLETED -> status becomes APPROVED!
        $this->actingAs($this->pantro)->post("/travel-requests/{$tr->id}/approve")->assertStatus(302);
        $tr->refresh();
        $this->assertEquals('approved', $tr->status);
        $this->assertNotNull($tr->approved_at);
        $this->assertNotNull($tr->signed_date);
    }

    public function test_attachment_view_accessibility_for_all_approvers()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('invoice_receipt.png', 200, 'image/png');

        $this->actingAs($this->employee)->post('/reimbursements', [
            'reimbursement_type' => 'non_travel',
            'category' => 'Technology',
            'date' => '2026-08-14',
            'bank' => 'Bank Central Asia (BCA)',
            'account_number' => '1234567890',
            'transfer_to' => 'Demo Employee',
            'items' => [['date' => '2026-08-14', 'details' => 'Software License', 'amount' => 100000]],
            'attachments' => [$file],
            'action' => 'submit',
        ]);

        $crf = Reimbursement::first();
        $this->assertNotNull($crf);
        $this->assertEquals(1, $crf->attachments->count());

        // Approver accounts (Billy, Apriliansyah, Manager, Finance, Pantro, Tung Sen) can access show view to view image
        $this->actingAs($this->billy)->get("/reimbursements/{$crf->id}")->assertStatus(200);
        $this->actingAs($this->manager)->get("/reimbursements/{$crf->id}")->assertStatus(200);
        $this->actingAs($this->finance)->get("/reimbursements/{$crf->id}")->assertStatus(200);
        $this->actingAs($this->pantro)->get("/reimbursements/{$crf->id}")->assertStatus(200);
        $this->actingAs($this->tungsen)->get("/reimbursements/{$crf->id}")->assertStatus(200);
    }
}
