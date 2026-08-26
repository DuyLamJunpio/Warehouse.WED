{{--
    Thanh tab của module in.

    Gom về một chỗ để thêm màn hình mới chỉ phải sửa đúng file này, thay vì đi
    sửa từng view rồi bỏ sót một cái.
--}}
@php
    // Đếm ngay tại đây thay vì bắt mỗi controller truyền xuống: một lần COUNT
    // rẻ hơn nhiều so với việc bỏ sót con số này ở một màn hình nào đó.
    $pendingDesigns = \App\Models\PrintDesign::where('review_status', \App\Models\PrintDesign::STATUS_PENDING)->count();

    $printTabs = [
        ['route' => 'print.pricing', 'label' => 'Bảng giá', 'match' => 'print.pricing*'],
        ['route' => 'print.techniques', 'label' => 'Kỹ thuật & bậc khổ', 'match' => 'print.techniques*'],
        ['route' => 'print.blanks', 'label' => 'Phôi in', 'match' => 'print.blanks*'],
        ['route' => 'print.library', 'label' => 'Thư viện sticker', 'match' => 'print.library*'],
        ['route' => 'print.designs', 'label' => 'Duyệt thiết kế', 'match' => 'print.designs*', 'badge' => $pendingDesigns],
    ];
@endphp

<div class="mb-5 flex flex-wrap items-center gap-1.5 border-b border-slate-200 dark:border-slate-700">
    @foreach ($printTabs as $tab)
        @php($active = request()->routeIs($tab['match']))
        <a href="{{ route($tab['route']) }}"
            class="flex items-center gap-2 px-4 py-2.5 text-sm border-b-2 transition-colors {{ $active
                ? 'font-semibold border-indigo-600 text-indigo-700 dark:text-indigo-300'
                : 'font-medium border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
            <span>{{ $tab['label'] }}</span>
            @if (!empty($tab['badge']))
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 tabular-nums">
                    {{ $tab['badge'] }}
                </span>
            @endif
        </a>
    @endforeach
</div>
