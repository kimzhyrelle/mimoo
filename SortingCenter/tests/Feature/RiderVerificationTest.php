<?php

namespace Tests\Feature;

use App\Models\Rider;
use App\Models\RiderApplication;
use App\Models\User;
use App\Services\SmsSender;
use Database\Seeders\SortingCenterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RiderVerificationTest extends TestCase
{
    use RefreshDatabase;

    private object $sms;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SortingCenterSeeder::class);

        $this->sms = new class implements SmsSender
        {
            /** @var array<int, array{phone: string, message: string}> */
            public array $messages = [];

            public function send(string $phone, string $message): void
            {
                $this->messages[] = ['phone' => $phone, 'message' => $message];
            }
        };

        $this->app->instance(SmsSender::class, $this->sms);
    }

    public function test_riders_page_renders_the_kyc_verification_controls(): void
    {
        $this->get(route('riders.index'))
            ->assertOk()
            ->assertSee('Verification Checklist')
            ->assertSee('Send code')
            ->assertSee('Verify code')
            ->assertSee('Approve Application');
    }

    public function test_rider_cannot_be_approved_without_complete_verification(): void
    {
        $application = RiderApplication::where('name', 'K. Torres')->firstOrFail();
        $riderCount = Rider::count();

        $response = $this->postJson(route('riders.applications.approve', $application));

        $response->assertStatus(422)->assertJson(['ok' => false]);
        $this->assertSame($riderCount, Rider::count());
        $this->assertDatabaseHas('rider_applications', [
            'id' => $application->id,
            'status' => 'pending',
        ]);
    }

    public function test_checklist_and_otp_verification_unlock_approval(): void
    {
        $application = RiderApplication::where('name', 'R. Manalo')->firstOrFail();

        $this->postJson(route('riders.applications.verification', $application), [
            'id_checked' => true,
            'license_checked' => true,
            'or_cr_expires_on' => now()->addYear()->toDateString(),
            'license_expires_on' => now()->addYear()->toDateString(),
        ])->assertOk()->assertJson([
            'ok' => true,
            'can_approve' => false,
            'kyc_status' => 'under_review',
        ]);

        $this->postJson(route('riders.applications.otp.send', $application), [
            'phone' => '0917 555 1234',
        ])->assertOk()->assertJson(['ok' => true]);

        $application->refresh();
        $this->assertNotNull($application->otp_hash);
        $this->assertNotNull($application->otp_expires_at);

        $message = end($this->sms->messages)['message'];
        preg_match('/\b(\d{6})\b/', $message, $matches);
        $code = $matches[1] ?? '';
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
        $this->assertNotSame($code, $application->otp_hash);
        $this->assertTrue(Hash::check($code, $application->otp_hash));

        $this->postJson(route('riders.applications.otp.verify', $application), [
            'code' => $code,
        ])->assertOk()->assertJson([
            'ok' => true,
            'can_approve' => true,
            'kyc_status' => 'verified',
        ]);

        $response = $this->postJson(route('riders.applications.approve', $application));

        $response->assertOk()->assertJson(['ok' => true]);
        $this->assertDatabaseHas('rider_applications', [
            'id' => $application->id,
            'status' => 'approved',
        ]);
        $rider = Rider::where('name', 'R. Manalo')->firstOrFail();
        $this->assertSame('verified', $rider->kyc_status);
        $this->assertDatabaseHas('rider_verification_audits', [
            'rider_application_id' => $application->id,
            'rider_id' => $rider->id,
            'action' => 'approved',
        ]);
    }

    public function test_sms_verification_does_not_automatically_verify_documents(): void
    {
        $application = RiderApplication::where('name', 'R. Manalo')->firstOrFail();

        $this->postJson(route('riders.applications.otp.send', $application), [
            'phone' => $application->phone,
        ])->assertOk();

        $message = end($this->sms->messages)['message'];
        preg_match('/\b(\d{6})\b/', $message, $matches);
        $code = $matches[1] ?? '';

        $this->postJson(route('riders.applications.otp.verify', $application), [
            'code' => $code,
        ])->assertOk()->assertJsonPath('checklist.phone_verified', true)
            ->assertJsonPath('checklist.id_checked', false)
            ->assertJsonPath('checklist.license_checked', false)
            ->assertJsonPath('checklist.docs_not_expired', false)
            ->assertJsonPath('can_approve', false);
    }

    public function test_otp_expires_after_ten_minutes(): void
    {
        $application = RiderApplication::where('name', 'R. Manalo')->firstOrFail();

        $application->update([
            'otp_hash' => Hash::make('123456'),
            'otp_expires_at' => now()->addMinutes(9),
            'otp_attempts' => 0,
        ]);

        $this->travel(10)->minutes();

        $this->postJson(route('riders.applications.otp.verify', $application), [
            'code' => '123456',
        ])->assertStatus(422)->assertJson(['ok' => false]);
    }

    public function test_disapproval_requires_fixed_reason_and_is_exposed_by_api(): void
    {
        $application = RiderApplication::where('name', 'K. Torres')->firstOrFail();

        $this->postJson(route('riders.applications.disapprove', $application), [
            'reason' => 'Not on the list',
        ])->assertStatus(422);

        $this->postJson(route('riders.applications.disapprove', $application), [
            'reason' => 'Other',
            'reason_detail' => 'License photo is unreadable',
        ])->assertOk()->assertJson(['ok' => true]);

        $application->refresh();
        $this->assertSame('rejected', $application->status);
        $this->assertSame('Other: License photo is unreadable', $application->rejection_reason);
        $this->assertDatabaseHas('rider_verification_audits', [
            'rider_application_id' => $application->id,
            'action' => 'rejected',
            'reason' => 'Other: License photo is unreadable',
        ]);

        Sanctum::actingAs(User::firstOrFail());

        $this->getJson('/api/rider-applications/'.$application->id)
            ->assertOk()
            ->assertJsonPath('data.rejection_reason', 'Other: License photo is unreadable')
            ->assertJsonPath('data.kyc_status', 'rejected');
    }
}
