{{-- Prussian fill, Stake focus ring: the system's signature pairing. --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-sheet border border-transparent bg-seal px-4 py-2.5 font-semibold text-white shadow-seat transition duration-150 ease-in-out hover:bg-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70']) }}>
    {{ $slot }}
</button>
