<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_page_shows_own_patients_medicine_and_recent_adherence(): void
    {
        $user = User::factory()->create();
        $patient = $user->patients()->create(['name' => '自分の患者']);
        $medicine = Medicine::create([
            'patient_id' => $patient->id,
            'medicine_name' => '自分の薬',
            'dosage' => '1錠',
        ]);
        $schedule = $medicine->schedules()->create(['scheduled_time' => '08:00']);
        $schedule->adherences()->create([
            'medicine_id' => $medicine->id,
            'taken_date' => now()->toDateString(),
            'taken_time' => now()->format('H:i:s'),
        ]);

        $response = $this->actingAs($user)->get('/reports');

        $response->assertOk();
        $response->assertSee('自分の患者');
        $response->assertSee('自分の薬');
    }

    public function test_report_page_does_not_show_other_users_patients(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $patient = $owner->patients()->create(['name' => '他人の患者']);
        Medicine::create([
            'patient_id' => $patient->id,
            'medicine_name' => '他人の薬',
            'dosage' => '1錠',
        ]);

        $response = $this->actingAs($viewer)->get('/reports');

        $response->assertOk();
        $response->assertDontSee('他人の患者');
        $response->assertDontSee('他人の薬');
    }
}
