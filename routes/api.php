<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\SupplierController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\InvoiceController;
use App\Http\Controllers\API\LocationController;
use App\Http\Controllers\API\StatisticalController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    //product
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products/create', [ProductController::class, 'store']);
    Route::post('/products/update/{id}', [ProductController::class, 'edit']);
    Route::get('/products/get-id/{id}', [ProductController::class, 'getProductById']);
    Route::get('/products/delete-image/{id}', [ProductController::class, 'deleteImageUrl']);
    Route::get('/products/search', [ProductController::class, 'search']);
    Route::get('/products/filter-supplier', [ProductController::class, 'filterBySupplier']);
    Route::get('/products/get-product-by-supplier', [ProductController::class, 'getProductsBySupplier']);
    Route::get('/products/filter-category', [ProductController::class, 'filterByCategory']);
    Route::get('/products/filter-status', [ProductController::class, 'filterByStatus']);
    Route::get('/products/getBatch/{id}', [ProductController::class, 'getProductExpiries']);
    Route::delete('/products/deleteBatch/{productId}/{expiryId}', [ProductController::class, 'updateOrDeleteExpiry']);

    //supplier
    Route::get('/suppliers', [SupplierController::class, 'index']);
    Route::post('/suppliers/create', [SupplierController::class, 'store']);
    Route::post('/suppliers/update/{id}', [SupplierController::class, 'update']);
    Route::get('/suppliers/search', [SupplierController::class, 'search']);
    Route::get('/suppliers/get-id/{id}', [SupplierController::class, 'getSupplier']);
    Route::get('/suppliers/delete/{id}', [SupplierController::class, 'destroy']);

    //customers
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers/create', [CustomerController::class, 'store']);
    Route::post('/customers/update/{id}', [CustomerController::class, 'edit']);
    Route::get('/customers/search', [CustomerController::class, 'search']);
    Route::get('/customers/get-id/{id}', [CustomerController::class, 'getCustomerId']);
    Route::get('/customers/delete/{id}', [CustomerController::class, 'destroy']);

    //category
    Route::get('/category', [CategoryController::class, 'index']);
    Route::post('/category/create', [CategoryController::class, 'store']);
    Route::post('/category/update/{id}', [CategoryController::class, 'update']);
    Route::get('/category/search', [CategoryController::class, 'search']);
    Route::get('/category/get-id/{id}', [CategoryController::class, 'getCategory']);
    Route::get('/category/delete/{id}', [CategoryController::class, 'destroy']);

    //profile
    Route::get('/staff', [AuthController::class, 'staff']);
    Route::get('/admin/{id}', [AuthController::class, 'admin']);
    Route::get('/get-staff/{id}', [AuthController::class, 'getStaff']);
    Route::patch('profile/update', [AuthController::class, 'update']);
    Route::post('profile/upload-avatar', [AuthController::class, 'upload']);
    Route::post('profile/delete/{id}', [AuthController::class, 'destroy']);
    Route::post('profile/update-password', [AuthController::class, 'password_update']);

    //invoice
    Route::get('/invoice', [InvoiceController::class, 'index']);
    Route::get('/invoice/filter/{invoiceType?}/{payStatus?}', [InvoiceController::class, 'filterByTypeAndStatus']);
    Route::get('/invoice/filter-payment/{value?}', [InvoiceController::class, 'filter_pay_status']);
    Route::post('/invoice/create', [InvoiceController::class, 'store']);
    Route::get('/invoice/get-invoice/{id}', [InvoiceController::class, 'getInvoiceDetails']);
    Route::get('/invoice/search', [InvoiceController::class, 'searchInvoice']);
    Route::post('/invoice/update/{id}', [InvoiceController::class, 'update']);
    Route::get('/invoice/delete/{id}', [InvoiceController::class, 'destroy']);

    //location
    Route::get('/location/filter', [LocationController::class, 'index']);
    Route::get('/location/auto-create', [LocationController::class, 'addNewLocationAutomatically']);
    Route::get('/location/get-zone', [LocationController::class, 'getZone']);
    Route::get('/location/get-shelf', [LocationController::class, 'getShelf']);
    Route::get('/location/get-level', [LocationController::class, 'getLevel']);

    //statistical
    //thống kê hóa đơn xuất
    Route::get('statistical/import-invoice', [StatisticalController::class, "showInventoryStats"]);
    Route::get('statistical/import-invoice/today', [StatisticalController::class, "statsToday"]);
    Route::get('statistical/import-invoice/yesterday', [StatisticalController::class, "statsYesterday"]);
    Route::get('statistical/import-invoice/this-month', [StatisticalController::class, "statsThisMonth"]);
    Route::get('statistical/import-invoice/last-month', [StatisticalController::class, "statsLastMonth"]);
    //thống kê hóa đơn nhập
    Route::get('statistical/export-invoice', [StatisticalController::class, "showInventoryExportStats"]);
    Route::get('statistical/export-invoice/today', [StatisticalController::class, "statsExportToday"]);
    Route::get('statistical/export-invoice/yesterday', [StatisticalController::class, "statsExportYesterday"]);
    Route::get('statistical/export-invoice/this-month', [StatisticalController::class, "statsExportThisMonth"]);
    Route::get('statistical/export-invoice/last-month', [StatisticalController::class, "statsExportLastMonth"]);

    //số lượng tồn kho cuối ngày
    Route::get('statistical/inventory/end-of-day', [StatisticalController::class, "inventoryStatsByExpiry"]);

    //register
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/upload-images', [ProductController::class, 'uploadBatchImages']);
Route::get('/update_product_status', [ProductController::class, 'updateProductStatus']);
