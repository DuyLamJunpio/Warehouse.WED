<!doctype html>
<html lang="vi">
<body style="margin:0;background:#f8fafc;color:#0f172a;font-family:Arial,sans-serif;line-height:1.6">
    <div style="max-width:640px;margin:24px auto;padding:28px;background:#fff;border:1px solid #e2e8f0;border-radius:14px">
        <h1 style="margin:0 0 8px;color:#312e81;font-size:22px">
            {{ $order->payment_method === 'bank_transfer' && (int) $order->pay_status === 1
                ? 'RUNGU đã xác nhận thanh toán'
                : 'RUNGU đã nhận đơn hàng' }}
        </h1>
        <p>Xin chào {{ $order->customer?->customer_name ?: 'bạn' }},</p>
        @if ($order->payment_method === 'bank_transfer' && (int) $order->pay_status === 1)
            <p>RUNGU đã nhận đủ khoản chuyển khoản cho đơn <strong>#{{ $order->order_code }}</strong>. Shop sẽ tiếp tục xử lý đơn hàng cho bạn.</p>
        @else
            <p>Cảm ơn bạn đã mua hàng. Đơn <strong>#{{ $order->order_code }}</strong> đã được ghi nhận.</p>
        @endif

        <table style="width:100%;border-collapse:collapse;margin:20px 0;font-size:14px">
            <thead>
                <tr style="border-bottom:2px solid #e2e8f0;text-align:left">
                    <th style="padding:8px 0">Sản phẩm</th>
                    <th style="padding:8px 0;text-align:center">SL</th>
                    <th style="padding:8px 0;text-align:right">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->productInvoices as $line)
                    <tr style="border-bottom:1px solid #e2e8f0">
                        <td style="padding:8px 0">{{ $line->product?->product_name ?: 'Sản phẩm' }}<br><small style="color:#64748b">{{ $line->variant?->label }}</small></td>
                        <td style="padding:8px 0;text-align:center">{{ $line->quantity }}</td>
                        <td style="padding:8px 0;text-align:right">{{ number_format((int) $line->line_total, 0, ',', '.') }} ₫</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p style="text-align:right;font-size:18px"><strong>Tổng cộng: {{ number_format((int) $order->total_amount, 0, ',', '.') }} ₫</strong></p>
        <p>Trạng thái hiện tại: <strong>{{ $order->order_status_label ?: $order->order_status }}</strong>.</p>
        <p style="color:#64748b;font-size:13px">Nếu cần hỗ trợ, hãy trả lời email này hoặc liên hệ RUNGU.</p>
    </div>
</body>
</html>
