<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\MedicineController;
use App\Notifications\MedicationReminder;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

// --- 通知関連 ---
// ブラウザからの「通知許可」情報を保存する
Route::post('/notifications/subscribe', function (Request $request) {
    // 最初のユーザーを取得、いなければ作成（テスト用）
    $user = \App\Models\User::first() ?? \App\Models\User::create([
        'name' => 'Admin', 
        'email' => 'admin@example.com', 
        'password' => bcrypt('password')
    ]);
    
    // 宛先情報を保存
    $user->updatePushSubscription(
        $request->endpoint,
        $request->keys['p256dh'],
        $request->keys['auth']
    );

    return response()->json(['success' => true]);
});

// テスト通知を飛ばす
Route::get('/test-notification', function () {
    $user = \App\Models\User::first();
    if ($user) {
        $user->notify(new MedicationReminder("テスト通知", "お薬の時間ですよ！"));
        return "ブラウザに通知を送りました！確認してください。";
    }
    return "ユーザーが見つかりません。先に通知ボタンを押してください。";
});

// --- メイン機能 ---
Route::resource('patients', PatientController::class);

Route::get('/medicines/create', [MedicineController::class, 'create'])->name('medicines.create');
Route::post('/medicines', [MedicineController::class, 'store'])->name('medicines.store');
Route::post('/adherences', [MedicineController::class, 'take'])->name('adherences.store');
Route::post('/adherences/cancel', [MedicineController::class, 'cancel'])->name('adherences.cancel');
Route::delete('/medicines/{id}', [MedicineController::class, 'destroy'])->name('medicines.destroy');
// ゴミ箱ページを表示する
Route::get('/medicines/trash', [MedicineController::class, 'trash'])->name('medicines.trash');

// お薬を元に戻す（復元）
Route::patch('/medicines/{id}/restore', [MedicineController::class, 'restore'])->name('medicines.restore');

// 完全に削除する
Route::delete('/medicines/{id}/force-delete', [MedicineController::class, 'forceDelete'])->name('medicines.forceDelete');
// ★ 月間カレンダー用のルート（ボタンで使う名前を固定します）
Route::get('/patients/{patient}', [PatientController::class, 'show'])->name('patients.show');
// 編集画面を表示する
Route::get('medicines/{medicine}/edit', [MedicineController::class, 'edit'])->name('medicines.edit');
// 更新内容を保存する
Route::put('medicines/{medicine}', [MedicineController::class, 'update'])->name('medicines.update');