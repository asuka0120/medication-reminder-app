<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineScheduleDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_medicine_can_have_multiple_schedules(): void
    {
        $user = User::factory()->create();
        $patient = Patient::create(['user_id' => $user->id, 'name' => '患者A']);
        $medicine = Medicine::create([
            'patient_id' => $patient->id,
            'medicine_name' => '薬A',
            'dosage' => '1錠',
        ]);
        $medicine->schedules()->create(['scheduled_time' => '08:00']);
        $medicine->schedules()->create(['scheduled_time' => '20:00']);

        $this->assertCount(2, $medicine->fresh()->schedules);
    }

    public function test_take_and_cancel_work_per_schedule(): void
    {
        $user = User::factory()->create();
        $patient = Patient::create(['user_id' => $user->id, 'name' => '患者A']);
        $medicine = Medicine::create([
            'patient_id' => $patient->id,
            'medicine_name' => '薬A',
            'dosage' => '1錠',
        ]);
        $morning = $medicine->schedules()->create(['scheduled_time' => '08:00']);
        $evening = $medicine->schedules()->create(['scheduled_time' => '20:00']);

        // 朝の分だけ「飲んだ」を記録する
        $this->actingAs($user)->post('/medicines/take', ['schedule_id' => $morning->id]);

        $this->assertCount(1, $morning->fresh()->adherences);
        $this->assertCount(0, $evening->fresh()->adherences);

        // 朝の分を取り消す
        $this->actingAs($user)->post('/medicines/cancel', ['schedule_id' => $morning->id]);

        $this->assertCount(0, $morning->fresh()->adherences);
    }

    public function test_deleting_medicine_moves_it_to_trash_and_can_be_restored(): void
    {
        $user = User::factory()->create();
        $patient = Patient::create(['user_id' => $user->id, 'name' => '患者A']);
        $medicine = Medicine::create([
            'patient_id' => $patient->id,
            'medicine_name' => '薬A',
            'dosage' => '1錠',
        ]);
        $medicine->schedules()->create(['scheduled_time' => '08:00']);

        // 削除（ゴミ箱へ）
        $this->actingAs($user)->delete("/medicines/{$medicine->id}");
        $this->assertSoftDeleted('medicines', ['id' => $medicine->id]);

        // 復元
        $this->actingAs($user)->post("/trash/{$medicine->id}/restore");
        $this->assertDatabaseHas('medicines', ['id' => $medicine->id, 'deleted_at' => null]);

        // 復元後もスケジュールがそのまま残っていること
        $this->assertCount(1, $medicine->fresh()->schedules);
    }
}
