<?php

use App\Http\Controllers\categoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatisticalController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/foo', function () {
    Artisan::call('storage:link');
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/categories', [categoryController::class, 'index'])->name('categories');
    Route::get('/categories/data', [categoryController::class, 'getData'])->name('categories.data');
    Route::post('/categories/add', [categoryController::class, 'store'])->name('categories.add');
    Route::post('/categories/edit/{id}', [categoryController::class, 'edit'])->name('categories.edit');
    Route::get('/categories/delete/{id}', [categoryController::class, 'destroy'])->name('categories.delete');
    Route::get('/search-categories', [categoryController::class, 'search'])->name('categories.search');

    Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier');
    Route::get('/supplier/data', [SupplierController::class, 'getData'])->name('supplier.data');
    Route::post('/supplier/add', [SupplierController::class, 'store'])->name('supplier.add');
    Route::get('/supplier/getsupplier/{id}', [SupplierController::class, 'getSupplierId'])->name('supplier.getsupplier');
    Route::post('/supplier/edit/{id}', [SupplierController::class, 'edit'])->name('supplier.edit');
    Route::get('/supplier/delete/{id}', [SupplierController::class, 'destroy'])->name('supplier.delete');
    Route::get('/search-suppliers', [SupplierController::class, 'search'])->name('suppliers.search');

    Route::get('/product', [ProductController::class, 'index'])->name('product');
    Route::get('/product/data', [ProductController::class, 'getData'])->name('product.data');
    Route::post('/product/add', [ProductController::class, 'store'])->name('product.add');
    Route::get('/product/get-product/{id}', [ProductController::class, 'getProductById'])->name('product.getproduct');
    Route::post('/product/edit/{id}', [ProductController::class, 'edit'])->name('product.edit');
    Route::get('/get-image/{id}', [ProductController::class, 'getImageUrl']);
    Route::get('/delete-image/{id}', [ProductController::class, 'deleteImageUrl']);
    Route::get('/product/delete/{id}', [ProductController::class, 'destroy'])->name('product.delete');
    Route::get('/search-products', [ProductController::class, 'search'])->name('product.search');
    Route::get('/product/{id}/barcode', [ProductController::class, 'generateBarcode'])->name('product.barcode');
    Route::get('/product/{id}/qrcode', [ProductController::class, 'generateQrCode'])->name('product.qrcode');

    Route::get('upload-imageee',function(){
        return view('upload.index');
    })->name('uploads');
    Route::post('/upload-images', [ProductController::class, 'uploadBatchImages'])->name('uploads.index');

    Route::get('/customer', [CustomerController::class, 'index'])->name('customer');
    Route::post('/customer/add', [CustomerController::class, 'store'])->name('customer.add');
    Route::get('/customer/data', [CustomerController::class, 'getData'])->name('customer.data');
    Route::post('/customer/edit/{id}', [CustomerController::class, 'edit'])->name('customer.edit');
    Route::get('/customer/delete/{id}', [CustomerController::class, 'destroy'])->name('customer.delete');
    Route::get('/search-customer', [CustomerController::class, 'search'])->name('customer.search');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile', [ProfileController::class, 'upload'])->name('profile.upload');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/invoice', [InvoiceController::class, 'index'])->name('invoice');
    Route::get('/invoice/filter/{value?}', [InvoiceController::class, 'filter'])->name('invoice.filter');
    Route::get('/invoice/filter-payment/{value?}', [InvoiceController::class, 'filter_pay_status'])->name('invoice.filter_pay_status');
    Route::post('/invoice/add', [InvoiceController::class, 'store'])->name('invoice.add');
    Route::get('/invoice/get-product-supplier/{id?}', [InvoiceController::class, 'getProductSupplier'])->name('invoice.getproduct_supplier');
    Route::get('/invoice/get-product', [InvoiceController::class, 'getProduct'])->name('invoice.getproduct');
    Route::get('/invoice/get-invoice-id/{id}', [InvoiceController::class, 'getInvoiceDetails'])->name('invoice.getInvoiceId');
    Route::get('/search-product', [InvoiceController::class, 'searchProduct'])->name('invoice.search_product');
    Route::get('/search-supplier', [InvoiceController::class, 'searchSupplier'])->name('invoice.search_supplier');
    Route::post('/invoice/update/{id}', [InvoiceController::class, 'update'])->name('invoice.update');
    Route::get('/invoice/delete/{id}', [InvoiceController::class, 'destroy'])->name('invoice.delete');
    // Route::get('/search-invoices', [SupplierController::class,'search'])->name('suppliers.search');

    Route::get('/account', [SupplierController::class, 'index'])->name('account');
    Route::get('/account/data', [SupplierController::class, 'getData'])->name('account.data');
    Route::post('/account/add', [SupplierController::class, 'store'])->name('account.add');
    Route::post('/account/edit/{id}', [SupplierController::class, 'edit'])->name('account.edit');
    Route::get('/account/delete/{id}', [SupplierController::class, 'destroy'])->name('account.delete');
    Route::get('/search-account', [SupplierController::class, 'search'])->name('account.search');

    Route::get('/product-location', [LocationController::class, 'index'])->name('location');
    Route::get('/auto-add-new-location', [LocationController::class, 'addNewLocationAutomatically'])->name('location.create');
    Route::get('location/get-shelf', [LocationController::class, 'getShelf'])->name('location.getShelf');
    Route::get('location/get-level', [LocationController::class, 'getLevel'])->name('location.getLevel');
    Route::get('/location/filter', [LocationController::class, 'getData'])->name('location.getData');

    //thống kê hóa đơn xuất
    Route::get('statistical/import-invoice',[StatisticalController::class, "showInventoryStats"])->name('statistical.import');
    Route::get('statistical/import-invoice/today',[StatisticalController::class, "statsToday"])->name('statistical.import.today');
    Route::get('statistical/import-invoice/yesterday',[StatisticalController::class, "statsYesterday"])->name('statistical.import.yesterday');
    Route::get('statistical/import-invoice/this-month',[StatisticalController::class, "statsThisMonth"])->name('statistical.import.this_month');
    Route::get('statistical/import-invoice/last-month',[StatisticalController::class, "statsLastMonth"])->name('statistical.import.last_month');

    //thống kê hóa đơn nhập
    Route::get('statistical/export-invoice',[StatisticalController::class, "showInventoryExportStats"])->name('statistical.export');
    Route::get('statistical/export-invoice/today',[StatisticalController::class, "statsExportToday"])->name('statistical.export.today');
    Route::get('statistical/export-invoice/yesterday',[StatisticalController::class, "statsExportYesterday"])->name('statistical.export.yesterday');
    Route::get('statistical/export-invoice/this-month',[StatisticalController::class, "statsExportThisMonth"])->name('statistical.export.this_month');
    Route::get('statistical/export-invoice/last-month',[StatisticalController::class, "statsExportLastMonth"])->name('statistical.export.last_month');

    //số lượng tồn kho cuối ngày
    Route::get('statistical/inventory/end-of-day',[StatisticalController::class, "inventoryStatsByExpiry"])->name('statistical.inventory');

});

require __DIR__ . '/auth.php';
