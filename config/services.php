<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
     * Bí mật dùng chung với web bán hàng (Next.js). Web bán hàng kiểm tra chữ ký
     * PayOS xong mới gọi sang đây, nên chặng này chỉ cần xác thực máy-với-máy.
     */
    'storefront' => [
        'secret' => env('STOREFRONT_WEBHOOK_SECRET'),

        // Địa chỉ web bán hàng, để báo nó làm mới catalogue sau khi lưu sản
        // phẩm. Bỏ trống thì web vẫn tự đọc lại mỗi phút, chỉ là chậm hơn.
        'url' => env('STOREFRONT_URL'),

        // Phải khớp PAYMENT_WINDOW_MINUTES bên webstore (lib/checkout.ts).
        'payment_window_minutes' => (int) env('STOREFRONT_PAYMENT_WINDOW_MINUTES', 15),

        // Ân hạn trước khi tự huỷ: webstore đẩy đơn sang TRƯỚC khi tạo link PayOS,
        // và webhook báo đã trả tiền có thể tới muộn hơn hạn một chút.
        'expiry_grace_minutes' => (int) env('STOREFRONT_EXPIRY_GRACE_MINUTES', 5),
    ],

];
