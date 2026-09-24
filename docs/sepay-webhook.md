# Kết nối SePay cho đơn web RUNGU

Web bán hàng tạo đơn trong QLBH. Khách chuyển đúng tổng tiền với nội dung là mã đơn `DH...`. SePay báo giao dịch về QLBH và QLBH ghi nhận `pay_status = 1` khi **đồng thời** đúng API key, tài khoản nhận, mã đơn, số tiền và chiều tiền vào. Đơn vẫn ở trạng thái chờ xác nhận để nhân viên xử lý vận hành.

## Kích hoạt

1. Triển khai mã QLBH và web bán hàng, rồi chạy riêng hai migration bổ sung `2026_09_24_000000_add_checkout_idempotency_to_invoices.php` và `2026_09_24_000001_create_sepay_transactions_table.php`. Không chạy lại hay xóa dữ liệu cũ.
2. Trong QLBH > Cài đặt bán hàng, nhập tài khoản ngân hàng đang kết nối với SePay. Số tài khoản tại đây phải trùng `accountNumber` trong webhook.
3. Tạo một khóa ngẫu nhiên đủ mạnh, đặt biến môi trường `SEPAY_WEBHOOK_API_KEY` trên máy chủ QLBH và cấu hình cùng khóa đó cho webhook trong SePay. Không đưa khóa vào web bán hàng hoặc trình duyệt. Nếu máy chủ cache cấu hình Laravel, làm mới cache sau khi đặt biến.
4. Trong SePay, chọn **Có tiền vào**, tài khoản ở bước 2, **API Key**, JSON, URL `https://api.rungu.com.vn/api/webhooks/sepay`. Đặt cấu trúc nhận diện mã thanh toán: tiền tố `DH`, hậu tố 10 ký tự chữ và số; hoặc cấu hình để SePay chuyển nguyên nội dung chuyển khoản, vì QLBH cũng đọc mã đơn từ `content`.
5. Dùng chức năng **Gửi thử** của SePay với mã đơn thử trong cơ sở dữ liệu thử. Sau đó kiểm tra giao dịch thật bằng đơn giá nhỏ nếu muốn xác nhận kết nối ngân hàng.

Mỗi `id` giao dịch SePay chỉ được xử lý một lần. Giao dịch sai số tiền, tài khoản, mã đơn hoặc chuyển vào đơn đã thanh toán được ghi vào `sepay_transactions` và log để nhân viên đối soát. Tiền về sau khi đơn hủy/hoàn sẽ được đánh dấu cần xử lý, không tự tái tạo đơn hay trừ kho.

## Thông báo Telegram

Đặt các biến môi trường này trên máy chủ QLBH (không đặt ở web bán hàng):

```env
TELEGRAM_BOT_TOKEN=
TELEGRAM_CHAT_ID=-1004301319652
TELEGRAM_TOPIC_ID=
```

Sau khi thay đổi biến môi trường, chạy `php artisan config:clear` hoặc làm mới
cache cấu hình. QLBH sẽ gửi vào nhóm:

- Khi tạo đơn: mã đơn, khách, sản phẩm, phương thức thanh toán, trạng thái và tổng tiền.
- Khi SePay nhận tiền đúng: số tiền vào, số dư sau giao dịch (nếu SePay gửi), mã đơn, nội dung chuyển khoản và mã giao dịch.
- Khi giao dịch sai hoặc không khớp: số tiền, chiều tiền, nội dung và lý do cần đối soát.
