<?php

use App\Http\Controllers\Auth\ChildAuthController;
use App\Http\Controllers\Child\ChildChoreHistoryController;
use App\Http\Controllers\Child\ChildHomeController;
use App\Http\Controllers\Child\ChildPaymentHistoryController;
use App\Http\Controllers\Child\ChildPaymentRecordController;
use App\Http\Controllers\Child\ChildProfileController;
use App\Http\Controllers\Child\ChildSavingController;
use App\Http\Controllers\Parent\ChildPaymentHistoryController as ParentChildPaymentHistoryController;
use App\Http\Controllers\Parent\ParentChildController;
use App\Http\Controllers\Parent\ParentChoreHistoryController;
use App\Http\Controllers\Parent\ParentChorePerformanceController;
use App\Http\Controllers\Parent\ParentChoreSettingController;
use App\Http\Controllers\Parent\ParentFamilyAccountController;
use App\Http\Controllers\Parent\ParentPocketMoneyController;
use App\Http\Controllers\Parent\ParentProfileController;
use App\Http\Controllers\Parent\ParentSavingController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ParentRegistrationController;
use App\Http\Controllers\Auth\ParentRegistrationVerificationController;

// 保護者新規登録の２要素認証
Route::middleware('guest')->group(function () {
    // Route::get('/parent/register',
    //     [ParentRegistrationController::class, 'create']
    // )->name('parent.register');

    // 保護者新規登録画面
    Route::get('/parent/register', function () {
        return view('auth.parent-register');
    })->name('parent.register');

    // 保護者新規登録機能の処理
    Route::post('/parent/register',
        [ParentRegistrationController::class, 'store']
    )->name('parent.register.store');

    // 確認コード入力画面
    Route::get('/parent/register/verify/{token}',
        [ParentRegistrationVerificationController::class, 'create']
    )->name('parent.register.verify');

    // 確認コード機能の処理
    Route::post('/parent/register/verify/{token}',
        [ParentRegistrationVerificationController::class, 'store']
    )->name('parent.register.verify.store');
});

// '/'にアクセスしたら自動的に'/parent/login'にリダイレクト
Route::redirect('/', '/parent/login');

// 保護者画面にてお子様ユーザーのデータを取得する記述
Route::bind('child', fn (string $value) => User::query()
    ->whereKey($value)
    ->where('role', 'child')
    ->firstOrFail()
);

// 保護者管理画面
Route::prefix('parent')->name('parent.')->middleware('role.parent')->group(function () {
    // お子様一覧
    Route::get('/children', [ParentChildController::class, 'index'])
        ->name('children.index');

    // お子様管理一覧
    Route::get('/child/{child}', [ParentChildController::class, 'show'])
        ->name('children.show');

    // おこづかい入金
    Route::get('/pocket-money/{child}', [ParentPocketMoneyController::class, 'edit'])
        ->name('pocket-money.show');
    Route::put('/pocket-money/{child}', [ParentPocketMoneyController::class, 'update'])
        ->name('pocket-money.update');

    // お手伝いの記録
    Route::get('/chores/performance/{child}', [ParentChorePerformanceController::class, 'create'])
        ->name('chores.performance');
    Route::post('/chores/performance/{child}', [ParentChorePerformanceController::class, 'store'])
        ->name('chores.performance.store');

    // お手伝いの実績
    Route::get('/chores/history/{child}', [ParentChoreHistoryController::class, 'index'])
        ->name('chores.history');
    Route::put('/chores/history/{child}/{choreRecord}',[ParentChoreHistoryController::class, 'update'])
        ->name('chores.history.update');
    Route::delete('/chores/history/{child}/{choreRecord}', [ParentChoreHistoryController::class, 'destroy'])
        ->name('chores.history.destroy');

    // 使用したお金
    Route::get('/child-payment/history/{child}', ParentChildPaymentHistoryController::class)
        ->name('child-payment.history');

    // 貯金の目標
    Route::get('/savings/{child}', ParentSavingController::class)
        ->name('savings.show');

    // 家族アカウント
    Route::get('/family-account', [ParentFamilyAccountController::class, 'index'])
        ->name('family-account.index');
    Route::post('/family-account/parents', [ParentFamilyAccountController::class, 'storeParent'])
        ->name('family-account.parents.store');
    Route::post('/family-account/children', [ParentFamilyAccountController::class, 'storeChild'])
        ->name('family-account.children.store');
    Route::put('/family-account/{account}', [ParentFamilyAccountController::class, 'update'])
        ->name('family-account.update');
    Route::delete('/family-account/{account}', [ParentFamilyAccountController::class, 'destroy'])
        ->name('family-account.destroy');

    // お手伝い設定
    Route::get('/chores-setting', [ParentChoreSettingController::class, 'index'])
        ->name('chores-setting.index');
    Route::post('/chores-setting', [ParentChoreSettingController::class, 'store'])
        ->name('chores-setting.store');
    Route::put('/chores-setting/{chore}', [ParentChoreSettingController::class, 'update'])
        ->name('chores-setting.update');
    Route::delete('/chores-setting/{chore}', [ParentChoreSettingController::class, 'destroy'])
        ->name('chores-setting.destroy');

    // プロフィール設定
    Route::get('/profile', [ParentProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::put('/profile', [ParentProfileController::class, 'update'])
        ->name('profile.update');
});

// お子様画面
Route::prefix('child')->name('child.')->middleware('role.child')->group(function () {
    // お子様ログイン
    Route::get('/login', [ChildAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [ChildAuthController::class, 'login'])->name('login.store');

    // お子様ログアウト
    Route::post('/logout', [ChildAuthController::class, 'logout'])->name('logout');

    // お子様ホーム画面
    Route::get('/', ChildHomeController::class)->name('home');

    // 使ったお金を記録
    Route::get('/payment-record', [ChildPaymentRecordController::class, 'create'])
        ->name('payment-record.create');
    Route::post('/payment-record', [ChildPaymentRecordController::class, 'store'])
        ->name('payment-record.store');

    // 最近使ったお金
    Route::get('/payment-history', ChildPaymentHistoryController::class)
        ->name('payment-history.index');

    // お手伝いの記録
    Route::get('/chores/history', ChildChoreHistoryController::class)
        ->name('chores.history');

    // ためたいお金
    Route::get('/savings', [ChildSavingController::class, 'show'])
        ->name('savings.show');
    Route::post('/savings', [ChildSavingController::class, 'store'])
        ->name('savings.store');

    // お子様プロフィール
    Route::get('/profile', [ChildProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::put('/profile', [ChildProfileController::class, 'update'])
        ->name('profile.update');
});
