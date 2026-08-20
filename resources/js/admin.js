/**
 * Tiện ích dùng chung cho toàn bộ trang quản trị.
 *
 * File này đi qua Vite nên là module (defer): nó chạy SAU khi jQuery đã được nạp
 * bằng thẻ <script> thường ở <head> của layout, và TRƯỚC khi các callback
 * $(document).ready() trong blade chạy. Nhờ vậy mọi hàm ở đây đều dùng được bên
 * trong $(document).ready() của từng trang mà không cần import gì.
 *
 * Trước đây các hàm này bị copy vào từng blade, dẫn tới sửa một chỗ quên chỗ khác
 * (ví dụ formatPrice có 3 bản, một bản sai làm mọi sản phẩm hiện chung một giá).
 */

const jq = window.jQuery;

if (!jq) {
    console.error('admin.js: jQuery phải được nạp trước bundle Vite (xem layouts/app.blade.php).');
} else {
    setupAdminHelpers(jq);
}

function setupAdminHelpers($) {
    // ----- CSRF cho mọi request AJAX -----
    // Thay cho việc lặp lại khối headers: { 'X-CSRF-TOKEN': ... } ở từng lời gọi.
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
    });

    // ----- Định dạng tiền -----
    /**
     * Đổi một giá trị bất kỳ thành chuỗi tiền VND. Chấp nhận cả chuỗi đã định dạng
     * sẵn ("1.200.000 ₫") để gọi lại nhiều lần vẫn không hỏng.
     */
    window.formatPrice = function (price) {
        const numeric = parseInt(
            String(price === null || price === undefined ? '' : price)
                .replace(/\./g, '')
                .replace('₫', '')
                .replace(/\s/g, '')
                .trim(),
            10
        );

        if (isNaN(numeric)) {
            return '';
        }

        return numeric.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
    };

    /**
     * Định dạng tại chỗ mọi ô khớp selector.
     *
     * Bắt buộc phải duyệt từng phần tử: $(sel).text() trả về chuỗi NỐI của mọi
     * phần tử, còn .text(giá trị) ghi đè TẤT CẢ bằng cùng một giá trị.
     */
    window.formatPriceCells = function (selector) {
        $(selector).each(function () {
            $(this).text(window.formatPrice($(this).text()));
        });
    };

    // ----- Helper Debounce -----
    window.debounce = function (func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait || 300);
        };
    };

    // ----- Phân cách số tiền hàng nghìn -----
    window.nhomNghin = function (v) {
        if (v === null || v === undefined) return '';
        return String(v).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };

    $(document).on('input', '.o-tien', function () {
        const cu = $(this).val();
        const moi = window.nhomNghin(cu);
        if (cu !== moi) $(this).val(moi);
    });

    // ----- Thông báo Toast hiện đại -----
    const TOAST_ICONS = {
        success: '<svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
        error: '<svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
        info: '<svg class="w-5 h-5 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        warning: '<svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
    };

    const toastHost = function () {
        let host = $('#admin-toast-host');

        if (!host.length) {
            host = $('<div id="admin-toast-host"></div>').css({
                position: 'fixed',
                top: '1.25rem',
                right: '1.25rem',
                zIndex: 99999,
                display: 'flex',
                flexDirection: 'column',
                gap: '0.625rem',
                maxWidth: '24rem',
                width: 'calc(100vw - 2.5rem)',
                pointerEvents: 'none',
            });
            $('body').append(host);
        }

        return host;
    };

    /**
     * Thông báo toast hiện đại, chuyên nghiệp.
     */
    window.showToast = function (message, type) {
        if (!message) return;

        const toastType = type || 'success';
        const iconSvg = TOAST_ICONS[toastType] || TOAST_ICONS.info;

        const borderClass = toastType === 'error' ? 'border-rose-200 dark:border-rose-800' :
                           toastType === 'warning' ? 'border-amber-200 dark:border-amber-800' :
                           toastType === 'info' ? 'border-indigo-200 dark:border-indigo-800' :
                           'border-emerald-200 dark:border-emerald-800';

        const toast = $(`
            <div class="pointer-events-auto flex items-start gap-3 p-4 bg-white dark:bg-slate-800 rounded-xl shadow-xl border ${borderClass} text-slate-800 dark:text-slate-100 text-sm transform transition-all duration-200 translate-y-2 opacity-0 cursor-pointer select-none">
                ${iconSvg}
                <div class="flex-1 text-xs sm:text-sm font-medium leading-5 whitespace-pre-line">${message}</div>
                <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs shrink-0 ml-1">✕</button>
            </div>
        `);

        toast.on('click', function () {
            toast.addClass('opacity-0 translate-x-4');
            setTimeout(() => toast.remove(), 200);
        });

        toastHost().append(toast);
        
        requestAnimationFrame(() => {
            toast.removeClass('translate-y-2 opacity-0');
        });

        setTimeout(
            function () {
                toast.addClass('opacity-0 translate-x-4');
                setTimeout(() => toast.remove(), 200);
            },
            toastType === 'error' ? 6000 : 3500
        );
    };

    /**
     * Rút một câu đọc được từ phần thân không phải JSON.
     *
     * Máy chủ có thể trả HTML thay vì JSON: PHP chặn tệp quá lớn, hết thời gian
     * chạy, hoặc phiên đăng nhập hết hạn nên bị đẩy về trang đăng nhập. Thiếu
     * bước này thì người dùng chỉ nhận được một thông báo trống rỗng.
     */
    const docThanPhanHoi = function (xhr) {
        const raw = (xhr && xhr.responseText) || '';
        if (!raw) return '';

        const warning = raw.match(/(?:Warning|Fatal error)[^<]*/i);
        if (warning) return warning[0].trim();

        // Bỏ thẻ HTML rồi lấy phần đầu, tránh đổ cả trang lỗi vào thông báo.
        const text = raw.replace(/<[^>]*>/g, ' ').replace(/[ \t\r\n]+/g, ' ').trim();
        return text.length > 300 ? text.slice(0, 300) + '…' : text;
    };

    /**
     * Hiển thị lỗi validate (422) hoặc lỗi nghiệp vụ trả về từ server.
     */
    window.showAjaxError = function (xhr) {
        const res = (xhr && xhr.responseJSON) || {};

        // Không nhận được phản hồi nào: thường là tệp quá lớn nên máy chủ ngắt
        // kết nối giữa chừng, hoặc mạng rớt khi đang tải lên.
        if (xhr && xhr.status === 0) {
            window.showToast(
                'Mất kết nối khi đang tải lên. Tệp có thể quá lớn — thử bớt ảnh/video rồi lưu lại.',
                'error'
            );
            return;
        }

        // Phiên làm việc không còn hiệu lực. Hay gặp nhất là sau mỗi lần máy chủ
        // được triển khai lại: phiên lưu ở tệp trong container nên container mới
        // là mọi tab đang mở đều cầm thẻ CSRF đã chết.
        if (xhr && (xhr.status === 419 || xhr.status === 401)) {
            window.showToast(
                'Phiên đăng nhập đã hết hạn. Tải lại trang (F5) rồi thao tác lại — dữ liệu đang nhập sẽ mất nên hãy chép ra trước.',
                'error'
            );
            return;
        }

        if (xhr && xhr.status === 413) {
            window.showToast('Tệp tải lên vượt quá giới hạn của máy chủ.', 'error');
            return;
        }

        const message = res.errors
            ? Object.keys(res.errors)
                  .map(function (k) {
                      return res.errors[k].join('\n');
                  })
                  .join('\n')
            : res.error ||
              res.message ||
              docThanPhanHoi(xhr) ||
              'Lỗi: ' + ((xhr && xhr.statusText) || 'không rõ');

        window.showToast(message, 'error');
    };

    // ----- Nút gỡ media -----
    const CLOSE_ICON =
        'data:image/svg+xml;utf8,' +
        encodeURIComponent(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">' +
                '<circle cx="12" cy="12" r="11" fill="rgba(17,24,39,.75)"/>' +
                '<path d="M8 8l8 8M16 8l-8 8" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/>' +
                '</svg>'
        );

    /**
     * Nút "x" để gỡ một ảnh/video khỏi khung xem trước.
     *
     * Icon nhúng thẳng dưới dạng data URI: bản cũ hotlink từ vecteezy.com nên site
     * đó chết hoặc chặn hotlink là nút biến mất khỏi trang quản trị.
     */
    window.closeIconButton = function () {
        return $('<img>')
            .addClass('absolute top-0 right-0 m-2 w-6 h-6 cursor-pointer z-10')
            .attr({ src: CLOSE_ICON, alt: 'Xóa' });
    };

    // ----- Gửi form kèm trạng thái đang xử lý -----
    const SPINNER =
        '<svg class="inline w-4 h-4 mr-2 -mt-0.5 animate-spin" viewBox="0 0 24 24" fill="none">' +
        '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>' +
        '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path></svg>';

    /**
     * Gửi form bằng AJAX, đồng thời khóa nút và hiện tiến trình.
     *
     * Form gửi bằng AJAX thì trình duyệt không có thanh tải riêng; không có phản hồi
     * thì người dùng tưởng hệ thống đứng và bấm lại nhiều lần, tạo bản ghi trùng.
     */
    window.submitFormWithProgress = function (form, url, onSuccess, options) {
        if (form.data('submitting')) return;

        const settings = options || {};
        const button = form.find('button[type="submit"]');
        const originalLabel = button.html();
        form.data('submitting', true);

        /**
         * Ô tiền hiện dấu chấm phân cách nghìn nhưng máy chủ chỉ nhận số nguyên.
         * Bỏ dấu ngay trên phần tử rồi trả lại: các ô giá của biến thể do JS sinh
         * ra động nên sửa trong FormData sau khi dựng là không đủ.
         */
        const oTien = form.find('.o-tien');
        const giuLai = oTien.map(function () {
            return $(this).val();
        }).get();
        oTien.each(function () {
            $(this).val(String($(this).val()).replace(/\./g, ''));
        });
        const formData = new FormData(form[0]);
        oTien.each(function (i) {
            $(this).val(giuLai[i]);
        });

        $.ajax({
            url: url,
            type: settings.method || 'POST',
            data: formData,
            contentType: false,
            processData: false,
            xhr: function () {
                const xhr = $.ajaxSettings.xhr();

                if (xhr.upload) {
                    xhr.upload.addEventListener('progress', function (ev) {
                        if (!ev.lengthComputable) return;

                        const percent = Math.round((ev.loaded / ev.total) * 100);
                        // Tải file xong rồi vẫn còn phải chờ server ghi vào DB.
                        button.html(
                            SPINNER +
                                (percent < 100
                                    ? 'Đang tải lên ' + percent + '%'
                                    : 'Đang lưu vào hệ thống...')
                        );
                    });
                }

                return xhr;
            },
            beforeSend: function () {
                button
                    .prop('disabled', true)
                    .addClass('opacity-60 cursor-not-allowed')
                    .html(SPINNER + 'Đang xử lý...');
            },
            complete: function () {
                form.data('submitting', false);
                button
                    .prop('disabled', false)
                    .removeClass('opacity-60 cursor-not-allowed')
                    .html(originalLabel);
            },
            // Máy chủ vẫn có thể trả mã 200 kèm phần thân không phải JSON — PHP in
            // cảnh báo "tệp quá lớn" ra trước là một ví dụ. Coi đó là thành công thì
            // ngăn kéo đóng lại lặng lẽ dù chẳng có gì được lưu, nên phải chặn ở đây.
            success: function (response, textStatus, xhr) {
                if (!response || typeof response !== 'object' || !response.success) {
                    window.showAjaxError(xhr);
                    return;
                }

                onSuccess(response, textStatus, xhr);
            },
            error: settings.error || window.showAjaxError,
        });
    };

    // ----- Chọn ảnh / video -----
    /**
     * Quản lý danh sách media đã chọn của một input file.
     *
     * Danh sách được TÍCH LŨY trong JS vì trình duyệt thay mới hoàn toàn input.files
     * sau mỗi lần chọn: chỉ dựa vào input.files thì lần chọn trước bị mất âm thầm
     * (chọn video rồi chọn ảnh thì chỉ ảnh được gửi đi, dù khung xem trước vẫn hiện
     * đủ cả hai).
     */
    window.createMediaPicker = function (inputSel, previewSel, pinSel) {
        const picker = {
            input: $(inputSel),
            preview: $(previewSel),
            pin: $(pinSel),
            items: [],
        };

        // Ghi danh sách tích lũy ngược lại vào input để FormData gửi đủ file.
        picker.sync = function () {
            const transfer = new DataTransfer();
            picker.items.forEach(function (item) {
                transfer.items.add(item.file);
            });
            picker.input.prop('files', transfer.files);
        };

        picker.remove = function (item) {
            const index = picker.items.indexOf(item);
            if (index === -1) return;

            picker.items.splice(index, 1);
            URL.revokeObjectURL(item.url);
            item.slide.remove();

            // Ảnh đại diện vừa bị gỡ thì bỏ luôn lựa chọn ghim.
            if (picker.pin.val() === item.file.name) {
                picker.pin.val('');
            }

            picker.sync();
        };

        picker.reset = function () {
            picker.items.forEach(function (item) {
                URL.revokeObjectURL(item.url);
            });
            picker.items = [];
            picker.sync();
            picker.preview.empty();
        };

        picker.add = function (file) {
            // Bỏ qua file trùng để không tải lên hai lần cùng một ảnh.
            const isDuplicate = picker.items.some(function (item) {
                return item.file.name === file.name && item.file.size === file.size;
            });
            if (isDuplicate) return;

            const isVideo = file.type.indexOf('video/') === 0;
            // createObjectURL thay cho FileReader.readAsDataURL: đọc video vài chục MB
            // thành chuỗi base64 làm treo trình duyệt và tốn gấp rưỡi bộ nhớ.
            const url = URL.createObjectURL(file);
            const slide = $('<swiper-slide>').addClass('slide relative border border-gray-300');
            const item = { file: file, url: url, slide: slide };
            picker.items.push(item);

            const media = isVideo
                ? $('<video>')
                      .attr({ src: url, controls: true, preload: 'metadata' })
                      .addClass('w-full')
                : $('<img>').attr('src', url);

            const deleteBtn = window.closeIconButton();
            deleteBtn.on('click', function (ev) {
                ev.stopPropagation();
                picker.remove(item);
            });

            if (isVideo) {
                slide.append(
                    $('<span>')
                        .addClass('absolute bottom-0 left-0 px-1 text-xs text-white bg-black/60')
                        .text('VIDEO')
                );
            } else {
                // Chỉ ảnh mới được chọn làm ảnh đại diện; video thì bỏ qua.
                slide.on('click', function () {
                    picker.preview.find('swiper-slide').removeClass('selected');
                    $(this).addClass('selected');
                    picker.preview.prepend($(this));
                    picker.pin.val(file.name);
                    $('.download').removeClass('selected');
                });
            }

            slide.append(media).append(deleteBtn);
            picker.preview.append(slide);
        };

        picker.input.on('change', function () {
            Array.from(picker.input.prop('files') || []).forEach(function (file) {
                picker.add(file);
            });
            // Gộp cả file chọn lần trước lẫn lần này rồi ghi lại vào input.
            picker.sync();
        });

        return picker;
    };
}
