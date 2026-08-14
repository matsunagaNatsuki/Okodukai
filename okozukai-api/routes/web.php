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

Route::redirect('/', '/parent/login');

Route::bind('child', fn (string $value) => User::query()
    ->whereKey($value)
    ->where('role', 'child')
    ->firstOrFail());

Route::prefix('parent')->name('parent.')->middleware('role.parent')->group(function () {
    Route::get('/children', [ParentChildController::class, 'index'])
        ->name('children.index');
    Route::get('/child/{child}', [ParentChildController::class, 'show'])
        ->name('children.show');
    Route::get('/pocket-money/{child}', [ParentPocketMoneyController::class, 'edit'])
        ->name('pocket-money.show');
    Route::put('/pocket-money/{child}', [ParentPocketMoneyController::class, 'update'])
        ->name('pocket-money.update');
    Route::get('/chores/performance/{child}', [ParentChorePerformanceController::class, 'create'])
        ->name('chores.performance');
    Route::post('/chores/performance/{child}', [ParentChorePerformanceController::class, 'store'])
        ->name('chores.performance.store');
    Route::get('/chores/history/{child}', [ParentChoreHistoryController::class, 'index'])
        ->name('chores.history');
    Route::put('/chores/history/{child}/{choreRecord}', [ParentChoreHistoryController::class, 'update'])
        ->name('chores.history.update');
    Route::delete('/chores/history/{child}/{choreRecord}', [ParentChoreHistoryController::class, 'destroy'])
        ->name('chores.history.destroy');
    Route::get('/child-payment/history/{child}', ParentChildPaymentHistoryController::class)
        ->name('child-payment.history');
    Route::get('/savings/{child}', ParentSavingController::class)
        ->name('savings.show');
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
    Route::get('/chores-setting', [ParentChoreSettingController::class, 'index'])
        ->name('chores-setting.index');
    Route::post('/chores-setting', [ParentChoreSettingController::class, 'store'])
        ->name('chores-setting.store');
    Route::put('/chores-setting/{chore}', [ParentChoreSettingController::class, 'update'])
        ->name('chores-setting.update');
    Route::delete('/chores-setting/{chore}', [ParentChoreSettingController::class, 'destroy'])
        ->name('chores-setting.destroy');
    Route::get('/profile', [ParentProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::put('/profile', [ParentProfileController::class, 'update'])
        ->name('profile.update');
});

Route::prefix('child')->name('child.')->middleware('role.child')->group(function () {
    Route::get('/login', [ChildAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [ChildAuthController::class, 'login'])->name('login.store');
    Route::post('/logout', [ChildAuthController::class, 'logout'])->name('logout');

    Route::get('/', ChildHomeController::class)->name('home');
    Route::get('/payment-record', [ChildPaymentRecordController::class, 'create'])
        ->name('payment-record.create');
    Route::post('/payment-record', [ChildPaymentRecordController::class, 'store'])
        ->name('payment-record.store');
    Route::get('/payment-history', ChildPaymentHistoryController::class)
        ->name('payment-history.index');
    Route::get('/chores/history', ChildChoreHistoryController::class)
        ->name('chores.history');
    Route::get('/savings', [ChildSavingController::class, 'show'])
        ->name('savings.show');
    Route::post('/savings', [ChildSavingController::class, 'store'])
        ->name('savings.store');
    Route::get('/profile', [ChildProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::put('/profile', [ChildProfileController::class, 'update'])
        ->name('profile.update');
});
