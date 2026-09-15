<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ScholarshipApplicationController as AdminScholarshipApplicationController;
use App\Http\Controllers\Admin\ScholarshipController as AdminScholarshipController;
use App\Http\Controllers\Admin\ScholarshipRequiredDocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScholarshipApplicationController;
use App\Http\Controllers\ScholarshipApplicationDocumentController;
use App\Http\Controllers\ScholarshipController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->role === UserRole::Admin
        ? redirect()->route('admin.dashboard')
        : redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'role:student'])->name('dashboard');

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:admin'])
    ->name('admin.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/password/change', function () {
        if (! auth()->user()->must_change_password) {
            return redirect('/');
        }

        return view('auth.force-password-change');
    })->name('password.force-change');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/scholarship-applications/{application}/documents/{document}', [ScholarshipApplicationDocumentController::class, 'show'])
        ->name('scholarship-applications.documents.show');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('scholarships', AdminScholarshipController::class);

    Route::post('scholarships/{scholarship}/required-documents', [ScholarshipRequiredDocumentController::class, 'store'])
        ->name('scholarships.required-documents.store');
    Route::delete('scholarships/{scholarship}/required-documents/{requiredDocument}', [ScholarshipRequiredDocumentController::class, 'destroy'])
        ->name('scholarships.required-documents.destroy');

    Route::patch('scholarships/{scholarship}/applications/{application}', [AdminScholarshipApplicationController::class, 'update'])
        ->name('scholarships.applications.update');

    Route::post('scholarships/{scholarship}/applications/import-decisions', [AdminScholarshipApplicationController::class, 'importDecisions'])
        ->name('scholarships.applications.import-decisions');
});

Route::middleware(['auth', 'verified', 'role:student'])->group(function () {
    Route::get('scholarships', [ScholarshipController::class, 'index'])->name('scholarships.index');
    Route::get('scholarships/{scholarship}', [ScholarshipController::class, 'show'])->name('scholarships.show');
    Route::get('scholarships/{scholarship}/apply', [ScholarshipApplicationController::class, 'create'])->name('scholarships.apply');
    Route::post('scholarships/{scholarship}/apply', [ScholarshipApplicationController::class, 'store'])->name('scholarships.apply.store');

    Route::get('scholarship-applications', [ScholarshipApplicationController::class, 'index'])->name('scholarship-applications.index');
    Route::get('scholarship-applications/{application}', [ScholarshipApplicationController::class, 'show'])->name('scholarship-applications.show');
});

require __DIR__.'/auth.php';
