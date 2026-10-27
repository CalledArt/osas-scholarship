<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 rounded-sheet border border-rule-strong bg-linen px-4 py-2 font-semibold text-graphite shadow-seat transition duration-150 ease-in-out hover:bg-linen-sunk focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70']) }}>
    {{ $slot }}
</button>
