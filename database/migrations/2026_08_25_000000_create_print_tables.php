<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module in áo theo yêu cầu.
 *
 * Khác với hàng bán sẵn ở một điểm quyết định toàn bộ thiết kế bên dưới: khi
 * khách được tự chọn chỗ in, tiền in KHÔNG còn là một mã hàng nữa mà là một
 * hàm. Nó phụ thuộc kỹ thuật in, khổ in, vùng nào trên áo, tông màu áo, số màu
 * mực và số lượng. Vì vậy không có bảng "sản phẩm dịch vụ in" nào ở đây; có một
 * bảng giá và một bộ quy tắc, xem App\Services\PrintPricing.
 *
 * Hai quy ước xuyên suốt các bảng này:
 *
 *   1. MILIMÉT LÀ SỰ THẬT. Toạ độ phần trăm chỉ để vẽ khung lên ảnh mockup cho
 *      người xem; thợ in đọc mm. Lưu theo pixel màn hình là đổi ảnh mockup một
 *      lần thì mọi đơn cũ in lệch.
 *   2. ƯU TIÊN KHÔNG XOÁ CỨNG. Kỹ thuật, bậc khổ, phôi, sticker đều có
 *      `is_active` để ngừng bán an toàn. Kỹ thuật đã có thiết kế khách thì
 *      bắt buộc giữ lại; bản ghi khai nhầm chưa được dùng vẫn có thể xoá.
 */
return new class extends Migration
{
    public function up(): void
    {
        /*
         * Kỹ thuật in do chủ shop tự tạo, nên ràng buộc của nó phải là DỮ LIỆU
         * chứ không phải mã. Thêu không in được ảnh chụp, in lụa giới hạn số
         * màu — nếu viết `if ($tech === 'theu')` thì lần đầu chủ shop tạo thêm
         * một kỹ thuật lạ là mọi ràng buộc trượt hết.
         */
        Schema::create('print_techniques', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            // null = không giới hạn (decal, DTG). 6 = in lụa.
            $table->unsignedSmallInteger('max_colors')->nullable();
            $table->boolean('accepts_photo')->default(true);
            $table->boolean('accepts_gradient')->default(true);
            // Áo tối cần lót trắng — cờ này là chỗ bám cho quy tắc phụ phí lót.
            $table->boolean('needs_underbase')->default(true);
            $table->unsignedSmallInteger('min_dpi')->default(150);
            // Danh sách đuôi tệp nhận, phân tách bằng dấu phẩy: "png,pdf,svg".
            $table->string('file_types')->default('png,pdf,svg');
            $table->unsignedSmallInteger('lead_days')->default(3);
            $table->unsignedSmallInteger('moq')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /*
         * Bậc khổ in. Khung bao của khách được xếp vào bậc NHỎ NHẤT chứa được —
         * thợ in tính tiền theo khổ decal / khung lụa, không theo cm² lẻ. Khách
         * dán ba sticker nhỏ nằm gọn trong A5 thì trả tiền A5, không phải ba lần.
         */
        Schema::create('print_size_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40);
            $table->unsignedSmallInteger('width_mm');
            $table->unsignedSmallInteger('height_mm');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /*
         * Phôi in. `product_id` CÓ THỂ RỖNG và đó là điểm mấu chốt: hầu hết shop
         * in áo đặt phôi từ nhà cung cấp và không đếm tồn theo từng màu × size.
         * Nối vào sản phẩm trong kho là tính năng thêm cho ai có trữ phôi sẵn,
         * lúc đó tồn kho và giá thừa hưởng từ bên kia.
         */
        Schema::create('print_blanks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            // Dùng khi không nối kho; nối rồi thì giá lấy từ sản phẩm.
            $table->unsignedInteger('base_price')->default(0);
            /*
             * Hiệu chuẩn khung ảnh: áo này rộng/cao bao nhiêu mm TRONG tấm
             * mockup. Khai một lần, sau đó mọi vùng in vẽ bằng chuột đều tự ra
             * mm thật thay vì bắt người dùng gõ toạ độ.
             */
            $table->unsignedSmallInteger('frame_width_mm')->default(520);
            $table->unsignedSmallInteger('frame_height_mm')->default(700);
            $table->unsignedSmallInteger('moq')->default(1);
            $table->unsignedSmallInteger('lead_days')->default(3);
            // File template khách tải về để tự thiết kế ngoài (Canva, AI...).
            $table->string('template_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* Phôi nào in được bằng kỹ thuật nào. */
        Schema::create('print_blank_technique', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_blank_id')->constrained()->cascadeOnDelete();
            $table->foreignId('print_technique_id')->constrained()->cascadeOnDelete();
            $table->unique(['print_blank_id', 'print_technique_id'], 'print_blank_technique_unique');
        });

        /*
         * Màu áo. `tone` là trường RIÊNG chứ không suy từ mã màu: xám mélange
         * nằm giữa, và việc nó có cần lót trắng hay không là quyết định của
         * người in. Mặc định suy từ độ sáng, cho phép sửa đè.
         */
        Schema::create('print_blank_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_blank_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('hex', 7);
            $table->enum('tone', ['light', 'dark'])->default('light');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
        });

        /*
         * Vùng in. Hai hệ toạ độ nằm cạnh nhau và KHÔNG được lẫn:
         *   width_mm / height_mm  → kích thước thật trên vải, thợ in đọc cái này
         *   box_*                 → phần trăm trên ảnh mockup, chỉ để vẽ khung
         */
        Schema::create('print_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_blank_id')->constrained()->cascadeOnDelete();
            $table->string('key', 40);
            $table->string('label', 80);
            $table->unsignedSmallInteger('width_mm');
            $table->unsignedSmallInteger('height_mm');
            $table->decimal('box_x', 5, 2);
            $table->decimal('box_y', 5, 2);
            $table->decimal('box_w', 5, 2);
            $table->decimal('box_h', 5, 2);
            $table->unsignedSmallInteger('max_placements')->default(10);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unique(['print_blank_id', 'key']);
        });

        /*
         * Ảnh mockup, một tấm cho mỗi màu × mỗi góc nhìn.
         *
         * Mockup KHÁC ảnh bán hàng: ảnh bán hàng là người mẫu mặc, chụp nghiêng,
         * overlay khung in lên là lệch. Mockup phải là áo trải phẳng, chính diện,
         * CÙNG khung cắt cho mọi màu — vùng in khai một lần cho cả phôi nên tấm
         * nào cắt cúp khác là khung in sai trên tấm đó.
         *
         * `offset_*` là đường thoát cho tấm không chụp lại được: chỉnh lệch
         * riêng cho một màu thay vì bắt khai lại toàn bộ vùng in.
         */
        Schema::create('print_mockups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_blank_id')->constrained()->cascadeOnDelete();
            $table->foreignId('print_blank_color_id')->nullable()->constrained('print_blank_colors')->cascadeOnDelete();
            $table->string('view', 40)->default('front');
            $table->string('path');
            $table->unsignedSmallInteger('width_px')->default(0);
            $table->unsignedSmallInteger('height_px')->default(0);
            $table->decimal('offset_x', 5, 2)->default(0);
            $table->decimal('offset_y', 5, 2)->default(0);
            $table->timestamps();
        });

        /*
         * Tài nguyên in: sticker/logo shop cung cấp sẵn (`library`) và file
         * khách tải lên (`upload`). Chung một bảng vì cả hai đều được đặt lên áo
         * y hệt nhau — chỗ khác nhau chỉ là ai đưa vào và có tính phí hay không.
         */
        Schema::create('print_assets', function (Blueprint $table) {
            $table->id();
            $table->enum('kind', ['library', 'upload'])->default('library');
            $table->string('name');
            $table->string('tag', 60)->nullable();
            $table->string('path');
            $table->unsignedInteger('width_px')->default(0);
            $table->unsignedInteger('height_px')->default(0);
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('bytes')->default(0);
            $table->boolean('has_alpha')->default(false);
            // 0 = miễn phí. Sticker có bản quyền thì tính tiền.
            $table->unsignedInteger('fee')->default(0);
            // null = mọi kỹ thuật. Sticker nhiều màu không dùng cho in lụa 1 màu.
            $table->json('allowed_technique_ids')->nullable();
            // Giới hạn phóng to, để khách không kéo ra vỡ nét.
            $table->unsignedSmallInteger('min_width_mm')->default(10);
            $table->unsignedSmallInteger('max_width_mm')->default(400);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('kind');
        });

        /*
         * Phông chữ shop in được.
         *
         * Là DỮ LIỆU chứ không phải danh sách gõ cứng, vì phông là thứ xưởng phải
         * thật sự có trong máy. Khách chọn phông ở studio là chọn từ đúng những
         * phông shop in nổi — cho tự do chọn phông hệ thống là nhận về những đơn
         * không sản xuất được.
         *
         * `file_path` là tệp web (woff2) để studio hiện đúng mặt chữ. Xưởng dùng
         * bản OTF/TTF của chính phông đó; tên phải khớp để hai bên nói cùng một thứ.
         */
        Schema::create('print_fonts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Ngăn xếp CSS dự phòng khi tệp phông chưa tải xong.
            $table->string('family')->default('sans-serif');
            $table->string('file_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /*
         * Bảng giá đã xuất bản — BẤT BIẾN.
         *
         * Chủ shop sửa giá lúc 3 giờ chiều thì đơn đặt lúc 2 giờ đang chờ duyệt
         * không được đổi tiền. Mỗi thiết kế lưu kèm id của phiên bản đã dùng.
         *
         * `data` chụp TRỌN VẸN cả kỹ thuật và bậc khổ chứ không chỉ trỏ id sang
         * hai bảng kia: sửa "A4" thành 210×310mm sau khi xuất bản mà snapshot
         * chỉ giữ id thì đơn cũ tính lại ra số khác. Bản nháp đang sửa nằm ở
         * Setting khoá `print_pricing_draft`, không nằm ở đây.
         */
        Schema::create('print_pricing_versions', function (Blueprint $table) {
            $table->id();
            $table->json('data');
            $table->string('note')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->useCurrent();
            $table->timestamps();
            $table->index('published_at');
        });

        /*
         * Thiết kế của khách. Một bản ghi ở đây là MỘT MẪU ÁO, không phải một
         * đơn hàng: khách đặt 30 áo cùng mẫu thì vẫn là một thiết kế, qty = 30.
         *
         * `placements` giữ mọi hình đã đặt, toạ độ tính bằng mm so với góc trên
         * trái của vùng in. Luồng "tải template về tự thiết kế rồi tải lên" cũng
         * đổ vào đúng cấu trúc này — nó chỉ là một hình phủ trọn vùng in, nên
         * không cần luồng đơn thứ hai.
         *
         * `price_breakdown` là bảng kê từng dòng đã tính. Bảng giá càng linh
         * hoạt thì bảng kê càng bắt buộc: một con số tổng từ công thức khách
         * không nhìn thấy là con số khách không tin, và là con số nhân viên
         * không cãi lại được khi khách thắc mắc.
         */
        Schema::create('print_designs', function (Blueprint $table) {
            $table->id();
            // Mã ngắn để khách chia sẻ lại bản nháp và để nhân viên gọi tên.
            $table->string('code', 24)->unique();
            $table->foreignId('print_blank_id')->constrained()->restrictOnDelete();
            $table->foreignId('print_technique_id')->constrained()->restrictOnDelete();
            $table->string('color_name', 80);
            $table->enum('color_tone', ['light', 'dark'])->default('light');
            $table->string('size', 40);
            $table->unsignedSmallInteger('ink_colors')->default(1);
            $table->unsignedSmallInteger('qty')->default(1);
            $table->json('placements');
            $table->string('preview_path')->nullable();

            /*
             * Hoá đơn chứa NHIỀU mẫu in, nên khoá ngoại nằm ở đây chứ không phải
             * một cột trên `invoices`. Mỗi mẫu là một món trong giỏ hàng: khách
             * dựng xong một mẫu thì thêm vào giỏ rồi dựng tiếp mẫu khác — đơn áo
             * lớp có người mặc M người mặc XL là chuyện thường.
             *
             * Rỗng = khách đã chốt thiết kế nhưng chưa đặt hàng.
             */
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();

            $table->foreignId('pricing_version_id')->nullable()->constrained('print_pricing_versions')->nullOnDelete();
            $table->json('price_breakdown')->nullable();
            $table->unsignedInteger('unit_price')->default(0);
            $table->unsignedInteger('total_price')->default(0);

            /*
             * Đơn in KHÔNG được nhảy thẳng từ "đã thanh toán" sang "đang in".
             * File có thể không đủ nét, nền không trong suốt, hoặc vi phạm bản
             * quyền. Bắt lỗi ở đây rẻ hơn in hỏng 50 áo.
             */
            $table->enum('review_status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->string('review_note', 500)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
            $table->index('review_status');
        });

        /*
         * Tiền in của cả hoá đơn, cộng dồn từ mọi mẫu gắn vào nó.
         *
         * Tách riêng khỏi `total_amount` để lúc tính lãi còn biết bao nhiêu là
         * tiền hàng và bao nhiêu là công in. Đầu mối tới từng mẫu đi theo chiều
         * ngược lại — `print_designs.invoice_id` — vì một đơn có nhiều mẫu.
         */
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedInteger('print_fee')->default(0)->after('note');

            /*
             * Tài khoản nhận hoàn tiền.
             *
             * Đơn in có thể bị từ chối sau khi đã thu tiền — file không đủ nét,
             * nội dung vi phạm bản quyền. Lúc đó nhân viên cần biết chuyển trả
             * vào đâu, và hỏi lại khách qua điện thoại là chậm và dễ nghe nhầm số.
             *
             * Nullable vì đơn hàng bán sẵn không cần: hàng lỗi thì đổi, hiếm khi
             * hoàn tiền.
             */
            $table->string('refund_bank_name', 100)->nullable()->after('print_fee');
            $table->string('refund_account_number', 40)->nullable()->after('refund_bank_name');
            $table->string('refund_account_name', 120)->nullable()->after('refund_account_number');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'print_fee',
                'refund_bank_name',
                'refund_account_number',
                'refund_account_name',
            ]);
        });

        Schema::dropIfExists('print_designs');
        Schema::dropIfExists('print_fonts');
        Schema::dropIfExists('print_pricing_versions');
        Schema::dropIfExists('print_assets');
        Schema::dropIfExists('print_mockups');
        Schema::dropIfExists('print_zones');
        Schema::dropIfExists('print_blank_colors');
        Schema::dropIfExists('print_blank_technique');
        Schema::dropIfExists('print_blanks');
        Schema::dropIfExists('print_size_tiers');
        Schema::dropIfExists('print_techniques');
    }
};
