<x-app-layout>
    <div class="mb-6">
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Kỹ thuật in</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Nhập tên và giá kỹ thuật cho mỗi áo. In nhiều vị trí, hình hoặc chữ trên cùng áo vẫn chỉ tính một lần. Lưu là áp dụng ngay.</p>
    </div>
    @include('print.partials.tabs')
    <section class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-indigo-200 bg-indigo-50/70 px-4 py-3 dark:border-indigo-900/70 dark:bg-indigo-950/30">
        <div>
            <p class="text-sm font-semibold text-indigo-900 dark:text-indigo-100">Giá hiển thị trên webstore</p>
            <p class="mt-0.5 text-xs text-indigo-700/80 dark:text-indigo-300/80">
                Gộp giá phôi với phí kỹ thuật chung.
                @if ($commonTechniquePrice === null)
                    Cần đặt tất cả kỹ thuật đang bật cùng một giá để bật.
                @else
                    Mức kỹ thuật hiện tại: {{ number_format($commonTechniquePrice, 0, ',', '.') }}đ.
                @endif
            </p>
        </div>
        <label data-combined-price-shell class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-indigo-900 dark:text-indigo-100">
            <input type="checkbox" data-combined-price-toggle
                data-url="{{ route('print.techniques.combined-price') }}"
                @checked($displayCombinedPrice)
                @disabled($commonTechniquePrice === null && !$displayCombinedPrice)
                class="peer sr-only">
            <span data-combined-price-track aria-hidden="true" class="relative h-6 w-11 shrink-0 rounded-full bg-slate-300 transition-colors dark:bg-slate-600">
                <span data-combined-price-thumb class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow-sm transition-transform"></span>
            </span>
            <span>Bật gộp giá</span>
            <span data-combined-price-spinner class="hidden items-center gap-1 text-xs font-medium text-indigo-700 dark:text-indigo-300">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path></svg>
                Đang lưu...
            </span>
        </label>
    </section>
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
        const SPIN = '<svg class="inline-block h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path></svg>';
        const setButtonBusy = (button, label) => {
            const original = button.innerHTML;
            button.disabled = true;
            button.classList.add('opacity-60', 'cursor-wait');
            button.innerHTML = '<span class="inline-flex items-center gap-2">' + SPIN + '<span>' + label + '</span></span>';
            return () => {
                button.disabled = false;
                button.classList.remove('opacity-60', 'cursor-wait');
                button.innerHTML = original;
            };
        };
        const combinedToggle = document.querySelector('[data-combined-price-toggle]');
        const combinedShell = document.querySelector('[data-combined-price-shell]');
        const combinedSpinner = document.querySelector('[data-combined-price-spinner]');
        const combinedTrack = document.querySelector('[data-combined-price-track]');
        const combinedThumb = document.querySelector('[data-combined-price-thumb]');
        const syncCombinedVisual = () => {
            const enabled = !!combinedToggle?.checked;
            combinedTrack?.classList.toggle('bg-indigo-600', enabled);
            combinedTrack?.classList.toggle('bg-slate-300', !enabled);
            combinedTrack?.classList.toggle('dark:bg-indigo-500', enabled);
            combinedTrack?.classList.toggle('dark:bg-slate-600', !enabled);
            combinedThumb?.classList.toggle('translate-x-5', enabled);
        };
        syncCombinedVisual();
        combinedToggle?.addEventListener('change', async () => {
            syncCombinedVisual();
            combinedToggle.disabled = true;
            combinedShell?.classList.add('cursor-wait', 'opacity-80');
            combinedSpinner?.classList.remove('hidden');
            combinedSpinner?.classList.add('inline-flex');
            try {
                const result = await post(combinedToggle.dataset.url, { enabled: combinedToggle.checked });
                window.showToast(result.success, 'success');
                // Trạng thái khóa/mở ô giá phụ thuộc vào bản giá vừa xuất bản.
                setTimeout(() => location.reload(), 350);
            } catch (error) {
                combinedToggle.checked = !combinedToggle.checked;
                syncCombinedVisual();
                window.showToast(error.message, 'error');
            } finally {
                combinedToggle.disabled = false;
                combinedShell?.classList.remove('cursor-wait', 'opacity-80');
                combinedSpinner?.classList.add('hidden');
                combinedSpinner?.classList.remove('inline-flex');
            }
        });
        document.querySelectorAll('[data-technique-form]').forEach(form => {
            form.addEventListener('submit', async event => {
                event.preventDefault();
                const button = form.querySelector('[type="submit"]');
                const restoreButton = setButtonBusy(button, 'Đang lưu...');
                try {
                    const result = await post(form.action, {
                        name: form.elements.name.value.trim(),
                        price: Number(form.elements.price.value),
                    });
                    window.showToast(result.success, 'success');
                    if (!form.dataset.techniqueForm) { location.reload(); return; }
                    form.querySelector('[data-price-status]').textContent = Number(form.elements.price.value).toLocaleString('vi-VN') + 'đ / áo';
                } catch (error) { window.showToast(error.message, 'error'); }
                finally { restoreButton(); }
            });
            const remove = form.querySelector('[data-delete]');
            remove?.addEventListener('click', async () => {
                if (!window.confirm('Xóa kỹ thuật "' + form.elements.name.value + '"? Chỉ xóa được khi chưa có thiết kế khách sử dụng.')) return;
                const restoreButton = setButtonBusy(remove, 'Đang xóa...');
                try {
                    const result = await post(remove.dataset.url, {}, 'DELETE');
                    window.showToast(result.success, 'success');
                    form.remove();
                } catch (error) { window.showToast(error.message, 'error'); }
                finally { restoreButton(); }
            });
            const toggle = form.querySelector('[data-toggle]');
            toggle?.addEventListener('change', async () => {
                toggle.disabled = true;
                toggle.classList.add('animate-pulse');
                try {
                    const result = await post(toggle.dataset.url, { is_active: toggle.checked });
                    window.showToast(result.success, 'success');
                } catch (error) {
                    toggle.checked = !toggle.checked;
                    window.showToast(error.message, 'error');
                } finally {
                    toggle.disabled = false;
                    toggle.classList.remove('animate-pulse');
                }
            });
        });
    })();
    </script>
</x-app-layout>
