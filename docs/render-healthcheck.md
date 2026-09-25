# Health check Render cho QLBH

QLBH có endpoint `GET /healthz`. Endpoint trả `200 {"status":"ok"}` chỉ khi ứng dụng khởi động xong và đã kết nối được cơ sở dữ liệu.

Trong dịch vụ Render của `api.rungu.com.vn`, vào **Settings** → **Health Check Path** và đặt:

```
/healthz
```

Lưu cấu hình rồi triển khai lại dịch vụ. Render sẽ giữ traffic ở phiên bản cũ cho đến khi phiên bản mới trả `200` tại endpoint này, nhờ đó khách không gặp trang HTML/502 trong lúc triển khai.
