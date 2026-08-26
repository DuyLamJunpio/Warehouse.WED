<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\StorefrontNotifier;
use Illuminate\Http\Request;

/**
 * Cài đặt bán hàng: bật tắt hình thức thanh toán và cách tính phí giao hàng.
 *
 * Web bán hàng đọc thẳng cấu hình này qua API nên mỗi lần lưu đều báo cho nó
 * làm mới, nếu không khách vẫn thấy hình thức thanh toán vừa bị tắt.
 */
class SettingController extends Controller
{
    public function __construct(private StorefrontNotifier $notifier)
    {
    }

    public function index()
    {
        return view('settings.sales', [
            'sales' => Setting::sales(),
            'methods' => self::METHOD_LABELS,
        ]);
    }

    /** Tên hiển thị của từng hình thức thanh toán. */
    private const METHOD_LABELS = [
        'bank_transfer' => 'Chuyển khoản',
        'cod' => 'Thanh toán khi nhận hàng',
    ];

    public function saveSales(Request $request)
    {
        $request->validate([
            'sales' => 'required|array',
            'sales.*.shipping_fee' => 'nullable|integer|min:0',
            'sales.*.fee_payer' => 'required|in:' . Setting::PAYER_CUSTOMER . ',' . Setting::PAYER_SHOP,
            'sales.*.free_shipping_min_items' => 'nullable|integer|min:1',
        ]);

        $settings = [];
        foreach (array_keys(Setting::salesDefaults()) as $method) {
            $threshold = $request->input("sales.$method.free_shipping_min_items");

            $settings[$method] = [
                'enabled' => $request->boolean("sales.$method.enabled"),
                'free_shipping' => $request->boolean("sales.$method.free_shipping"),
                'shipping_fee' => (int) $request->input("sales.$method.shipping_fee", 0),
                'fee_payer' => $request->input("sales.$method.fee_payer"),
                // Chuỗi rỗng nghĩa là không đặt ngưỡng, khác hẳn với ngưỡng 0.
                'free_shipping_min_items' => ($threshold === null || $threshold === '')
                    ? null
                    : (int) $threshold,
            ];
        }

        // Tắt hết thì web bán hàng không còn đường nào để đặt hàng.
        if (!collect($settings)->contains(fn($method) => $method['enabled'])) {
            return response()->json([
                'error' => 'Phải bật ít nhất một hình thức thanh toán.',
            ], 422);
        }

        Setting::putSales($settings);
        $this->notifier->markDirty();

        return response()->json(['success' => 'Đã lưu cài đặt bán hàng.']);
    }
}
