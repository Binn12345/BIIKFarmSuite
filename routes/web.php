<?php

use App\Http\Controllers\Auth\PasswordController;
use App\Livewire\Admin\AuditLogIndex;
use App\Livewire\Admin\RoleManagement;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Farm\AnimalIndex;
use App\Livewire\Farm\FarmRecordIndex;
use App\Livewire\Farm\FarmReportIndex;
use App\Livewire\Farm\PenIndex;
use App\Livewire\Account\Profile;
use App\Support\DashboardModules;
use App\Support\DashboardSummary;
use App\Support\FarmRecordCatalog;
use App\Support\PermissionCatalog;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');

    Route::get('/forgot-password', [PasswordController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'sendResetLink'])
        ->middleware('throttle:6,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'reset'])
        ->middleware('throttle:6,1')
        ->name('password.update');
});

Route::get('/email/verify', fn () => view('auth.verify-email'))
    ->middleware('auth')
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect()->route('dashboard')->with('status', 'email-verified');
})->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return redirect()->route('dashboard');
    }

    $request->user()->sendEmailVerificationNotification();

    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth')->name('logout');

Route::middleware(['auth', 'verified', 'account.active'])->group(function () {
    Route::get('/dashboard', function () {
        return view('pages.dashboard', [
            'modules' => DashboardModules::for(request()->user()),
            'summary' => DashboardSummary::for(request()->user()),
        ]);
    })->middleware('can:dashboard.view')->name('dashboard');
    Route::get('/profile', Profile::class)->name('account.profile');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', fn () => redirect()->route('dashboard'))->name('dashboard');
        Route::get('/users', UserManagement::class)->middleware('can:users.view')->name('users');
        Route::get('/roles', RoleManagement::class)->middleware('can:roles.manage')->name('roles');
        Route::view('/permissions', 'pages.admin.permissions', [
            'catalog' => PermissionCatalog::modules(),
        ])->middleware('can:permissions.manage')->name('permissions');
        Route::get('/audit-logs', AuditLogIndex::class)
            ->middleware('can:audit-logs.view')
            ->name('audit-logs');
    });

    Route::get('/modules/animals', AnimalIndex::class)
        ->middleware('can:animals.view')
        ->name('modules.animals');
    Route::get('/modules/pens', PenIndex::class)
        ->middleware('can:pens.view')
        ->name('modules.pens');

    foreach (FarmRecordCatalog::modules() as $slug => $module) {
        Route::get('/modules/'.$slug, FarmRecordIndex::class)
            ->defaults('module', $slug)
            ->middleware('can:'.$slug.'.view')
            ->name('modules.'.$slug);
    }
    Route::get('/modules/reports', FarmReportIndex::class)
        ->middleware('can:reports.view')
        ->name('modules.reports');
});
