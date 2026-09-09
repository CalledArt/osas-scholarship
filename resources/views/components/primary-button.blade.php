<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-cvsu-green border border-transparent rounded-lg font-semibold text-sm text-white shadow-sm hover:bg-cvsu-green-dark focus:outline-none focus:ring-2 focus:ring-cvsu-gold focus:ring-offset-2 disabled:opacity-70 disabled:cursor-not-allowed transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
