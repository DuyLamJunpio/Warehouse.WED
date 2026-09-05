# Sao lưu database tự động sang Google Drive

Workflow `.github/workflows/daily-database-backup.yml` chạy mỗi ngày lúc 00:00 theo giờ Việt Nam. Có thể vào tab **Actions** trên GitHub và chọn **Daily database backup** > **Run workflow** để chạy thử ngay.

Mỗi lần chạy sẽ:

1. Kết nối Supabase qua Session Pooler và tạo PostgreSQL custom dump.
2. Mã hóa dump bằng AES-256 với passphrase riêng.
3. Tải file `.dump.gpg` lên một thư mục Google Drive chỉ định.

Không có bước xóa tự động: mọi backup trên Google Drive được giữ nguyên cho đến khi bạn chủ động đặt chính sách lưu giữ.

## Kết nối Google Drive cá nhân (khuyến nghị)

1. Tạo thư mục, ví dụ `Warehouse database backups`, trong Google Drive.
2. Mở Google Cloud Console, tạo hoặc chọn một project, bật **Google Drive API**.
3. Cấu hình **OAuth consent screen**, sau đó tạo OAuth 2.0 Client ID và Client Secret cho ứng dụng web.
4. Thêm `https://developers.google.com/oauthplayground` vào **Authorized redirect URIs** của OAuth client.
5. Nếu app còn ở chế độ **Testing**, thêm chính email Google Drive của bạn vào danh sách test users để tạo token thử nghiệm.
6. Mở [OAuth 2.0 Playground](https://developers.google.com/oauthplayground), chọn biểu tượng bánh răng và nhập OAuth Client ID/Secret của bạn, rồi authorize scope `https://www.googleapis.com/auth/drive.file` bằng chính tài khoản Google Drive nhận backup. Scope này chỉ cho phép workflow tạo và quản lý các file backup của chính nó.
7. Chuyển OAuth app sang **In production** trước khi lấy refresh token dùng lâu dài. Token của app ở chế độ Testing sẽ hết hạn sau 7 ngày.
8. Chọn **Exchange authorization code for tokens** và lưu `Refresh token` nhận được.
9. Lấy Folder ID từ URL thư mục Drive: phần nằm sau `/folders/`.

Refresh token và Client Secret là thông tin nhạy cảm. Không tải lên repository và không gửi qua chat. OAuth offline cho phép workflow tự làm mới access token khi không có người thao tác.

## Dùng Google Workspace Shared Drive (tùy chọn)

Nếu dùng Google Workspace Shared Drive, có thể dùng service account thay OAuth. Service account không có storage quota nên **không phù hợp với Google Drive cá nhân**; chỉ dùng với Shared Drive đã cấp quyền phù hợp.

## GitHub Actions Secrets

Vào GitHub repository > **Settings** > **Secrets and variables** > **Actions** > **New repository secret**, rồi thêm các secret sau:

| Tên secret | Giá trị |
| --- | --- |
| `BACKUP_DB_HOST` | `DB_HOST` trong Render hoặc Supabase Session Pooler host |
| `BACKUP_DB_PORT` | `5432` |
| `BACKUP_DB_DATABASE` | `postgres` |
| `BACKUP_DB_USERNAME` | `postgres.<project-ref>` — không dùng `postgres` đơn lẻ |
| `BACKUP_DB_PASSWORD` | Mật khẩu database Supabase, không có dấu ngoặc kép ngoài cùng |
| `GOOGLE_DRIVE_FOLDER_ID` | Folder ID ở bước trên |
| `GOOGLE_OAUTH_CLIENT_ID` | OAuth 2.0 Client ID của Google Cloud project |
| `GOOGLE_OAUTH_CLIENT_SECRET` | OAuth 2.0 Client Secret của Google Cloud project |
| `GOOGLE_OAUTH_REFRESH_TOKEN` | Refresh token từ OAuth 2.0 Playground |
| `BACKUP_ENCRYPTION_PASSPHRASE` | Một mật khẩu dài, riêng cho file backup; lưu ở password manager |

Khi dùng Google Workspace Shared Drive thay vì Google Drive cá nhân, bỏ ba secret `GOOGLE_OAUTH_*` và thêm `GOOGLE_SERVICE_ACCOUNT_JSON` chứa toàn bộ key JSON của service account.

Sau khi thêm đủ Secrets, chạy thủ công workflow một lần. Nếu thành công, Google Drive có file tên dạng `warehouse-YYYYMMDDTHHMMSSZ.dump.gpg`.

## Khôi phục khi cần

Không chạy các lệnh khôi phục lên database đang dùng. Hãy tạo database Supabase mới để kiểm tra trước.

```bash
gpg --decrypt --output warehouse.dump warehouse-YYYYMMDDTHHMMSSZ.dump.gpg
pg_restore --host <host> --port 5432 --username <user> --dbname <database> --clean --if-exists warehouse.dump
```

`pg_restore --clean` có thể xóa các object hiện có, vì vậy chỉ dùng lệnh này với database đích đã được kiểm tra và có xác nhận rõ ràng.
