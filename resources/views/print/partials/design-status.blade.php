{{-- Nhãn trạng thái duyệt, dùng chung cho danh sách và trang chi tiết. --}}
@php
    $tone = match ($design->review_status) {
        \App\Models\PrintDesign::STATUS_APPROVED => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
        \App\Models\PrintDesign::STATUS_REJECTED => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300',
        \App\Models\PrintDesign::STATUS_PENDING => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
        default => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
    };
@endphp

<span class="px-2.5 py-1 rounded-full text-[11px] font-bold whitespace-nowrap {{ $tone }}">
    {{ $design->statusLabel() }}
</span>
