<?php

return [
    /*
     * Các module tùy chọn được giữ nguyên mã nguồn nhưng tắt mặc định trong
     * bản quản trị chung. Brand có nhu cầu có thể bật bằng biến môi trường.
     */
    'print_studio' => (bool) env('PRINT_STUDIO_ENABLED', false),
];
