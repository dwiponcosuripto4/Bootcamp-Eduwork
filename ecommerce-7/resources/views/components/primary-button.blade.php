<button
    {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#FFDD5E] border border-transparent rounded-md font-semibold text-xs text-black hover:text-white uppercase tracking-widest hover:bg-[#7A0C0C] focus:bg-[#7A0C0C] active:bg-[#7A0C0C] focus:outline-none focus:ring-2 focus:ring-[#FFDD5E] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
