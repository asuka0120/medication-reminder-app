<?php

use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TrashController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'nocache'])->name('dashboard');

// ログインしているユーザーだけアクセスできるルート
Route::middleware(['auth', 'nocache'])->group(function () {

    // プロフィール
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 患者（家族）管理
    Route::resource('patients', PatientController::class);

    // 薬管理
    // MedicineControllerにはindex()・show()が存在しないため、
    // 使わないアクションはexcept()で明示的に除外する
    Route::resource('medicines', MedicineController::class)->except(['index', 'show']);

    // 服薬記録：どのスケジュール（何時の分）を対象にするかをURLに含める
    Route::post('/medicine-schedules/{schedule}/take', [MedicineController::class, 'take'])->name('medicine-schedules.take');
    Route::post('/medicine-schedules/{schedule}/cancel', [MedicineController::class, 'cancel'])->name('medicine-schedules.cancel');

    // ゴミ箱
    Route::get('/trash', [TrashController::class, 'index'])->name('trash.index');
    Route::post('/trash/{id}/restore', [TrashController::class, 'restore'])->name('trash.restore');
    Route::delete('/trash/{id}/force-delete', [TrashController::class, 'forceDelete'])->name('trash.forceDelete');

    // 週間服薬レポート
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});

require __DIR__.'/auth.php';
