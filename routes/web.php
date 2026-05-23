<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Dashboard;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\InvoiceController;

Route::get('/',[AuthController::class,'loginForm'])->name('auth.login-form');
Route::post('login',[AuthController::class,'login'])->name('auth.login');

// routes/web.php
Route::get('/product-search', [\App\Http\Controllers\ProductSearchController::class, 'search'])->name('product.search');


Route::prefix('admin')->middleware(['auth','authorized'])->group(function (){

    Route::get('/logout',[AuthController::class,'logout'])->name('admin.logout');

    Route::post('/verify-amount-password', [Dashboard::class, 'verifyAmountPassword'])->name('verify.amount.password');


    Route::get('/dashboard',Dashboard::class)->name('admin.dashboard');
    Route::get('/customer-list',\App\Livewire\Admin\User::class)->name('customer.list');
    Route::get('/customer-invoice-list/{id}',\App\Livewire\Admin\UserInvoiceList::class)->name('customer.invoice.list');
    Route::get('/customer-details/{slug}',\App\Livewire\Admin\User::class)->name('customer.details');
    Route::get('/customer-delete/{id}',\App\Livewire\Admin\User::class)->name('customer.delete');
    Route::get('/staff-list',\App\Livewire\Admin\Staff::class)->name('staff.list');


    Route::get('/companies',\App\Livewire\Admin\Company::class)->name('admin.company');

    Route::get('/medicines',\App\Livewire\Admin\Medicine::class)->name('admin.medicine');
    Route::get('/low-medicines-stock',\App\Livewire\Admin\Medicine::class)->name('admin.low-medicine-stock');
    Route::get('/pre-medicine-stock',\App\Livewire\Admin\PreProductStocks::class)->name('admin.pre-product-medicine-stock');
    Route::get('/pre-medicine-stock-item/{id}',\App\Livewire\Admin\PreOrderStockItem::class)->name('admin.pre-product-medicine-stock-item');
    Route::get('/user-invoices',\App\Livewire\Admin\OrderInvoice::class)->name('admin.user-order-invoice');
    Route::get('/draft-invoices',\App\Livewire\Admin\OrderInvoice::class)->name('admin.draft-order-invoice');
    Route::get('/staff-pending-invoices',\App\Livewire\Admin\StaffPendingInvoiceList::class)->name('admin.staff-pending-invoices');
    Route::get('/customer-pending-invoices',\App\Livewire\Admin\CustomerPendingInvoiceList::class)->name('admin.customer-pending-invoices');
    Route::get('/create-invoice',\App\Livewire\Admin\CreateUserInvoice::class)->name('admin.create-invoice');
    Route::get('/get-medicine-Product',[\App\Livewire\Admin\CreateUserInvoice::class,'getMedicineItem'])->name('admin.get-medicine-product');
    Route::post('/submit-invoice',[\App\Livewire\Admin\CreateUserInvoice::class,'saveInvoice'])->name('admin.save-invoice');
    Route::post('/submit-pending-invoice',[\App\Livewire\Admin\CreateUserInvoice::class,'pendingInvoice'])->name('admin.save-pending-invoice');
    Route::post('/update-pending-invoice',[\App\Livewire\Admin\CreateUserInvoice::class,'updatePendingInvoice'])->name('admin.update-pending-invoice');
    Route::post('/save-invoice-with-pdf',[\App\Livewire\Admin\CreateUserInvoice::class,'generatePdf'])->name('admin.save-invoice-with-pdf');
    Route::get('/invoice-details/{id}',[\App\Livewire\Admin\InvoiceDetails::class,'getInvoiceDetails'])->name('admin.invoice-details');
    Route::post('/update-invoice/{id}',[\App\Livewire\Admin\InvoiceDetails::class,'updateInvoice'])->name('admin.update-invoice');
    Route::post('/save-update-invoice-with-pdf',[\App\Livewire\Admin\InvoiceDetails::class,'generatePdf'])->name('admin.save-update-invoice-with-pdf');
    Route::get('/user-invoice-details/{id}',[\App\Livewire\Admin\UserInvoiceDetails::class,'getInvoiceDetails'])->name('admin.user-invoice-details');


    Route::get('/site-setting',\App\Livewire\Admin\SiteSetting::class)->name('admin.site-setting');
    Route::get('/acl',\App\Livewire\Admin\Acl::class)->name('admin.acl');

    Route::post('/add-role',[RolePermissionController::class,'addRole'])->name('add-role');
    Route::post('/update-role',[RolePermissionController::class,'updateRole'])->name('admin.update-role');
    Route::get('add-permission/{id}',[RolePermissionController::class,'getAllRole'])->name('admin.add-permission');
    Route::post('update-user-role-permission/{id}',[RolePermissionController::class,'updateRolePermission'])->name('update-user-role-permission');

    Route::post('/import-medicines',[RolePermissionController::class,'importMedicine'])->name('medicines.import');


    Route::get('/test',function (){
        $medicineType = Product::select('products.type')->where('products.type','!=','')->orderBy('type')->groupBy('products.type')->get();
//        $medicineStrength = Product::where('products.strength','!=','')->orderBy('type')->groupBy('products.type')->pluck('products.strength')->toArray();
        $medicineStrength = Product::where('strength', '!=', '')
            ->distinct()
            ->orderBy('strength')
            ->pluck('strength')
            ->toArray();
        dd($medicineType,$medicineStrength);

    });

});
