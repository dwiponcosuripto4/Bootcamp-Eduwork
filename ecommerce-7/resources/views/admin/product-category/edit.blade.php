<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#7A0C0C] leading-tight">
            {{ __('Edit Product Category') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#FFF7F2] min-h-[70vh]">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-[#E53935]/20">
                <div class="p-6 text-[#7A0C0C]">
                    <form action="{{ route('product-categories.update', $productCategory->id) }}" method="POST"
                        class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="name" class="block text-sm font-medium mb-1">Nama Kategori</label>
                            <input type="text" id="name" name="name"
                                value="{{ old('name', $productCategory->name) }}"
                                class="w-full rounded-md border-[#E53935]/30 focus:border-[#E53935] focus:ring-[#E53935]"
                                required>
                            @error('name')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-[#E53935] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#c62828] focus:outline-none focus:ring-2 focus:ring-[#E53935] focus:ring-offset-2 transition ease-in-out duration-150">
                                Update
                            </button>
                            <a href="{{ route('product-categories.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition ease-in-out duration-150">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
