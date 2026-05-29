<?php

use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\SuperAdminController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ChatController;
use App\Http\Controllers\Web\ContractController;
use App\Http\Controllers\Web\TherapistShareViewController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\LegalController;
use App\Http\Controllers\Web\AssessmentWebController;
use App\Http\Controllers\Web\ProgramWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Callback de retorno para la app móvil (MercadoPago redirige aquí)
Route::get('/app-callback', fn() => view('app_callback'))->name('app.callback');

// Contratos por plan — acceso público
Route::get('/contratos/free', [ContractController::class, 'free'])->name('contracts.free');
Route::get('/contratos/pro',  [ContractController::class, 'pro'])->name('contracts.pro');
Route::get('/contratos/plus', [ContractController::class, 'plus'])->name('contracts.plus');

// Páginas legales — acceso público
Route::get('/privacidad',    [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/datos',         [LegalController::class, 'dataUsage'])->name('legal.data-usage');
Route::get('/cookies',       [LegalController::class, 'cookies'])->name('legal.cookies');
Route::get('/terminos',      [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/consentimiento',[LegalController::class, 'consent'])->name('legal.consent');

// Comparador de planes (público)
Route::get('/planes', fn() => view('plans.compare'))->name('plans.compare');

// Enlace compartido con terapeuta (público, con token)
Route::get('/shared/{token}', [TherapistShareViewController::class, 'show'])->name('shared.therapist');

// Planes
Route::get('/planes/pro',          [HomeController::class, 'proPlan'])->name('plans.pro');
Route::post('/planes/pro',         [HomeController::class, 'proPlanSubmit'])->name('plans.pro.submit');
Route::get('/planes/pro/callback', [HomeController::class, 'proPlanCallback'])->name('plans.pro.callback');
Route::get('/planes/full',          [HomeController::class, 'fullPlan'])->name('plans.full');
Route::post('/planes/full',         [HomeController::class, 'fullPlanSubmit'])->name('plans.full.submit');
Route::get('/planes/plus',           [HomeController::class, 'plusPlan'])->name('plans.plus');
Route::post('/planes/plus',          [HomeController::class, 'plusPlanSubmit'])->name('plans.plus.submit');
Route::post('/planes/plus/pay',      [HomeController::class, 'plusPlanPaySubmit'])->name('plans.plus.pay');
Route::get('/planes/plus/callback',  [HomeController::class, 'plusPlanPayCallback'])->name('plans.plus.callback');

Route::middleware('guest')->group(function () {
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/mi-plan',   [HomeController::class, 'miPlan'])->name('mi-plan');
    Route::get('/chat',            [ChatController::class, 'index'])->name('chat');
    Route::post('/chat/send',      [ChatController::class, 'send'])->name('chat.send');
    Route::post('/chat/transcribe',[ChatController::class, 'transcribe'])->name('chat.transcribe');
    Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');

    // Evaluaciones clínicas (GAD-7 / PHQ-9)
    Route::get('/evaluaciones',    [AssessmentWebController::class, 'index'])->name('assessments');
    Route::post('/evaluaciones',   [AssessmentWebController::class, 'store'])->name('assessments.store');

    // Programas de bienestar
    Route::get('/programas',                                    [ProgramWebController::class, 'index'])->name('programs');
    Route::post('/programas/{slug}/inscribir',                  [ProgramWebController::class, 'enroll'])->name('programs.enroll');
    Route::post('/programas/{slug}/dia/{day}/completar',        [ProgramWebController::class, 'completeDay'])->name('programs.complete-day');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',                    [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users',               [AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}',        [AdminController::class, 'userDetail'])->name('user');
    Route::post('/users/{user}',       [AdminController::class, 'updateUser'])->name('user.update');
    Route::post('/users-group',        [AdminController::class, 'groupAction'])->name('users.group');
    Route::get('/sessions',            [AdminController::class, 'sessions'])->name('sessions');
    Route::post('/sessions/{record}',  [AdminController::class, 'deleteSession'])->name('sessions.delete');
    Route::post('/sessions-export',    [AdminController::class, 'exportSessions'])->name('sessions.export');
    Route::get('/reports',             [AdminController::class, 'reports'])->name('reports');
    Route::get('/institution',         [AdminController::class, 'institution'])->name('institution');
    Route::post('/institution',        [AdminController::class, 'updateInstitution'])->name('institution.update');
});

Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/',                     [SuperAdminController::class, 'dashboard'])->name('dashboard');

    // Users
    Route::get('/users',                [SuperAdminController::class, 'users'])->name('users');
    Route::post('/users/{user}',        [SuperAdminController::class, 'updateUser'])->name('users.update');
    Route::get('/users/{user}/detail',  [SuperAdminController::class, 'userDetail'])->name('users.detail');

    // Institutions
    Route::get('/institutions',              [SuperAdminController::class, 'institutions'])->name('institutions');
    Route::post('/institutions',             [SuperAdminController::class, 'storeInstitution'])->name('institutions.store');
    Route::get('/institutions/{institution}',[SuperAdminController::class, 'editInstitution'])->name('institutions.edit');
    Route::put('/institutions/{institution}',[SuperAdminController::class, 'updateInstitution'])->name('institutions.update');

    // Sessions
    Route::get('/sessions',             [SuperAdminController::class, 'sessions'])->name('sessions');
    Route::post('/sessions/{record}/delete', [SuperAdminController::class, 'deleteSession'])->name('sessions.delete');
    Route::post('/sessions-export',     [SuperAdminController::class, 'exportSessions'])->name('sessions.export');

    // Plan Requests
    Route::get('/plan-requests',        [SuperAdminController::class, 'planRequests'])->name('plan-requests');
    Route::post('/plan-requests/{planRequest}', [SuperAdminController::class, 'reviewPlanRequest'])->name('plan-requests.review');

    // Subscriptions
    Route::get('/subscriptions',                  [SuperAdminController::class, 'subscriptions'])->name('subscriptions');
    Route::get('/subscriptions/{subscription}',   [SuperAdminController::class, 'showSubscription'])->name('subscriptions.show');
    Route::post('/subscriptions/{subscription}',  [SuperAdminController::class, 'updateSubscription'])->name('subscriptions.update');

    // Pro Orders
    Route::get('/pro-orders',                    [SuperAdminController::class, 'proOrders'])->name('pro-orders');
    Route::get('/pro-orders/{proOrder}',         [SuperAdminController::class, 'showProOrder'])->name('pro-orders.show');
    Route::post('/pro-orders/{proOrder}',        [SuperAdminController::class, 'reviewProOrder'])->name('pro-orders.review');

    // Groups
    Route::get('/groups',               [SuperAdminController::class, 'groups'])->name('groups');
    Route::post('/groups',              [SuperAdminController::class, 'storeGroup'])->name('groups.store');
    Route::get('/groups/{group}',       [SuperAdminController::class, 'editGroup'])->name('groups.edit');
    Route::post('/groups/{group}',      [SuperAdminController::class, 'updateGroup'])->name('groups.update');
    Route::delete('/groups/{group}',    [SuperAdminController::class, 'deleteGroup'])->name('groups.delete');
});
