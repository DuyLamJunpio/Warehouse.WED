@props([
    'id' => 'modal-confirm',
    'title' => 'Xác nhận hành động',
    'message' => 'Bạn có chắc chắn muốn thực hiện hành động này không? Hành động này không thể hoàn tác.',
    'confirmText' => 'Đồng ý',
    'cancelText' => 'Hủy bỏ',
    'confirmVariant' => 'danger', // danger, primary
])

<div id="{{ $id }}" tabindex="-1" aria-hidden="true"
    class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-200">
    <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200/80 dark:border-slate-700/80 p-6 overflow-hidden">
        
        <div class="flex items-center gap-3 mb-4">
            <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $confirmVariant === 'danger' ? 'bg-rose-100 dark:bg-rose-950/60 text-rose-600' : 'bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600' }} shrink-0">
                @if ($confirmVariant === 'danger')
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                @else
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @endif
            </div>
            <div>
                <h3 class="text-base font-semibold text-slate-900 dark:text-white" id="{{ $id }}-title">
                    {{ $title }}
                </h3>
            </div>
        </div>

        <p class="text-sm text-slate-600 dark:text-slate-300 mb-6 leading-relaxed" id="{{ $id }}-message">
            {{ $message }}
        </p>

        <div class="flex items-center justify-end gap-2.5">
            <button type="button" data-modal-hide="{{ $id }}"
                class="btn-cancel-modal px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg shadow-sm dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700 transition-all">
                {{ $cancelText }}
            </button>
            <button type="button" id="{{ $id }}-btn-confirm"
                class="btn-confirm-modal px-4 py-2 text-sm font-medium text-white {{ $confirmVariant === 'danger' ? 'bg-rose-600 hover:bg-rose-700 active:bg-rose-800' : 'bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800' }} rounded-lg shadow-sm transition-all">
                {{ $confirmText }}
            </button>
        </div>
    </div>
</div>
