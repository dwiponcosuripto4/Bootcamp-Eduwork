<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#7A0C0C] leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#FFF7F2] min-h-[70vh]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-[#E53935]/20">
                <div class="p-6 text-[#7A0C0C]">
                    <p class="text-lg font-semibold">{{ __("You're logged in!") }}</p>
                    <p class="mt-2 text-sm text-[#E53935]">Selamat datang kembali di dashboard Natlan Store.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
