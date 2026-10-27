@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-sheet border-rule-strong text-ink shadow-seat placeholder:text-slate focus:border-seal focus:ring-seal disabled:cursor-not-allowed disabled:bg-linen-sunk disabled:text-slate']) }}>
