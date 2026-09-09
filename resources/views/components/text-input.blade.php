@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-cvsu-green focus:ring-cvsu-green rounded-lg shadow-sm']) }}>
