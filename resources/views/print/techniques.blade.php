<x-app-layout>
    <div class="mb-6">
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Kỹ thuật in</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Nhập tên và giá in cho mỗi vị trí trên một áo. Lưu là áp dụng ngay.</p>
    </div>
    @include('print.partials.tabs')
    @if ($techniques->contains(fn ($technique) => $technique->price === null))
        <p class="mb-4 rounded-xl bg-amber-50 p-4 text-sm text-amber-800 dark:bg-amber-950 dark:text-amber-200">
            Nhập giá cố định cho các kỹ thuật đang báo “Chưa có giá” để khách có thể chọn in.
        </p>
    @endif
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-start">
        <section class="space-y-3">
            @forelse ($techniques as $technique)
                @include('print.partials.technique-form', ['technique' => $technique])
            @empty
                <p class="p-5 text-sm text-slate-500">Chưa có kỹ thuật in. Tạo kỹ thuật đầu tiên bằng tên và giá.</p>
            @endforelse
        </section>
        <section>
            <h2 class="mb-3 font-semibold text-slate-900 dark:text-white">Thêm kỹ thuật in</h2>
            @include('print.partials.technique-form', ['technique' => null])
            <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Ví dụ: phôi 100.000đ + in mặt trước 30.000đ = 130.000đ/áo. In thêm mặt sau cộng 30.000đ.</p>
        </section>
    </div>
    <script>
    (() => {
        const post = async (url, body, method = 'POST') => {
            const response = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(body),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(Object.values(result.errors || {}).flat().join(' ') || result.error || result.message || 'Không lưu được. Vui lòng thử lại.');
            return result;
        };
        document.querySelectorAll('[data-technique-form]').forEach(form => {
            form.addEventListener('submit', async event => {
                event.preventDefault();
                const button = form.querySelector('[type="submit"]');
                button.disabled = true;
                try {
                    const result = await post(form.action, {
                        name: form.elements.name.value.trim(),
                        price: Number(form.elements.price.value),
                    });
                    window.showToast(result.success, 'success');
                    if (!form.dataset.techniqueForm) { location.reload(); return; }
                    form.querySelector('[data-price-status]').textContent = Number(form.elements.price.value).toLocaleString('vi-VN') + 'đ / vị trí / áo';
                } catch (error) { window.showToast(error.message, 'error'); }
                finally { button.disabled = false; }
            });
            const remove = form.querySelector('[data-delete]');
            remove?.addEventListener('click', async () => {
                if (!window.confirm('Xóa kỹ thuật "' + form.elements.name.value + '"? Chỉ xóa được khi chưa có thiết kế khách sử dụng.')) return;
                remove.disabled = true;
                try {
                    const result = await post(remove.dataset.url, {}, 'DELETE');
                    window.showToast(result.success, 'success');
                    form.remove();
                } catch (error) { window.showToast(error.message, 'error'); }
                finally { remove.disabled = false; }
            });
            const toggle = form.querySelector('[data-toggle]');
            toggle?.addEventListener('change', async () => {
                toggle.disabled = true;
                try {
                    const result = await post(toggle.dataset.url, { is_active: toggle.checked });
                    window.showToast(result.success, 'success');
                } catch (error) {
                    toggle.checked = !toggle.checked;
                    window.showToast(error.message, 'error');
                } finally { toggle.disabled = false; }
            });
        });
    })();
    </script>
</x-app-layout>
