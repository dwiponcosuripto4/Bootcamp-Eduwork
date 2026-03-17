@props(['active'])

@php
    $classes =
        $active ?? false
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-[#FFDD5E] text-sm font-medium leading-5 text-[#FFDD5E] focus:outline-none focus:border-[#FFDD5E] transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-[#FFF1F1] hover:text-[#FFDD5E] hover:border-[#FFDD5E]/70 focus:outline-none focus:text-[#FFDD5E] focus:border-[#FFDD5E]/70 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
