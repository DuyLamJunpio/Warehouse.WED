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
                sans: ['Be Vietnam Pro', 'Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Cormorant Garamond', 'serif'],
            },

            colors: {
                /** Màu đất–rừng cho giao diện quản trị RUNGU. */
                primary: {
                    50: '#f3f6ef',
                    100: '#e3ebdb',
                    200: '#c8d8ba',
                    300: '#a7c28f',
                    400: '#83a365',
                    500: '#628449',
                    600: '#496a35',
                    700: '#39552b',
                    800: '#2e4424',
                    900: '#26391f',
                    950: '#142010',
                },
                // Giữ các class indigo cũ để không phải đổi hàng trăm view,
                // nhưng toàn bộ giao diện nay dùng cùng bảng màu RUNGU.
                indigo: {
                    50: '#f3f6ef',
                    100: '#e3ebdb',
                    200: '#c8d8ba',
                    300: '#a7c28f',
                    400: '#83a365',
                    500: '#628449',
                    600: '#496a35',
                    700: '#39552b',
                    800: '#2e4424',
                    900: '#26391f',
                    950: '#142010',
                },
            },
        },
    },

    plugins: [
        forms,
        flowbite,
    ],
};
