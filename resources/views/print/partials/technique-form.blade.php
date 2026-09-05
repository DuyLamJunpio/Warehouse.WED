<form data-technique-form="{{ $technique?->id }}" action="{{ $technique ? route('print.techniques.update', $technique) : route('print.techniques.store') }}" class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
    <label class="block text-sm text-slate-700 dark:text-slate-200">
        Tên kỹ thuật
        <input name="name" required maxlength="120" value="{{ $technique?->name }}" placeholder="VD: In chuyển nhiệt" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-900">
    </label>
    <label class="block text-sm text-slate-700 dark:text-slate-200">
        Giá in (đồng / vị trí / áo)
        <input name="price" type="number" required min="0" max="1000000000" step="1" value="{{ $technique?->price }}" placeholder="30000" class="mt-1 w-full rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-900">
    </label>
    @if ($technique)
        <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
            <span data-price-status class="text-slate-500 dark:text-slate-400">{{ $technique->price === null ? 'Chưa có giá' : number_format($technique->price, 0, ',', '.') . 'đ / vị trí / áo' }}</span>
            <label class="flex items-center gap-2 text-slate-700 dark:text-slate-200">
                <input type="checkbox" data-toggle data-url="{{ route('print.techniques.toggle', $technique) }}" @checked($technique->is_active) class="rounded border-slate-300 text-indigo-600"> Đang dùng
            </label>
        </div>
    @endif
    @if ($technique)
        <button type="button" data-delete data-url="{{ route('print.techniques.destroy', $technique) }}" class="mr-3 text-sm text-rose-600 hover:underline">Xóa kỹ thuật</button>
    @endif
    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">{{ $technique ? 'Lưu thay đổi' : 'Tạo kỹ thuật' }}</button>
</form>
