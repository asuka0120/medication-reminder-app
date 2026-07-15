<?php

namespace Tests\Feature;

use App\Models\Adherence;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_stranger_cannot_view_patient_calendar(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $patient = Patient::create(['user_id' => $owner->id, 'name' => '患者A']);

        $response = $this->actingAs($stranger)->get("/patients/{$patient->id}");

        $response->assertStatus(403);
    }

    public function test_stranger_cannot_view_patient_edit_page(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $patient = Patient::create(['user_id' => $owner->id, 'name' => '患者A']);

        $response = $this->actingAs($stranger)->get("/patients/{$patient->id}/edit");

        $response->assertStatus(403);
    }

    public function test_stranger_cannot_delete_patient(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $patient = Patient::create(['user_id' => $owner->id, 'name' => '患者A']);

        $response = $this->actingAs($stranger)->delete("/patients/{$patient->id}");

        $response->assertStatus(403);
    }

    public function test_stranger_cannot_open_medicine_create_page_for_others_patient(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $patient = Patient::create(['user_id' => $owner->id, 'name' => '患者A']);

        $response = $this->actingAs($stranger)->get('/medicines/create?patient_id='.$patient->id);

        $response->assertStatus(403);
    }

    public function test_stranger_cannot_edit_others_medicine(): void
    {
        [$stranger, $medicine] = $this->createMedicineOwnedByAnotherUser();

        $response = $this->actingAs($stranger)->get("/medicines/{$medicine->id}/edit");

        $response->assertStatus(403);
    }

    public function test_stranger_cannot_take_others_medicine(): void
    {
        [$stranger, $medicine, $schedule] = $this->createMedicineOwnedByAnotherUser();

        $response = $this->actingAs($stranger)->post('/medicines/take', [
            'schedule_id' => $schedule->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_stranger_cannot_cancel_others_medicine(): void
    {
        [$stranger, $medicine, $schedule] = $this->createMedicineOwnedByAnotherUser();
        Adherence::create([
            'medicine_id' => $medicine->id,
            'medicine_schedule_id' => $schedule->id,
            'taken_date' => now()->toDateString(),
            'taken_time' => now()->format('H:i:s'),
        ]);

        $response = $this->actingAs($stranger)->post('/medicines/cancel', [
            'schedule_id' => $schedule->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_stranger_cannot_delete_others_medicine(): void
    {
        [$stranger, $medicine] = $this->createMedicineOwnedByAnotherUser();

        $response = $this->actingAs($stranger)->delete("/medicines/{$medicine->id}");

        $response->assertStatus(403);
    }

    /**
     * 他人の患者・お薬データを1件作成し、[部外者ユーザー, お薬, スケジュール] を返す共通ヘルパー
     */
    private function createMedicineOwnedByAnotherUser(): array
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $patient = Patient::create(['user_id' => $owner->id, 'name' => '患者A']);
        $medicine = Medicine::create([
            'patient_id' => $patient->id,
            'medicine_name' => '薬A',
            'dosage' => '1錠',
        ]);
        $schedule = $medicine->schedules()->create(['scheduled_time' => '08:00']);

        return [$stranger, $medicine, $schedule];
    }
}
