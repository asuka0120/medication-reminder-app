<?php

namespace App\Console;

use App\Models\MedicineSchedule;
use App\Models\User;
use App\Notifications\MedicationReminder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 1分ごとに、今飲むべき薬があるかチェックする
        $schedule->call(function () {
            $now = now()->format('H:i');
            $schedules = MedicineSchedule::with('medicine.patient')
                ->where('scheduled_time', $now.':00')
                ->get();

            foreach ($schedules as $medicineSchedule) {
                $medicine = $medicineSchedule->medicine;

                // 管理者（あなた）に通知を送る
                $user = User::first();
                if ($user) {
                    $user->notify(new MedicationReminder(
                        'お薬の時間です',
                        "{$medicine->patient->name}さんの「{$medicine->medicine_name}」の時間になりました。"
                    ));
                }
            }
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
