@php
    $isEdit = $prefix === 'edit';
    $field = fn ($name, $default = '') => old($name, $voucher?->{$name} ?? $default);
    $input = 'block w-full rounded-xl border-slate-300 bg-white px-3.5 py-2 text-sm shadow-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700 dark:text-white';
@endphp
<div class="grid grid-cols-2 gap-3">
    <div><label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Mã voucher <span class="text-rose-500">*</span></label><input id="{{ $prefix }}-code" name="code" value="{{ $field('code') }}" maxlength="50" required placeholder="SALE10" class="{{ $input }} uppercase"><p class="mt-1 text-[11px] text-slate-500">Chữ, số, - hoặc _</p></div>
    <div><label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Loại voucher <span class="text-rose-500">*</span></label><select id="{{ $prefix }}-type" name="type" class="{{ $input }}">@foreach($types as $key => $label)<option value="{{ $key }}" @selected($field('type', \App\Models\Voucher::TYPE_PERCENTAGE) === $key)>{{ $label }}</option>@endforeach</select></div>
</div>
<div><label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Tên chương trình <span class="text-rose-500">*</span></label><input id="{{ $prefix }}-name" name="name" value="{{ $field('name') }}" required maxlength="255" placeholder="Ưu đãi khách hàng mới" class="{{ $input }}"></div>
<div class="grid grid-cols-2 gap-3">
    <div><label id="{{ $prefix }}-value-label" class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Phần trăm giảm (%)</label><input id="{{ $prefix }}-value" type="number" name="value" value="{{ $field('value', 0) }}" min="0" required class="{{ $input }}"></div>
    <div id="{{ $prefix }}-max-box"><label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Giảm tối đa (₫)</label><input id="{{ $prefix }}-max_discount" type="number" name="max_discount" value="{{ $field('max_discount') }}" min="1" placeholder="Không giới hạn" class="{{ $input }}"></div>
</div>
<div class="grid grid-cols-2 gap-3">
    <div><label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Đơn tối thiểu (₫)</label><input id="{{ $prefix }}-min_order_amount" type="number" name="min_order_amount" value="{{ $field('min_order_amount', 0) }}" min="0" required class="{{ $input }}"></div>
    <div><label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Giới hạn lượt dùng</label><input id="{{ $prefix }}-usage_limit" type="number" name="usage_limit" value="{{ $field('usage_limit') }}" min="1" placeholder="Không giới hạn" class="{{ $input }}"></div>
</div>
<div class="grid grid-cols-2 gap-3">
    <div><label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Bắt đầu</label><input id="{{ $prefix }}-starts_at" type="datetime-local" name="starts_at" value="{{ $field('starts_at') }}" class="{{ $input }}"></div>
    <div><label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Kết thúc</label><input id="{{ $prefix }}-ends_at" type="datetime-local" name="ends_at" value="{{ $field('ends_at') }}" class="{{ $input }}"></div>
</div>
<div><label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Mô tả nội bộ</label><textarea id="{{ $prefix }}-description" name="description" rows="3" maxlength="2000" placeholder="Ghi chú về điều kiện, kênh áp dụng..." class="{{ $input }}">{{ $field('description') }}</textarea></div>
<label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm font-medium text-slate-700 dark:border-slate-600 dark:bg-slate-700/50 dark:text-slate-200"><input id="{{ $prefix }}-status" type="checkbox" name="status" value="1" @checked($isEdit ? true : old('status', true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"> Bật voucher ngay khi lưu</label>
