<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormRequestVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_own_patient_can_be_updated(): void
    {
        $user = User::factory()->create();
        $patient = Patient::create(['user_id' => $user->id, 'name' => '元の名前']);

        $response = $this->actingAs($user)->put("/patients/{$patient->id}", [
            'name' => '更新後の名前',
        ]);

        $response->assertRedirect('/patients');
        $this->assertEquals('更新後の名前', $patient->fresh()->name);
    }

    public function test_other_users_patient_cannot_be_updated(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $patient = Patient::create(['user_id' => $owner->id, 'name' => '元の名前']);

        $response = $this->actingAs($stranger)->put("/patients/{$patient->id}", [
            'name' => '不正な更新',
        ]);

        $response->assertStatus(403);
        $this->assertEquals('元の名前', $patient->fresh()->name);
    }

    public function test_patient_update_requires_name(): void
    {
        $user = User::factory()->create();
        $patient = Patient::create(['user_id' => $user->id, 'name' => '元の名前']);

        $response = $this->actingAs($user)->put("/patients/{$patient->id}", [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_own_medicine_can_be_updated(): void
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
            'medicine_name' => '薬A更新',
            'timings' => ['09:00'],
            'dosage_select' => '1錠',
        ]);

        $response->assertRedirect('/patients');
    }

    public function test_other_users_medicine_cannot_be_updated(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $patient = Patient::create(['user_id' => $owner->id, 'name' => '患者A']);
        $medicine = Medicine::create([
            'patient_id' => $patient->id,
            'medicine_name' => '薬A',
            'dosage' => '1錠',
        ]);
        $medicine->schedules()->create(['scheduled_time' => '08:00']);

        $response = $this->actingAs($stranger)->put("/medicines/{$medicine->id}", [
            'medicine_name' => '不正な更新',
            'timings' => ['09:00'],
            'dosage_select' => '1錠',
        ]);

        $response->assertStatus(403);
    }
}
