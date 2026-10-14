<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\TwoFactorSetupController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public or unauthenticated routes handled by Fortify (/login, /two-factor-challenge, etc.)

// Authenticated Routes
Route::middleware(['auth', 'enforce.2fa'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Analytics\DashboardController::class, 'index'])->name('dashboard');

    // Interactive Cartographic Map Engine (Leaflet + Supercluster)
    Route::get('/map', [\App\Http\Controllers\Map\MapController::class, 'index'])->name('map.index');

    // 2FA Setup Flow (excluded from enforce.2fa redirection inside the middleware itself)
    Route::get('/two-factor/setup', [TwoFactorSetupController::class, 'show'])->name('two-factor.setup');

    // Project Portfolio
    Route::get('/projects', [\App\Http\Controllers\Portfolio\ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [\App\Http\Controllers\Portfolio\ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [\App\Http\Controllers\Portfolio\ProjectController::class, 'store'])->name('projects.store');

    // Shared Location & Institution Registries
    Route::get('/sites', [\App\Http\Controllers\Registry\SiteController::class, 'index'])->name('sites.index');
    Route::get('/sites/create', [\App\Http\Controllers\Registry\SiteController::class, 'create'])->name('sites.create');
    Route::post('/sites', [\App\Http\Controllers\Registry\SiteController::class, 'store'])->name('sites.store');
    Route::post('/sites/import', [\App\Http\Controllers\Registry\SiteController::class, 'import'])->name('sites.import');
    Route::get('/sites/{site}', [\App\Http\Controllers\Registry\SiteController::class, 'show'])->name('sites.show');

    // Field Reporting & Verification Engine
    Route::get('/submissions/create', [\App\Http\Controllers\Reporting\SubmissionController::class, 'create'])->name('submissions.create');
    Route::post('/submissions', [\App\Http\Controllers\Reporting\SubmissionController::class, 'store'])->name('submissions.store');
    Route::get('/reviews', [\App\Http\Controllers\Reporting\SubmissionController::class, 'reviewQueue'])->name('reviews.index');
    Route::post('/reviews/{fieldReport}/verify', [\App\Http\Controllers\Reporting\SubmissionController::class, 'verify'])->name('reviews.verify');

    Route::get('/institutions', [\App\Http\Controllers\Registry\InstitutionController::class, 'index'])->name('institutions.index');
    Route::post('/institutions', [\App\Http\Controllers\Registry\InstitutionController::class, 'store'])->name('institutions.store');

    // Accomplishment Reporting & Automated Subscriptions
    Route::get('/reports', [\App\Http\Controllers\Reporting\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/excel', [\App\Http\Controllers\Reporting\ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::post('/reports/subscriptions', [\App\Http\Controllers\Reporting\ReportController::class, 'storeSubscription'])->name('reports.subscriptions.store');
    Route::delete('/reports/subscriptions/{subscription}', [\App\Http\Controllers\Reporting\ReportController::class, 'destroySubscription'])->name('reports.subscriptions.destroy');

    // Admin Operations
    Route::prefix('admin')->name('admin.')->group(function () {
        // Users & Scoped Grant Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/{user}/grants', [UserController::class, 'storeGrant'])->name('users.grants.store');
        Route::delete('/grants/{grant}', [UserController::class, 'destroyGrant'])->name('grants.destroy');
        Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

        // Immutable Audit Trail
        Route::get('/audit', [\App\Http\Controllers\Admin\AuditController::class, 'index'])->name('audit.index');

        // PSGC Hierarchy & Congressional Districts
        Route::get('/geo', [\App\Http\Controllers\Admin\GeoController::class, 'index'])->name('geo.index');
        Route::get('/geo/barangays/{citymunCode}', [\App\Http\Controllers\Admin\GeoController::class, 'barangays'])->name('geo.barangays');

        // Project Types, Status Workflows & Custom Field Builder
        Route::get('/project-types', [\App\Http\Controllers\Admin\ProjectTypeController::class, 'index'])->name('project-types.index');
        Route::get('/project-types/{projectType}/edit', [\App\Http\Controllers\Admin\ProjectTypeController::class, 'edit'])->name('project-types.edit');
        Route::put('/project-types/{projectType}', [\App\Http\Controllers\Admin\ProjectTypeController::class, 'update'])->name('project-types.update');
        Route::post('/project-types/{projectType}/statuses', [\App\Http\Controllers\Admin\ProjectTypeController::class, 'storeStatus'])->name('project-types.statuses.store');
        Route::delete('/project-types/{projectType}/statuses/{statusDef}', [\App\Http\Controllers\Admin\ProjectTypeController::class, 'deleteStatus'])->name('project-types.statuses.destroy');
        Route::post('/project-types/{projectType}/fields', [\App\Http\Controllers\Admin\ProjectTypeController::class, 'storeField'])->name('project-types.fields.store');
        Route::delete('/project-types/{projectType}/fields/{fieldDef}', [\App\Http\Controllers\Admin\ProjectTypeController::class, 'deleteField'])->name('project-types.fields.destroy');
    });
});
