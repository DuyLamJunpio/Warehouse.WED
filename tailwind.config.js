import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import flowbite from 'flowbite/plugin';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './node_modules/flowbite/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                /**
                 * Màu chủ đạo của giao diện quản trị.
                 *
                 * Giao diện Flowbite Admin dùng primary-300..800 ở 176 chỗ, nhưng
                 * bảng màu này chưa bao giờ được khai, nên Tailwind không sinh ra
                 * class nào cho chúng: mọi nút "Cập nhật", "Thêm mới" đều mất nền
                 * và thành chữ trắng trên nền trắng.
                 */
                primary: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                },
            },
        },
    },

    plugins: [
        forms,
        flowbite,
    ],
};
