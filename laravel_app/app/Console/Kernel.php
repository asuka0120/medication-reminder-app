<?php

namespace App\Console;

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
            $medicines = \App\Models\Medicine::where('scheduled_time', $now.':00')->get();

            foreach ($medicines as $medicine) {
                // 管理者（あなた）に通知を送る
                $user = \App\Models\User::first();
                if ($user) {
                    $user->notify(new \App\Notifications\MedicationReminder(
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
