# Database backups

Đây là vị trí tạm để lưu các bản backup PostgreSQL của database Warehouse.

## Tạo backup

Chạy lệnh sau tại thư mục gốc dự án (cần cài `pg_dump`):

```powershell
$env:PGPASSWORD = ((Get-Content .env | Select-String '^DB_PASSWORD=').Line -split '=', 2)[1].Trim().Trim('"').Trim("'")
$env:PGUSER = ((Get-Content .env | Select-String '^DB_USERNAME=').Line -split '=', 2)[1].Trim().Trim('"').Trim("'")
$env:PGHOST = ((Get-Content .env | Select-String '^DB_HOST=').Line -split '=', 2)[1].Trim().Trim('"').Trim("'")
$env:PGPORT = ((Get-Content .env | Select-String '^DB_PORT=').Line -split '=', 2)[1].Trim().Trim('"').Trim("'")
$env:PGDATABASE = ((Get-Content .env | Select-String '^DB_DATABASE=').Line -split '=', 2)[1].Trim().Trim('"').Trim("'")
pg_dump --format=custom --file "backups/database/warehouse-$(Get-Date -Format yyyyMMdd-HHmmss).dump"
Remove-Item Env:PGPASSWORD
Remove-Item Env:PGUSER
Remove-Item Env:PGHOST
Remove-Item Env:PGPORT
Remove-Item Env:PGDATABASE
```

Lưu ý: `DB_USERNAME` phải có dạng `postgres.<project-ref>` khi dùng Supabase pooler. Không thay bằng `postgres` đơn lẻ.

Sau khi tạo xong, hãy sao chép file `.dump` sang OneDrive, Google Drive, ổ cứng ngoài hoặc nơi lưu trữ khác. Không lưu bản backup duy nhất trong cùng thư mục dự án.

Các file dump trong thư mục này không được commit vào Git.
