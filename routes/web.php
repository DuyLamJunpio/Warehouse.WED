<?php

use App\Http\Controllers\categoryController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PrintAssetController;
use App\Http\Controllers\PrintBlankController;
use App\Http\Controllers\PrintDesignController;
use App\Http\Controllers\PrintPricingController;
use App\Http\Controllers\PrintTechniqueController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StatisticalController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('auth.login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/categories', [categoryController::class, 'index'])->name('categories');
    Route::get('/categories/data', [categoryController::class, 'getData'])->name('categories.data');
    Route::post('/categories/add', [categoryController::class, 'store'])->name('categories.add');
    Route::post('/categories/edit/{id}', [categoryController::class, 'edit'])->name('categories.edit');
    Route::delete('/categories/delete/{id}', [categoryController::class, 'destroy'])->name('categories.delete');
    Route::post('/categories/reorder/{id}', [categoryController::class, 'reorder'])->name('categories.reorder');
    Route::get('/search-categories', [categoryController::class, 'search'])->name('categories.search');

    Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier');
    Route::get('/supplier/data', [SupplierController::class, 'getData'])->name('supplier.data');
    Route::post('/supplier/add', [SupplierController::class, 'store'])->name('supplier.add');
    Route::get('/supplier/getsupplier/{id}', [SupplierController::class, 'getSupplierId'])->name('supplier.getsupplier');
    Route::post('/supplier/edit/{id}', [SupplierController::class, 'edit'])->name('supplier.edit');
    Route::delete('/supplier/delete/{id}', [SupplierController::class, 'destroy'])->name('supplier.delete');
    Route::get('/search-suppliers', [SupplierController::class, 'search'])->name('suppliers.search');

    Route::get('/product', [ProductController::class, 'index'])->name('product');
    Route::get('/product/data', [ProductController::class, 'getData'])->name('product.data');
    Route::post('/product/add', [ProductController::class, 'store'])->name('product.add');
    Route::get('/product/get-product/{id}', [ProductController::class, 'getProductById'])->name('product.getproduct');
    Route::post('/product/edit/{id}', [ProductController::class, 'edit'])->name('product.edit');
    Route::get('/get-image/{id}', [ProductController::class, 'getImageUrl']);
    Route::delete('/delete-image/{id}', [ProductController::class, 'deleteImageUrl']);
    Route::delete('/product/delete/{id}', [ProductController::class, 'destroy'])->name('product.delete');
    Route::get('/search-products', [ProductController::class, 'search'])->name('product.search');

    // Quản lý đơn hàng bán (invoices có invoice_type = 1)
    Route::get('/order', [OrderController::class, 'index'])->name('order');
    Route::get('/order/data', [OrderController::class, 'getData'])->name('order.data');
    // Lập đơn tại quầy ngay trong trang đơn hàng. Phải khai trước '/order/{id}'
    // để route đó không nuốt mất đường dẫn.
    Route::get('/order/variants', [OrderController::class, 'searchVariants'])->name('order.variants');
    Route::post('/order/store', [OrderController::class, 'store'])->name('order.store');
    Route::get('/order/{id}', [OrderController::class, 'show'])->name('order.show');
    Route::post('/order/{id}/status', [OrderController::class, 'updateStatus'])->name('order.status');
    Route::get('/order/{id}/print', [OrderController::class, 'print'])->name('order.print');

    // Nội dung trang chủ web bán hàng: slide hero, chữ chạy, tiêu đề các khối
    // Cài đặt bán hàng: hình thức thanh toán và phí giao hàng.
    Route::get('/settings/sales', [SettingController::class, 'index'])->name('settings.sales');
    Route::post('/settings/sales', [SettingController::class, 'saveSales'])->name('settings.sales.save');

    /*
     * Module in áo theo yêu cầu.
     *
     * Bảng giá sửa vào bản nháp, chỉ có hiệu lực với khách sau khi bấm Xuất bản
     * — xem App\Services\PrintPricing. Kỹ thuật và bậc khổ chỉ bật/tắt, không
     * có route xoá: đơn cũ và quy tắc giá cũ đang trỏ vào chúng.
     */
    Route::prefix('print')->name('print.')->group(function () {
        Route::get('/pricing', [PrintPricingController::class, 'index'])->name('pricing');
        Route::post('/pricing/draft', [PrintPricingController::class, 'saveDraft'])->name('pricing.draft');
        Route::post('/pricing/publish', [PrintPricingController::class, 'publish'])->name('pricing.publish');
        Route::post('/pricing/simulate', [PrintPricingController::class, 'simulate'])->name('pricing.simulate');

        Route::get('/techniques', [PrintTechniqueController::class, 'index'])->name('techniques');
        Route::post('/techniques', [PrintTechniqueController::class, 'store'])->name('techniques.store');
        Route::post('/techniques/{technique}', [PrintTechniqueController::class, 'update'])->name('techniques.update');
        Route::post('/techniques/{technique}/toggle', [PrintTechniqueController::class, 'toggle'])->name('techniques.toggle');

        Route::post('/tiers', [PrintTechniqueController::class, 'storeTier'])->name('tiers.store');
        Route::post('/tiers/{tier}', [PrintTechniqueController::class, 'updateTier'])->name('tiers.update');
        Route::post('/tiers/{tier}/toggle', [PrintTechniqueController::class, 'toggleTier'])->name('tiers.toggle');

        Route::get('/blanks', [PrintBlankController::class, 'index'])->name('blanks');
        Route::post('/blanks', [PrintBlankController::class, 'store'])->name('blanks.store');
        Route::post('/blanks/{blank}', [PrintBlankController::class, 'update'])->name('blanks.update');
        Route::post('/blanks/{blank}/toggle', [PrintBlankController::class, 'toggle'])->name('blanks.toggle');
        // Xoá hẳn — chỉ đi được khi chưa thiết kế nào trỏ vào, xem controller.
        Route::delete('/blanks/{blank}', [PrintBlankController::class, 'destroy'])->name('blanks.destroy');
        Route::delete('/blank-colors/{color}', [PrintBlankController::class, 'destroyColor'])->name('blanks.colors.destroy');
        Route::post('/blanks/{blank}/mockups', [PrintBlankController::class, 'uploadMockup'])->name('blanks.mockups.store');
        Route::delete('/mockups/{mockup}', [PrintBlankController::class, 'destroyMockup'])->name('mockups.destroy');

        Route::get('/designs', [PrintDesignController::class, 'index'])->name('designs');
        Route::get('/designs/{design}', [PrintDesignController::class, 'show'])->name('designs.show');
        Route::get('/designs/{design}/svg', [PrintDesignController::class, 'svg'])->name('designs.svg');
        Route::post('/designs/{design}/review', [PrintDesignController::class, 'review'])->name('designs.review');

        Route::get('/library', [PrintAssetController::class, 'index'])->name('library');
        Route::post('/library', [PrintAssetController::class, 'store'])->name('library.store');
        Route::post('/library/{asset}', [PrintAssetController::class, 'update'])->name('library.update');
        Route::post('/library/{asset}/toggle', [PrintAssetController::class, 'toggle'])->name('library.toggle');

        Route::post('/fonts', [PrintAssetController::class, 'storeFont'])->name('fonts.store');
        Route::post('/fonts/{font}', [PrintAssetController::class, 'updateFont'])->name('fonts.update');
        Route::post('/fonts/{font}/toggle', [PrintAssetController::class, 'toggleFont'])->name('fonts.toggle');

        // Hàng đợi duyệt thiết kế — chốt chặn trước khi đưa vào xưởng.
        Route::get('/designs', [PrintDesignController::class, 'index'])->name('designs');
        Route::get('/designs/{design}', [PrintDesignController::class, 'show'])->name('designs.show');
        Route::post('/designs/{design}/review', [PrintDesignController::class, 'review'])->name('designs.review');

        Route::get('/library', [PrintAssetController::class, 'index'])->name('library');
        Route::post('/library', [PrintAssetController::class, 'store'])->name('library.store');
        Route::post('/library/{asset}', [PrintAssetController::class, 'update'])->name('library.update');
        Route::post('/library/{asset}/toggle', [PrintAssetController::class, 'toggle'])->name('library.toggle');

        Route::post('/fonts', [PrintAssetController::class, 'storeFont'])->name('fonts.store');
        Route::post('/fonts/{font}', [PrintAssetController::class, 'updateFont'])->name('fonts.update');
        Route::post('/fonts/{font}/toggle', [PrintAssetController::class, 'toggleFont'])->name('fonts.toggle');
    });

    Route::get('/content', [ContentController::class, 'index'])->name('content');
    Route::post('/content/banner', [ContentController::class, 'storeBanner'])->name('content.banner.store');
    Route::post('/content/banner/{id}', [ContentController::class, 'updateBanner'])->name('content.banner.update');
    Route::delete('/content/banner/{id}', [ContentController::class, 'destroyBanner'])->name('content.banner.destroy');
    Route::post('/content/banner/{id}/reorder', [ContentController::class, 'reorderBanner'])->name('content.banner.reorder');
    Route::post('/content/marquee', [ContentController::class, 'saveMarquee'])->name('content.marquee');
    Route::post('/content/headings', [ContentController::class, 'saveHeadings'])->name('content.headings');
    Route::post('/content/collection/{id?}', [ContentController::class, 'saveCollection'])->name('content.collection');
    Route::delete('/content/collection/{id}', [ContentController::class, 'destroyCollection'])->name('content.collection.destroy');

    // Quản lý tồn kho theo biến thể size/màu
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::get('/inventory/data', [InventoryController::class, 'getData'])->name('inventory.data');
    Route::post('/inventory/adjust/{id}', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::get('/inventory/history/{id}', [InventoryController::class, 'history'])->name('inventory.history');
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
    Route::delete('/customer/delete/{id}', [CustomerController::class, 'destroy'])->name('customer.delete');
    Route::get('/search-customer', [CustomerController::class, 'search'])->name('customer.search');
    Route::get('/customer/{id}/profile', [CustomerController::class, 'show'])->name('customer.show');
    Route::post('/customer/{id}/note', [CustomerController::class, 'updateNote'])->name('customer.note');

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
    Route::delete('/invoice/delete/{id}', [InvoiceController::class, 'destroy'])->name('invoice.delete');
    // Route::get('/search-invoices', [SupplierController::class,'search'])->name('suppliers.search');

    // Quản lý tài khoản: chỉ admin (role = 1), theo quy ước ở app/Policies/UserPolicy.php
    Route::middleware('role:1')->group(function () {
        Route::get('/account', [SupplierController::class, 'index'])->name('account');
        Route::get('/account/data', [SupplierController::class, 'getData'])->name('account.data');
        Route::post('/account/add', [SupplierController::class, 'store'])->name('account.add');
        Route::post('/account/edit/{id}', [SupplierController::class, 'edit'])->name('account.edit');
        Route::delete('/account/delete/{id}', [SupplierController::class, 'destroy'])->name('account.delete');
        Route::get('/search-account', [SupplierController::class, 'search'])->name('account.search');
    });

    // Quản lý vị trí trong kho đã bỏ: shop quần áo không xếp hàng theo kệ/tầng.
    // Controller, model và bảng product_locations vẫn giữ nguyên, chưa xoá dữ liệu.

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
    Route::get('statistical/inventory/end-of-day',[StatisticalController::class, "inventoryStatsByVariant"])->name('statistical.inventory');

});

require __DIR__ . '/auth.php';
