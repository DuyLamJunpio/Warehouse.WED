<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 active:bg-rose-800 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-rose-500/30 disabled:opacity-50 transition-all duration-150']) }}>
    {{ $slot }}
</button>
