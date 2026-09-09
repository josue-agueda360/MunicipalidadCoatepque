<?php

use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PasswordRecoveryController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectDocumentFolderController;
use App\Http\Controllers\ProjectFileController;
use App\Http\Controllers\ProjectFinanceController;
use App\Http\Controllers\ProjectFolderController;
use App\Http\Controllers\ProjectLocationController;
use App\Http\Controllers\ProjectMonitoringController;
use App\Http\Controllers\PublicPortalController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }

    return view('welcome');
})->middleware('no.store')->name('login');

Route::middleware('no.store')->group(function (): void {
    Route::get('/portal-publico', PublicPortalController::class)
        ->name('public.portal');

    Route::get(
        '/portal-publico/ubicacion-de-proyectos',
        ProjectLocationController::class,
    )->name('public.project-locations');

    Route::get(
        '/portal-publico/monitoreo',
        [ProjectMonitoringController::class, 'index'],
    )->name('public.project-monitoring.index');

    Route::get(
        '/portal-publico/control-financiero',
        [ProjectFinanceController::class, 'index'],
    )->name('public.project-finance.index');

    Route::get(
        '/portal-publico/proyectos/{project}/finanzas/documentos/{projectFinancialRequiredDocument}/ver',
        [ProjectFinanceController::class, 'previewRequiredDocument'],
    )->middleware('throttle:30,1')
        ->name('public.project-finance.required-documents.preview');

    Route::get(
        '/portal-publico/proyectos/{project}/finanzas/documentos/{projectFinancialRequiredDocument}',
        [ProjectFinanceController::class, 'downloadRequiredDocument'],
    )->middleware('throttle:30,1')
        ->name('public.project-finance.required-documents.download');
});

Route::post(
    '/login',
    [AuthenticatedSessionController::class, 'store'],
)->middleware('throttle:5,1')->name('login.store');

Route::middleware(['auth', 'active.session', 'no.store'])->group(function (): void {
    Route::get('/inicio', HomeController::class)->name('home');

    Route::get(
        '/ubicacion-de-proyectos',
        ProjectLocationController::class,
    )->name('project-locations');

    Route::get(
        '/monitoreo',
        [ProjectMonitoringController::class, 'index'],
    )->name('project-monitoring.index');

    Route::post(
        '/monitoreo',
        [ProjectMonitoringController::class, 'store'],
    )->middleware('throttle:20,1')->name('project-monitoring.store');

    Route::patch(
        '/monitoreo/{projectMonitoring}/estado',
        [ProjectMonitoringController::class, 'updateStatus'],
    )->middleware('throttle:30,1')->name('project-monitoring.status');

    Route::get(
        '/control-financiero',
        [ProjectFinanceController::class, 'index'],
    )->name('project-finance.index');

    Route::middleware('user.manager')->group(function (): void {
        Route::get(
            '/usuarios',
            [UserManagementController::class, 'index'],
        )->name('user-management.index');

        Route::post(
            '/usuarios',
            [UserManagementController::class, 'store'],
        )->middleware('throttle:10,1')->name('user-management.store');

        Route::delete(
            '/usuarios/{user}',
            [UserManagementController::class, 'destroy'],
        )->middleware('throttle:10,1')->name('user-management.destroy');
    });

    Route::put(
        '/proyectos/{project}/finanzas/resumen',
        [ProjectFinanceController::class, 'updateProfile'],
    )->middleware('throttle:30,1')->name('project-finance.profile.update');

    Route::post(
        '/proyectos/{project}/finanzas/fuentes',
        [ProjectFinanceController::class, 'storeFundingSource'],
    )->middleware('throttle:30,1')->name('project-finance.funding-sources.store');

    Route::delete(
        '/proyectos/{project}/finanzas/fuentes/{projectFundingSource}',
        [ProjectFinanceController::class, 'destroyFundingSource'],
    )->middleware('throttle:10,1')->name('project-finance.funding-sources.destroy');

    Route::post(
        '/proyectos/{project}/finanzas/documentos/{documentType}',
        [ProjectFinanceController::class, 'storeRequiredDocument'],
    )->middleware('throttle:10,1')->name('project-finance.required-documents.store');

    Route::patch(
        '/proyectos/{project}/finanzas/contrato/omitir',
        [ProjectFinanceController::class, 'updateContractWaiver'],
    )->middleware('throttle:20,1')->name('project-finance.contract-waiver.update');

    Route::get(
        '/proyectos/{project}/finanzas/documentos/{projectFinancialRequiredDocument}/ver',
        [ProjectFinanceController::class, 'previewRequiredDocument'],
    )->middleware('throttle:30,1')->name('project-finance.required-documents.preview');

    Route::get(
        '/proyectos/{project}/finanzas/documentos/{projectFinancialRequiredDocument}',
        [ProjectFinanceController::class, 'downloadRequiredDocument'],
    )->middleware('throttle:30,1')->name('project-finance.required-documents.download');

    Route::delete(
        '/proyectos/{project}/finanzas/documentos/{projectFinancialRequiredDocument}',
        [ProjectFinanceController::class, 'destroyRequiredDocument'],
    )->middleware('throttle:10,1')->name('project-finance.required-documents.destroy');

    Route::post(
        '/proyectos/{project}/finanzas/movimientos',
        [ProjectFinanceController::class, 'storeMovement'],
    )->middleware('throttle:20,1')->name('project-finance.movements.store');

    Route::delete(
        '/proyectos/{project}/finanzas/movimientos/{projectFinancialMovement}',
        [ProjectFinanceController::class, 'destroyMovement'],
    )->middleware('throttle:10,1')->name('project-finance.movements.destroy');

    Route::get(
        '/proyectos/{project}/finanzas/movimientos/{projectFinancialMovement}/documentos/{projectFinancialDocument}/ver',
        [ProjectFinanceController::class, 'previewDocument'],
    )->middleware('throttle:30,1')->name('project-finance.documents.preview');

    Route::get(
        '/proyectos/{project}/finanzas/movimientos/{projectFinancialMovement}/documentos/{projectFinancialDocument}',
        [ProjectFinanceController::class, 'downloadDocument'],
    )->middleware('throttle:30,1')->name('project-finance.documents.download');

    Route::post(
        '/carpetas-de-proyectos',
        [ProjectFolderController::class, 'store'],
    )->middleware('throttle:20,1')->name('project-folders.store');

    Route::patch(
        '/carpetas-de-proyectos/{projectFolder}',
        [ProjectFolderController::class, 'update'],
    )->middleware('throttle:20,1')->name('project-folders.update');

    Route::delete(
        '/carpetas-de-proyectos/{projectFolder}',
        [ProjectFolderController::class, 'destroy'],
    )->middleware('throttle:10,1')->name('project-folders.destroy');

    Route::post(
        '/carpetas-de-proyectos/{projectFolder}/proyectos',
        [ProjectController::class, 'store'],
    )->middleware('throttle:20,1')->name('projects.store');

    Route::patch(
        '/proyectos/{project}',
        [ProjectController::class, 'update'],
    )->middleware('throttle:20,1')->name('projects.update');

    Route::delete(
        '/proyectos/{project}',
        [ProjectController::class, 'destroy'],
    )->middleware('throttle:10,1')->name('projects.destroy');

    Route::post(
        '/proyectos/{project}/documentos',
        [ProjectDocumentFolderController::class, 'store'],
    )->middleware('throttle:20,1')->name('project-document-folders.store');

    Route::delete(
        '/proyectos/{project}/documentos/{projectDocumentFolder}',
        [ProjectDocumentFolderController::class, 'destroy'],
    )->middleware('throttle:10,1')->name('project-document-folders.destroy');

    Route::post(
        '/proyectos/{project}/archivos/{slot}',
        [ProjectFileController::class, 'store'],
    )->middleware('throttle:10,1')->name('project-files.store');

    Route::post(
        '/proyectos/{project}/enlaces/{slot}',
        [ProjectFileController::class, 'storeLink'],
    )->middleware('throttle:10,1')->name('project-files.links.store');

    Route::get(
        '/proyectos/{project}/archivos/{projectFile}/ver',
        [ProjectFileController::class, 'preview'],
    )->middleware('throttle:30,1')->name('project-files.preview');

    Route::get(
        '/proyectos/{project}/archivos/{projectFile}',
        [ProjectFileController::class, 'download'],
    )->middleware('throttle:30,1')->name('project-files.download');

    Route::delete(
        '/proyectos/{project}/archivos/{projectFile}',
        [ProjectFileController::class, 'destroy'],
    )->middleware('throttle:10,1')->name('project-files.destroy');

    Route::post('/sesion/actividad', function () {
        return response()->noContent();
    })->middleware('throttle:6,1')->name('session.activity');

    Route::post(
        '/logout',
        [AuthenticatedSessionController::class, 'destroy'],
    )->name('logout');
});

Route::post(
    '/recuperar-contrasena/codigo',
    [PasswordRecoveryController::class, 'sendCode'],
)->middleware('throttle:3,1')->name('password.recovery.code');

Route::post(
    '/recuperar-contrasena/verificar',
    [PasswordRecoveryController::class, 'verifyCode'],
)->middleware('throttle:10,1')->name('password.recovery.verify');

Route::post(
    '/recuperar-contrasena/restablecer',
    [PasswordRecoveryController::class, 'resetPassword'],
)->middleware('throttle:5,1')->name('password.recovery.reset');
