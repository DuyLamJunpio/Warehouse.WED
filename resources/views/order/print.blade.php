<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Phiếu giao hàng {{ $order->order_code ?? '#' . $order->id }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 13px;
            color: #111;
            margin: 0;
            padding: 24px;
        }

        .sheet {
            max-width: 720px;
            margin: 0 auto;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 4px;
        }

        .muted {
            color: #666;
            font-size: 12px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 20px;
        }

        .box {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 12px;
        }

        .box h3 {
            margin: 0 0 6px;
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        th,
        td {
            padding: 8px 6px;
            border-bottom: 1px solid #eee;
        }

        th {
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #666;
            border-bottom: 1px solid #999;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .totals {
            margin-left: auto;
            width: 260px;
        }

        .totals td {
            border: none;
            padding: 3px 0;
        }

        .grand {
            font-size: 16px;
            font-weight: bold;
            border-top: 1px solid #999;
            padding-top: 6px;
        }

        .sign {
            display: flex;
            justify-content: space-between;
            margin-top: 48px;
            text-align: center;
        }

        .sign div {
            width: 45%;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="sheet">
        <button class="no-print" onclick="window.print()"
            style="float:right;padding:8px 16px;cursor:pointer">In phiếu</button>

        <h1>PHIẾU GIAO HÀNG</h1>
        <p class="muted">
            Mã đơn: <strong>{{ $order->order_code ?? '#' . $order->id }}</strong> ·
            Ngày lập: {{ $order->created_at->format('d/m/Y H:i') }} ·
            Trạng thái: {{ $order->order_status_label ?? 'Chưa có trạng thái' }}
        </p>

        <div class="row">
            <div class="box">
                <h3>Người nhận</h3>
                <div><strong>{{ $order->shipping_name ?? ($order->customer->customer_name ?? 'Khách lẻ') }}</strong>
                </div>
                <div>{{ $order->shipping_phone ?? '—' }}</div>
                <div>{{ $order->shipping_address ?? 'Không có địa chỉ giao' }}</div>
            </div>
            <div class="box">
                <h3>Thông tin đơn</h3>
                <div>Thanh toán: {{ $order->payment_method ?? '—' }}</div>
                <div>Nhân viên: {{ $order->user->name ?? '—' }}</div>
                @if ($order->note)
                    <div>Ghi chú: {{ $order->note }}</div>
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Size / Màu</th>
                    <th class="center">SL</th>
                    <th class="right">Đơn giá</th>
                    <th class="right">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->productInvoices as $line)
                    <tr>
                        <td>{{ $line->product->product_name ?? 'Sản phẩm đã xóa' }}</td>
                        <td>{{ $line->variant->label ?? '—' }}</td>
                        <td class="center">{{ $line->quantity }}</td>
                        <td class="right">{{ number_format($line->unit_price, 0, ',', '.') }} ₫</td>
                        <td class="right">{{ number_format($line->line_total, 0, ',', '.') }} ₫</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td>Tiền hàng</td>
                <td class="right">{{ number_format($order->subtotal, 0, ',', '.') }} ₫</td>
            </tr>
            <tr>
                <td>Phí giao hàng</td>
                <td class="right">{{ number_format($order->shipping_fee, 0, ',', '.') }} ₫</td>
            </tr>
            <tr>
                <td>Chiết khấu</td>
                <td class="right">{{ number_format($order->discount ?? 0, 0, ',', '.') }} ₫</td>
            </tr>
            <tr class="grand">
                <td>TỔNG CỘNG</td>
                <td class="right">{{ number_format($order->total_amount, 0, ',', '.') }} ₫</td>
            </tr>
        </table>

        <div class="sign">
            <div>
                <strong>Người gửi</strong>
                <p class="muted">(Ký, ghi rõ họ tên)</p>
            </div>
            <div>
                <strong>Người nhận</strong>
                <p class="muted">(Ký, ghi rõ họ tên)</p>
            </div>
        </div>
    </div>
</body>

</html>
