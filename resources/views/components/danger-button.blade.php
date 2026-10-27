{{-- Oxide is the only red fill in the system, and destructive is the only thing it marks. --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-sheet border border-transparent bg-oxide px-4 py-2 font-semibold text-white transition duration-150 ease-in-out hover:bg-oxide-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-oxide focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70']) }}>
    {{ $slot }}
</button>
