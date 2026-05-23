<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Livewire\User\Dashboard;
use App\Livewire\User\InvoiceList;
use App\Livewire\User\PendingInvoiceList;
use App\Livewire\User\CreateInvoice;
use App\Livewire\User\InvoiceDetails;
use App\Livewire\UserInvoiceDetails;

Route::middleware('auth')->group(function (){

    Route::get('/logout',[AuthController::class,'logout'])->name('user.logout');

    Route::get('/logout',[AuthController::class,'logout'])->name('user.logout');


    Route::get('/dashboard',Dashboard::class)->name('user.dashboard');

    Route::get('/invoices-list',InvoiceList::class)->name('user.invoice-list');
    Route::get('/pending-invoices-list',PendingInvoiceList::class)->name('user.pending-invoice-list');
    Route::get('/create-invoice',CreateInvoice::class)->name('user.create-invoice');
    Route::get('/user-invoice-details/{id}',[UserInvoiceDetails::class,'getInvoiceDetails'])->name('user.invoice-details');
    Route::get('/get-medicine-Product',[\App\Livewire\Admin\CreateUserInvoice::class,'getMedicineItem'])->name('user.get-medicine-product');
    Route::post('/submit-pending-invoice',[\App\Livewire\Admin\CreateUserInvoice::class,'saveUserPendingInvoice'])->name('user.save-pending-invoice');
    Route::post('/submit-invoice',[\App\Livewire\Admin\CreateUserInvoice::class,'saveUserInvoice'])->name('user.save-invoice');
    Route::post('/update-user-invoice/{id}',[\App\Livewire\Admin\InvoiceDetails::class,'updateUserInvoice'])->name('user.update-invoice');
    Route::post('/update-user-pending-invoice',[\App\Livewire\Admin\CreateUserInvoice::class,'updateUserPendingInvoice'])->name('user.update-pending-invoice');
    Route::post('/save-user-update-invoice-with-pdf',[\App\Livewire\Admin\InvoiceDetails::class,'generateUserPdf'])->name('user.save-update-invoice-with-pdf');

//    Route::get('/user-invoice-details/{id}',[\App\Livewire\Admin\InvoiceDetails::class,'getInvoiceDetails'])->name('admin.invoice-details');
//    Route::get('/invoice-details/{id}',InvoiceDetails::class)->name('user.invoice-details');

    Route::get('/profile',[\App\Http\Controllers\AuthController::class,'profile'])->name('user.profile');
    Route::post('/profile',[\App\Http\Controllers\AuthController::class,'updateProfile'])->name('update-profile');
    Route::post('/update-Password',[\App\Http\Controllers\AuthController::class,'changePassword'])->name('update-Password');
    Route::post('/update-access-password',[\App\Http\Controllers\AuthController::class,'changeAccessPassword'])->name('update-access-Password');

});
