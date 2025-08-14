<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompoundController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\AppartmentPDFController;
use App\Http\Controllers\FinancialTrasanctionController;
use App\Http\Controllers\FinancialTrasanctionPDFController;
use App\Http\Controllers\MaintenancePDFController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\TryTransactionController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group(['prefix' => '/'],function(){
    Route::get('/', [AdminController::class,"dashboard"])->name('home');
    Route::post('/data', [AdminController::class,"getData"])->name('data');
    Route::get('/download', [UserController::class,"download"])->name('download-backup');

});

Route::group(['prefix' => '/payment'],function(){
    Route::post('/rent', [UserController::class,"payment"])->name('payment.rent');
    Route::post('/cash', [UserController::class,"paymentCash"])->name('payment.cash');
    Route::get('/success', [UserController::class,"paymentSucess"])->name('payment.success');
    Route::get('/failure', [UserController::class,"paymentFailure"])->name('payment.failure');
    Route::post('/save', [UserController::class,"save"])->name('payment.save');
});

//ss
Route::group(['prefix' => '/tryTransactions'],function(){
    // Route::post('/rent', [TryTransactionController::class,"payment"])->name('payment.rent');
    // Route::post('/cash', [TryTransactionController::class,"paymentCash"])->name('payment.cash');
    // Route::get('/success', [TryTransactionController::class,"paymentSucess"])->name('payment.success');
    // Route::get('/failure', [TryTransactionController::class,"paymentFailure"])->name('payment.failure');
    Route::get('/', [TryTransactionController::class,"showAll"])->name('tryTransactions.showAll');
     Route::get('/filter', [TryTransactionController::class,"filter"])->name('tryTransactions.filter');
});
//ss


Route::group(['prefix' => 'user'],function(){
    Route::get('/', [UserController::class,"index"])->name('user');
    Route::post('/users', [UserController::class, 'users'])->name('users');
    Route::post('/usersTenant', [UserController::class, 'usersTenant'])->name('usersTenant');
    Route::post('api/fetch-minor', [UserController::class, 'fetchMainor'])->name('user.fetch');
    Route::get('/upsert/{user?}',[UserController::class,'upsert'])->name('user.upsert');
    Route::get('/filter',[UserController::class,'filter'])->name('user.filter');
    Route::post('/modify',[UserController::class,'modify'])->name('user.modify');
    Route::post('/modify/password',[UserController::class,'modifyPassword'])->name('user.password');
    Route::post('/delete/{user}',[UserController::class,'destroy'])->name('user.delete');
});

Route::group(['prefix' => 'compound'],function(){
    Route::get('/', [CompoundController::class,"index"])->name('compound');
    Route::post('/compounds', [CompoundController::class, 'compounds'])->name('compounds');
    Route::post('api/fetch-minor', [CompoundController::class, 'fetchMainor'])->name('compound.fetch');
    Route::get('/upsert/{compound?}',[CompoundController::class,'upsert'])->name('compound.upsert');
    Route::get('/filter',[CompoundController::class,'filter'])->name('compound.filter');
    Route::post('/modify',[CompoundController::class,'modify'])->name('compound.modify');
    Route::post('/delete/{compound}',[CompoundController::class,'destroy'])->name('compound.delete');
});

Route::group(['prefix' => 'building'],function(){
    Route::get('/', [BuildingController::class,"index"])->name('building');
    Route::post('/buildings', [BuildingController::class, 'buildings'])->name('buildings');
    Route::get('/compounds', [BuildingController::class, 'retrieveCompound'])->name('buildings.compounds');
    Route::get('/users', [BuildingController::class, 'retrieveUser'])->name('buildings.users');
    Route::post('api/fetch-minor', [BuildingController::class, 'fetchMainor'])->name('building.fetch');
    Route::get('/upsert/{building?}',[BuildingController::class,'upsert'])->name('building.upsert');
    Route::get('/filter',[BuildingController::class,'filter'])->name('building.filter');
    Route::post('/modify',[BuildingController::class,'modify'])->name('building.modify');
    Route::post('/edit',[BuildingController::class,'edit'])->name('building.edit');
    Route::post('/name',[BuildingController::class,'name'])->name('building.name');
    Route::post('/status',[BuildingController::class,'status'])->name('building.status');
    Route::get('/edit-building/{building?}',[BuildingController::class,'editBuilding'])->name('building.edit.building');
    Route::post('/add-appartment',[BuildingController::class,'appartment'])->name('building.appartment');
    Route::post('/appartment-delete',[BuildingController::class,'appartmentDelete'])->name('building.appartment.delete');
    Route::post('/delete/{building}',[BuildingController::class,'destroy'])->name('building.delete');
    Route::get('/export-apartments',[BuildingController::class, 'exportApartment'])->name('export-apartments');
    Route::get('/pdf/{building?}',[AppartmentPDFController::class,'index'])->name('building-pdf');
    Route::get('/export-revenu',[BuildingController::class, 'exportRevenu'])->name('export-revenu');
    Route::get('/pdf-buildings',[AppartmentPDFController::class,'indexBuildings'])->name('building.buildings-pdf');
    Route::get('/report',[BuildingController::class,'reportIndex'])->name('building.report');
    Route::get('/reportUpsert/{report?}',[BuildingController::class,'reportUpsert'])->name('building.report-upsert');
    Route::post('/reportModify',[BuildingController::class,'reportModify'])->name('building.report-modify');
    Route::post('/reportUpdate',[BuildingController::class,'reportUpdateData'])->name('building.report-update-data');
    Route::get('/reportFilter',[BuildingController::class,'reportFilter'])->name('building.report-filter');
    Route::post('/reportDestroy',[BuildingController::class,'reportDestroy'])->name('building.report-delete');
      // routes/admin.php
    Route::get('/building/export-tenants-month', [BuildingController::class, 'exportTenantsMonth'])
    ->name('building.export-tenants-month');
});

Route::group(['prefix' => 'maintenance'],function(){
    Route::get('/', [MaintenanceController::class,"index"])->name('maintenance');
    Route::post('/building', [MaintenanceController::class, 'building'])->name('maintenance.building');
    Route::post('api/fetch-minor', [MaintenanceController::class, 'fetchMainor'])->name('maintenance.fetch');
    Route::get('/upsert/{maintenance?}',[MaintenanceController::class,'upsert'])->name('maintenance.upsert');
    Route::get('/add/{user}/{compound}/{building}/{apartment}',[MaintenanceController::class,'add'])->name('maintenance.add');
    Route::get('/filter',[MaintenanceController::class,'filter'])->name('maintenance.filter');
    Route::post('/modify',[MaintenanceController::class,'modify'])->name('maintenance.modify');
    Route::post('/delete/{maintenance}',[MaintenanceController::class,'destroy'])->name('maintenance.delete');
    Route::get('/pdf-maintenances',[MaintenancePDFController::class,'index'])->name('maintenance.maintenances-pdf');
    Route::get('/export-maintenances',[MaintenanceController::class, 'exportMaintenance'])->name('export-maintenances');

});

Route::group(['prefix' => 'revenue'],function(){
    Route::get('/', [RevenueController::class,"index"])->name('revenue');
    Route::post('/building', [RevenueController::class, 'building'])->name('revenue.building');
    Route::post('api/fetch-minor', [RevenueController::class, 'fetchMainor'])->name('revenue.fetch');
    Route::get('/upsert/{maintenance?}',[RevenueController::class,'upsert'])->name('revenue.upsert');
    Route::get('/add/{user}/{compound}/{building}/{apartment}',[RevenueController::class,'add'])->name('revenue.add');
    Route::get('/filter',[RevenueController::class,'filter'])->name('revenue.filter');
    Route::post('/modify',[RevenueController::class,'modify'])->name('revenue.modify');
    Route::post('/delete/{revenue}',[RevenueController::class,'destroy'])->name('revenue.delete');
});

Route::group(['prefix' => 'apartment'],function(){
    Route::get('/', [ApartmentController::class,"index"])->name('apartment');
    Route::post('api/fetch-minor', [ApartmentController::class, 'fetchMainor'])->name('apartment.fetch');
    Route::get('/upsert/{apartment?}',[ApartmentController::class,'upsert'])->name('apartment.upsert');
    Route::get('/filter',[ApartmentController::class,'filter'])->name('apartment.filter');
    Route::post('/apartments', [ApartmentController::class, 'apartments'])->name('apartments');
    Route::post('/modify',[ApartmentController::class,'modify'])->name('apartment.modify');
    Route::post('/delete/{apartment}',[ApartmentController::class,'destroy'])->name('apartment.delete');
});

Route::group(['prefix' => 'financial_transaction'],function(){
    Route::get('/', [FinancialTrasanctionController::class, "index"])->name('financial_transaction');
    Route::get('/filter', [FinancialTrasanctionController::class, 'filter'])->name('financial_transaction.filter');
    Route::get('/pdf-financial',[FinancialTrasanctionPDFController::class, 'index'])->name('financial_transaction.financial-pdf');
    // Route::get('/show/{id}',[FinancialTrasanctionController::class,'show'])->name('financial_transaction-pdf');
    Route::get('/show/{id}',[FinancialTrasanctionPDFController::class,'show'])->name('financial_transaction-pdf');
    Route::post('/delete/{financial_transaction}',[FinancialTrasanctionController::class,'destroy'])->name('financial_transaction.delete');
    Route::get('/export_transactions',[FinancialTrasanctionController::class, 'exportTransactions'])->name('export-transactions');

});

Route::group(['prefix' => 'profile'],function(){
    Route::get('/', [ProfileController::class, "index"])->name('profile');
});

Route::group(['prefix' => 'unpaid'],function(){
    Route::get('/', [AdminController::class, "unpaidUsers"])->name('unpaid');
    Route::get('/filter', [AdminController::class, "filterUnpaidUsers"])->name('unpaid.filter');
});

Route::group(['prefix' => 'empty'],function(){
    Route::get('/', [ApartmentController::class, "emptyApartments"])->name('empty');
    Route::get('/filter', [ApartmentController::class, "filterEmptyApartments"])->name('empty.filter');
});

Route::group(['prefix' => 'tenant'],function(){
    Route::get('/', [TenantController::class,"index"])->name('tenant');
    Route::post('api/fetch-minor', [TenantController::class, 'fetchMainor'])->name('tenant.fetch');
    Route::get('/upsert/{tenant?}',[TenantController::class,'upsert'])->name('tenant.upsert');
    Route::get('/filter',[TenantController::class,'filter'])->name('tenant.filter');
    Route::post('/status',[TenantController::class,'status'])->name('tenant.status');
    Route::post('/modify',[TenantController::class,'modify'])->name('tenant.modify');
    Route::post('/delete/{tenant}',[TenantController::class,'destroy'])->name('tenant.delete');
});

Route::group(['prefix' => 'posts'], function(){
    Route::get('/', [PostController::class, "index"])->name('posts');
    Route::get('/insert', [PostController::class, 'showCreate'])->name('posts.insert.show');
    Route::post('/insert', [PostController::class, 'create'])->name('posts.insert');
    Route::get('/edit/{post_id}', [PostController::class, 'showEdit'])->name('posts.edit.show');
    Route::post('/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::post('/delete/{post_id}', [PostController::class, 'delete'])->name('posts.delete');
    Route::get('/{post_id}', [PostController::class, "show"])->name('posts.details');

});
