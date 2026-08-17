<?php

use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Api\AccountsController;
use App\Http\Controllers\Api\AppDataController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\ClientSasStatusController;
use App\Http\Controllers\Api\ClientsController;
use App\Http\Controllers\Api\CollectionsController;
use App\Http\Controllers\Api\CollectorBalancesController;
use App\Http\Controllers\Api\InvoicesController;
use App\Http\Controllers\Api\Member\OprationController;
use App\Http\Controllers\Api\Member\UsersApiController;
use App\Http\Controllers\Api\NotificationsController;
use App\Http\Controllers\Api\Settings;
use App\Http\Controllers\Api\Trainers\ScheduleController;
use App\Http\Controllers\Api\Trainers\TrainersController;
use App\Http\Controllers\Api\UnpaidInvoicesController;
use App\Http\Controllers\Api\ApiComplaints;
use App\Http\Controllers\Api\MasrofatController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'api', 'prefix' => 'v1'], function ($router) {
    // Zernio webhook — public endpoint, no auth (signature-verified internally)
    Route::post('webhooks/zernio', [\App\Http\Controllers\Admin\ZernioWebhookController::class, 'handle'])
        ->withoutMiddleware([\Illuminate\Routing\Middleware\ThrottleRequests::class]);

    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);


    Route::group(['middleware' => 'jwt'], function ($router) {
        Route::get('user/profile', [AuthController::class, 'show']);
        Route::post('user/update', [AuthController::class, 'update']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user/financial_sum', [AuthController::class, 'authUserFinancialSum']);


        Route::get('/clients', [ClientsController::class, 'index']);
        // Feature 009: batch SAS status — registered BEFORE parameterized
        // client routes so the literal segment is never captured by {id}.
        Route::post('/clients/sas-status', [ClientSasStatusController::class, 'check']);
        Route::get('/clients/{id}/invoices', [ClientsController::class, 'clientInvoices']);
        Route::get('/collections', [CollectionsController::class, 'index']);

        // Route::post('invoices', [InvoicesController::class, 'index']);
        Route::post('invoices', [InvoicesController::class, 'unpaidInvoices']);
        Route::post('interval/invoices', [InvoicesController::class, 'paidInvoices']);
        Route::get('invoice/{id}', [InvoicesController::class, 'show']);
        Route::post('/invoices/{id}/payments', [InvoicesController::class, 'securePayInvoice'])
            ->whereNumber('id');
        Route::get('/payments/{idempotencyKey}', [InvoicesController::class, 'securePaymentStatus'])
            ->whereUuid('idempotencyKey');
        Route::get('/invoice/{id}/print', [InvoicesController::class, 'print_invoice']);

        Route::get('/unpaid-invoices', [UnpaidInvoicesController::class, 'index']);

        Route::get('/admin/collector-balances', [CollectorBalancesController::class, 'index']);


        Route::post('/masrofat', [MasrofatController::class, 'index']);
        Route::get('/statistics', [MasrofatController::class, 'getSystemStatistics']);

        Route::get('/accounts', [AccountsController::class, 'index']);
        Route::post('/collectors', [AccountsController::class, 'collectors']);

        Route::get('/notifications', [NotificationsController::class, 'index']);
    });
});
