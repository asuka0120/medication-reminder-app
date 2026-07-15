<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_fails_when_no_valid_time_is_selected(): void
    {
        $user = User::factory()->create();
        $patient = Patient::create(['user_id' => $user->id, 'name' => '患者A']);

        $response = $this->actingAs($user)->post('/medicines', [
            'patient_id' => $patient->id,
            'medicine_name' => '薬A',
            'dosage_select' => '1錠',
            'times' => ['', ''], // 空欄のみ
        ]);

        $response->assertSessionHas('error_message');
        $this->assertDatabaseCount('medicines', 0);
    }

    public function test_store_creates_one_medicine_per_selected_time(): void
    {
        $user = User::factory()->create();
        $patient = Patient::create(['user_id' => $user->id, 'name' => '患者A']);

        $response = $this->actingAs($user)->post('/medicines', [
            'patient_id' => $patient->id,
            'medicine_name' => '薬A',
            'dosage_select' => '1錠',
            'times' => ['08:00', '20:00'],
        ]);

        $response->assertRedirect('/patients');
        $this->assertDatabaseCount('medicines', 1);
        $this->assertDatabaseCount('medicine_schedules', 2);
    }

    public function test_update_fails_when_no_valid_time_is_selected(): void
    {
        $user = User::factory()->create();
        $patient = Patient::create(['user_id' => $user->id, 'name' => '患者A']);
        $medicine = Medicine::create([
            'patient_id' => $patient->id,
            'medicine_name' => '薬A',
            'dosage' => '1錠',
        ]);
        $medicine->schedules()->create(['scheduled_time' => '08:00']);

        $response = $this->actingAs($user)->put("/medicines/{$medicine->id}", [
            'medicine_name' => '薬A',
            'dosage_select' => '1錠',
            'timings' => [''],
        ]);

        $response->assertSessionHas('error_message');
    }
}
