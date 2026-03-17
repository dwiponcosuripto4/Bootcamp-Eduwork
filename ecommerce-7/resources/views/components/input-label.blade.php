@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-[#7A0C0C]']) }}>
    {{ $value ?? $slot }}
</label>
