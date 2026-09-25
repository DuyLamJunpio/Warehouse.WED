#!/bin/sh
set -eu

# Render khởi động một container mới cho mỗi lần deploy. Chạy migration trước
# khi Apache nhận traffic để endpoint checkout không thể dùng model/table mới
# khi schema PostgreSQL chưa được cập nhật. `migrate --force` chỉ áp dụng các
# migration chưa có trong bảng migrations, không xóa hay làm rỗng dữ liệu.
php artisan migrate --force --no-interaction

exec apache2-foreground
