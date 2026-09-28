<x-app-layout>
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="text-xs text-slate-500 dark:text-slate-400">Hệ thống / Dữ liệu đã xóa</div>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Thùng rác</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Bản ghi được giữ 30 ngày, sau đó hệ thống sẽ xóa vĩnh viễn.
            </p>
        </div>
        <span class="inline-flex w-fit items-center rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
            Khôi phục trước hạn xóa
        </span>
    </div>

    <div id="trash-groups" class="space-y-5">
        @forelse ($groups as $group)
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-700/80 dark:bg-slate-800">
                <div class="flex items-center justify-between border-b border-slate-200/80 px-5 py-4 dark:border-slate-700/80">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ $group['label'] }}</h2>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $group['items']->count() }} bản ghi</p>
                    </div>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700/80">
                    @foreach ($group['items'] as $item)
                        <div class="flex items-center justify-between gap-3 px-5 py-3.5" data-trash-row>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $item['name'] }}</div>
                                <div class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    Xóa lúc {{ $item['deleted_at'] }} · còn khoảng {{ $item['days_left'] }} ngày
                                </div>
                            </div>
                            <button type="button"
                                class="restore-trash inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:text-indigo-300"
                                data-type="{{ $group['key'] }}" data-id="{{ $item['id'] }}">
                                Khôi phục
                            </button>
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-800">
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Thùng rác đang trống</div>
                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Các bản ghi xóa mềm sẽ xuất hiện ở đây.</div>
            </div>
        @endforelse
    </div>

    <script>
        $(document).on('click', '.restore-trash', function () {
            const button = $(this);
            button.prop('disabled', true).text('Đang khôi phục…');
            $.ajax({
                url: '/trash/' + button.data('type') + '/' + button.data('id') + '/restore',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    window.showToast(response.success);
                    button.closest('[data-trash-row]').remove();
                    if (!$('#trash-groups [data-trash-row]').length) {
                        window.location.reload();
                    }
                },
                error: function (xhr) {
                    button.prop('disabled', false).text('Khôi phục');
                    window.showAjaxError(xhr);
                }
            });
        });
    </script>
</x-app-layout>
