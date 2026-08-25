<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PrintDesign;
use App\Services\PrintReviewMailer;
use Illuminate\Http\Request;

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
        $design->load(['blank.zones', 'blank.colors', 'blank.mockups', 'technique', 'reviewer', 'pricingVersion', 'invoice']);

        return view('print.design-detail', [
            'design' => $design,
            'invoice' => $design->invoice,
            'sheet' => $design->productionSheet(),
            'boxes' => $design->zoneBoxes(),
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
        $design->loadMissing(['blank.zones']);

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

        return response()->json([
            'success' => $data['decision'] === PrintDesign::STATUS_APPROVED
                ? 'Đã duyệt thiết kế ' . $design->code . '. Có thể đưa vào xưởng.'
                : 'Đã từ chối thiết kế ' . $design->code . '. Nhớ liên hệ khách và hoàn tiền.',
        ]);
    }
}
