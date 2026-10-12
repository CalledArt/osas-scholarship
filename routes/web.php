<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ScholarshipApplicationController as AdminScholarshipApplicationController;
use App\Http\Controllers\Admin\ScholarshipController as AdminScholarshipController;
use App\Http\Controllers\Admin\ScholarshipRequiredDocumentController;
use App\Http\Controllers\Admin\ScholarshipRequiredDocumentSampleController as AdminScholarshipRequiredDocumentSampleController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\StudentRosterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScholarshipApplicationController;
use App\Http\Controllers\ScholarshipApplicationDocumentController;
use App\Http\Controllers\ScholarshipController;
use App\Http\Controllers\ScholarshipRequiredDocumentSampleController;
use App\Http\Controllers\TutorialController;
use App\Http\Middleware\StartTutorialOnFirstVisit;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->role === UserRole::Admin
        ? redirect()->route('admin.dashboard')
        : redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:student', StartTutorialOnFirstVisit::class])
    ->name('dashboard');

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:admin', StartTutorialOnFirstVisit::class])
    ->name('admin.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/password/change', function () {
        if (! auth()->user()->must_change_password) {
            return redirect('/');
        }

        return view('auth.force-password-change');
    })->name('password.force-change');

    Route::get('/tutorial', [TutorialController::class, 'show'])->middleware('verified')->name('tutorial');
    Route::post('/tutorial/complete', [TutorialController::class, 'complete'])->name('tutorial.complete');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/scholarship-applications/{application}/documents/{document}', [ScholarshipApplicationDocumentController::class, 'show'])
        ->name('scholarship-applications.documents.show');

    Route::get('/scholarships/{scholarship}/required-documents/{requiredDocument}/sample', [ScholarshipRequiredDocumentSampleController::class, 'show'])
        ->name('scholarships.required-documents.sample');
    Route::get('/scholarships/{scholarship}/required-documents/{requiredDocument}/sample/download', [ScholarshipRequiredDocumentSampleController::class, 'download'])
        ->name('scholarships.required-documents.sample.download');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('scholarships', AdminScholarshipController::class);

    Route::post('scholarships/{scholarship}/required-documents', [ScholarshipRequiredDocumentController::class, 'store'])
        ->name('scholarships.required-documents.store');
    Route::delete('scholarships/{scholarship}/required-documents/{requiredDocument}', [ScholarshipRequiredDocumentController::class, 'destroy'])
        ->name('scholarships.required-documents.destroy');
    Route::put('scholarships/{scholarship}/required-documents/{requiredDocument}/sample', [AdminScholarshipRequiredDocumentSampleController::class, 'update'])
        ->name('scholarships.required-documents.sample.update');
    Route::delete('scholarships/{scholarship}/required-documents/{requiredDocument}/sample', [AdminScholarshipRequiredDocumentSampleController::class, 'destroy'])
        ->name('scholarships.required-documents.sample.destroy');

    Route::post('scholarships/{scholarship}/applications/import-decisions', [AdminScholarshipApplicationController::class, 'importDecisions'])
        ->name('scholarships.applications.import-decisions');

    Route::get('roster', [StudentRosterController::class, 'index'])->name('roster.index');

    Route::resource('students', AdminStudentController::class)->only(['index', 'edit', 'update']);
});

Route::middleware(['auth', 'verified', 'role:student'])->group(function () {
    Route::get('scholarships', [ScholarshipController::class, 'index'])->name('scholarships.index');
    // Registered ahead of scholarships/{scholarship}, which would otherwise capture this path.
    Route::view('scholarships/republic-act-10931', 'scholarships.republic-act-10931')->name('scholarships.republic-act-10931');
    Route::get('scholarships/{scholarship}', [ScholarshipController::class, 'show'])->name('scholarships.show');
    Route::get('scholarships/{scholarship}/apply', [ScholarshipApplicationController::class, 'create'])->name('scholarships.apply');
    Route::post('scholarships/{scholarship}/apply', [ScholarshipApplicationController::class, 'store'])->name('scholarships.apply.store');

    Route::get('scholarship-applications', [ScholarshipApplicationController::class, 'index'])->name('scholarship-applications.index');
    Route::get('scholarship-applications/{application}', [ScholarshipApplicationController::class, 'show'])->name('scholarship-applications.show');
});

require __DIR__.'/auth.php';
