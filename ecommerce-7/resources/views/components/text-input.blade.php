@props(['disabled' => false])

<input @disabled($disabled)
    {{ $attributes->merge(['class' => 'border-[#E53935]/30 focus:border-[#E53935] focus:ring-[#FFDD5E] rounded-md shadow-sm']) }}>
