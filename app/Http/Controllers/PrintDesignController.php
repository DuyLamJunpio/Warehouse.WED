<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PrintDesign;
use App\Services\PrintReviewMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Hàng đợi duyệt thiết kế.
 *
 * Đơn in KHÔNG được nhảy thẳng từ "đã thanh toán" sang "đang in". File có thể
 * không đủ nét, nền không trong suốt, hoặc nội dung vi phạm bản quyền. Bắt lỗi
 * ở màn hình này rẻ hơn in hỏng 50 áo rất nhiều.
 */
class PrintDesignController extends Controller
{
    public function __construct(private PrintReviewMailer $mailer)
    {
    }

    public function index(Request $request)
    {
        $status = $request->query('status', PrintDesign::STATUS_PENDING);

        $designs = PrintDesign::with(['blank', 'technique'])
            ->when(
                in_array($status, array_keys(PrintDesign::STATUS_LABELS), true),
                fn ($q) => $q->where('review_status', $status),
            )
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Đếm theo từng trạng thái để nhân viên thấy ngay còn bao nhiêu việc.
        $counts = PrintDesign::selectRaw('review_status, count(*) as total')
            ->groupBy('review_status')
            ->pluck('total', 'review_status');

        return view('print.designs', [
            'designs' => $designs,
            'status' => $status,
            'counts' => $counts,
            'labels' => PrintDesign::STATUS_LABELS,
        ]);
    }

    public function show(PrintDesign $design)
    {
        $design->load(['blank.colors', 'blank.mockups', 'technique', 'reviewer', 'pricingVersion', 'invoice']);

        return view('print.design-detail', [
            'design' => $design,
            'invoice' => $design->invoice,
            'sheet' => $design->productionSheet(),
            'boxes' => $design->positionBoxes(),
        ]);
    }

    /**
     * Tải file sản xuất.
     *
     * Trả thẳng chuỗi SVG chứ không lưu ra đĩa: nó dựng lại từ `placements` trong
     * một phần nghìn giây, và lưu bản sao là tự tạo ra thứ có thể lệch với dữ liệu.
     */
    public function svg(PrintDesign $design)
    {
        $design->loadMissing('blank');

        return response($design->toSvg(), 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $design->code . '.svg"',
        ]);
    }

    /**
     * Duyệt hoặc từ chối.
     *
     * Từ chối bắt buộc có lý do: khách sẽ đọc đúng câu này, và "không đạt" thì
     * họ không sửa được gì. Duyệt rồi mới được đưa vào xưởng.
     */
    public function review(Request $request, PrintDesign $design)
    {
        $data = $request->validate([
            'decision' => 'required|in:approved,rejected',
            'note' => 'required_if:decision,rejected|nullable|string|max:500',
        ]);

        $design->update([
            'review_status' => $data['decision'],
            'review_note' => $data['note'] ?? null,
            'reviewed_by' => $request->user()?->id,
            'reviewed_at' => now(),
        ]);

        // Khách phải biết ngay. Mẫu bị từ chối nghĩa là họ vừa mất tiền và chưa
        // hiểu vì sao — im lặng ở đúng chỗ này là họ phải tự gọi lên hỏi.
        $this->mailer->queue($design);

        if ($data['decision'] === PrintDesign::STATUS_REJECTED) {
            return response()->json([
                'success' => 'Đã từ chối thiết kế ' . $design->code
                    . '. Đã gửi thư báo khách. Nhớ liên hệ và hoàn tiền.',
            ]);
        }

        return response()->json([
            'success' => 'Đã duyệt thiết kế ' . $design->code . '. ' . $this->confirmOrder($design),
        ]);
    }

    /**
     * Kéo đơn gắn với mẫu vừa duyệt sang "Đã xác nhận".
     *
     * Duyệt thiết kế LÀ khâu xác nhận của một đơn in: sau bước này không còn gì
     * để nhân viên cân nhắc nữa, nên bắt họ mở tiếp trang đơn hàng bấm thêm một
     * nút là thừa — và là chỗ dễ quên, để đơn nằm ở "chờ xác nhận" trong khi
     * xưởng đã in xong.
     *
     * Thư báo khách không gửi ở đây: OrderStatusObserver bắt mọi lần đơn đổi
     * trạng thái, kể cả lần này. Đơn đã trả tiền thì observer tự im vì thư
     * "đã duyệt thiết kế" vừa gửi xong đã nói đúng việc đó rồi.
     *
     * Trả về câu mô tả kết quả để hiện cho nhân viên.
     */
    private function confirmOrder(PrintDesign $design): string
    {
        if (!$design->invoice_id) {
            return 'Mẫu chưa gắn đơn nào — khách chốt thiết kế nhưng chưa đặt hàng.';
        }

        return DB::transaction(function () use ($design) {
            // Khoá dòng đơn: hai nhân viên có thể đang duyệt hai mẫu của cùng
            // một đơn, và cả hai đều thấy "mẫu cuối cùng" nếu không khoá.
            $order = Invoice::orders()->whereKey($design->invoice_id)->lockForUpdate()->first();

            if (!$order) {
                return 'Không tìm thấy đơn gắn với mẫu này.';
            }

            /*
             * Một đơn có thể gồm nhiều mẫu — đơn áo lớp là cùng một hình trên ba
             * size, mỗi size một mẫu. Chỉ xác nhận khi TẤT CẢ đã duyệt: còn một
             * mẫu chờ duyệt thì đơn chưa chắc đi được, còn một mẫu bị từ chối thì
             * đơn phải do người xử lý tay (hoàn một phần hay huỷ hẳn).
             */
            $pending = $order->printDesigns()
                ->where('review_status', '!=', PrintDesign::STATUS_APPROVED)
                ->count();

            if ($pending > 0) {
                return 'Đơn ' . $order->order_code . ' còn ' . $pending
                    . ' mẫu chưa duyệt xong nên giữ nguyên trạng thái.';
            }

            // pending → confirmed là bước duy nhất hợp lệ; đơn đã huỷ, đã xác
            // nhận hay đang giao đều rơi vào đây và không bị đụng tới.
            if (!$order->canTransitionTo(Invoice::STATUS_CONFIRMED)) {
                return 'Đơn ' . $order->order_code . ' đang ở "'
                    . $order->order_status_label . '" nên giữ nguyên.';
            }

            $order->order_status = Invoice::STATUS_CONFIRMED;
            $order->save();

            return 'Đơn ' . $order->order_code . ' đã chuyển sang "Đã xác nhận" và khách đã được báo.';
        });
    }
}
