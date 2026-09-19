<?php

return [
    /*
     * Các module không còn phục vụ vận hành hiện tại được giữ nguyên dữ liệu,
     * nhưng không hiện trong trang quản trị hay storefront.
     */
    'print_studio' => (bool) env('PRINT_STUDIO_ENABLED', false),
];
