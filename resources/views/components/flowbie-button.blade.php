<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:opacity-50 transition-all duration-150']) }}>
    {{ $slot }}
</button>
