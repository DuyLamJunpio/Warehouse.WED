<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\SupplierController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CheckoutController;
use App\Http\Controllers\API\PrintStorefrontController;
use App\Http\Controllers\API\StorefrontController;
use App\Http\Controllers\API\StorefrontOrderController;
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
    Route::get('/products/variants/{id}', [ProductController::class, 'getProductVariants']);
    Route::delete('/products/variants/{productId}/{variantId}', [ProductController::class, 'updateOrDeleteVariant']);

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
    Route::get('statistical/inventory/end-of-day', [StatisticalController::class, "inventoryStatsByVariant"]);

    //register
    Route::post('/register', [AuthController::class, 'register']);

    //upload hàng loạt + đồng bộ trạng thái sản phẩm
    Route::post('/upload-images', [ProductController::class, 'uploadBatchImages']);
    Route::get('/update_product_status', [ProductController::class, 'updateProductStatus']);
});

Route::post('/login', [AuthController::class, 'login']);

/*
 * Đặt hàng từ web bán hàng - công khai vì khách không có tài khoản.
 * Giới hạn số lần gọi để tránh bị spam đơn rác; mọi số tiền do server tự tính.
 */
Route::middleware('throttle:20,1')->group(function () {
    // Invoice chỉ được tạo từ luồng PayOS đã xác nhận. Webstore gọi controller này
    // qua StorefrontOrderController::fulfill bằng request nội bộ, còn HTTP trực tiếp phải có bí mật.
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('storefront.secret');
    Route::post('/checkout/check-stock', [CheckoutController::class, 'checkStock']);
    // Chi web ban hang duoc goi: no da xac thuc chu ky PayOS truoc do.
    Route::post('/checkout/{orderCode}/paid', [CheckoutController::class, 'markPaid'])
        ->middleware('storefront.secret');
});

/*
 * Catalogue công khai cho web bán hàng đọc. Chỉ đọc, và chỉ những trường
 * hiển thị ngoài cửa hàng - không có giá nhập hay nhà cung cấp.
 */
Route::middleware('throttle:120,1')->prefix('storefront')->group(function () {
    Route::get('/products', [StorefrontController::class, 'products']);
    Route::get('/products/{slug}', [StorefrontController::class, 'product']);
    Route::get('/categories', [StorefrontController::class, 'categoriesIndex']);
    Route::get('/content', [StorefrontController::class, 'content']);

    /*
     * Studio đặt in. Hai đường này chỉ đọc và không mang dữ liệu khách nào:
     * catalogue là phôi và bảng giá đang áp dụng, quote là báo giá lại từ máy
     * chủ. Studio tự tính giá bằng TypeScript cho mượt tay, nhưng con số tính
     * tiền thật luôn dựng lại ở đây.
     */
    Route::get('/print/catalogue', [PrintStorefrontController::class, 'catalogue']);
    Route::post('/print/quote', [PrintStorefrontController::class, 'quote']);
});

/*
 * Ghi của module in: nhận file thiết kế và chốt một mẫu áo.
 *
 * Nằm sau bí mật dùng chung vì chúng ghi file lên Supabase và tạo bản ghi chờ
 * duyệt - để ngỏ là ai cũng đổ rác vào kho ảnh được. Web bán hàng gọi từ route
 * handler của Next, không phải từ trình duyệt.
 */
Route::middleware(['throttle:60,1', 'storefront.secret'])
    ->prefix('storefront/print')
    ->group(function () {
        Route::post('/assets', [PrintStorefrontController::class, 'storeAsset']);
        Route::post('/designs', [PrintStorefrontController::class, 'storeDesign']);
        // Giá đã đóng băng của một mẫu — web bán hàng đọc lại lúc dựng giỏ hàng
        // và lúc thanh toán thay vì tin con số nào trong localStorage.
        Route::get('/designs/{code}', [PrintStorefrontController::class, 'showDesign']);
    });

/*
 * Cho web bán hàng lưu đơn của trang thanh toán: mã trên URL, mã QR, hạn chuyển
 * khoản, trạng thái PayOS. Bên đó không có cơ sở dữ liệu nào, và thư mục tạm của
 * máy chủ Vercel bị xoá sau vài phút - đơn mất là khách đang trả tiền thì trang
 * đơn hàng thành 404.
 *
 * Payload có tên, số điện thoại và địa chỉ khách, nên cả nhóm nằm sau bí mật
 * dùng chung. Hạn gọi nới rộng vì trang thanh toán hỏi lại mỗi 4 giây trong lúc
 * khách còn ở trong app ngân hàng.
 */
Route::middleware(['throttle:600,1', 'storefront.secret'])
    ->prefix('storefront/orders')
    ->group(function () {
        Route::post('/', [StorefrontOrderController::class, 'store']);
        // Đặt trước {ref} để mã PayOS không bị bắt làm mã trên URL.
        Route::get('/by-code/{orderCode}', [StorefrontOrderController::class, 'showByCode'])
            ->whereNumber('orderCode');

        Route::prefix('{ref}')->whereAlphaNumeric('ref')->group(function () {
            Route::get('/', [StorefrontOrderController::class, 'show']);
            Route::patch('/', [StorefrontOrderController::class, 'update']);
            // Chỉ gọi sau khi PayOS đã xác nhận. Endpoint tạo hoá đơn thật trong
            // transaction, nên webhook và trang khách kiểm tra song song không sinh đơn trùng.
            Route::post('/fulfill', [StorefrontOrderController::class, 'fulfill']);
            Route::post('/email-claim', [StorefrontOrderController::class, 'claimEmail']);
            Route::delete('/email-claim', [StorefrontOrderController::class, 'releaseEmail']);
        });
    });
