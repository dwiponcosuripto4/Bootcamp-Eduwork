<x-app-layout>
    <x-slot name="title">Edit Product | Laravel</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#7A0C0C] leading-tight">
            {{ __('Products') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-[70vh]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-[#E53935]/20">
                <div class="p-6 text-[#7A0C0C]">
                    <h3 class="text-lg font-semibold mb-6">Edit Produk</h3>
                    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data"
                        class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div>
                            <div>
                                <x-input-label for="name" :value="__('Nama Produk')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                    value="{{ old('name', $product->name) }}" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="slug" :value="__('Slug')" />
                                <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full"
                                    value="{{ old('slug', $product->slug) }}" required />
                                <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="description" :value="__('Deskripsi')" />
                                <textarea id="description" name="description"
                                    class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#E53935] focus:ring-[#E53935]" rows="4"
                                    required>{{ old('description', $product->description) }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="price" :value="__('Harga')" />
                                <x-text-input id="price" name="price" type="number" class="mt-1 block w-full"
                                    value="{{ old('price', $product->price) }}" required />
                                <x-input-error :messages="$errors->get('price')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="stock" :value="__('Stock')" />
                                <x-text-input id="stock" name="stock" type="number" class="mt-1 block w-full"
                                    value="{{ old('stock', $product->stock) }}" required />
                                <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="image" :value="__('Gambar')" />
                                <x-text-input id="image" name="image" type="file" class="mt-1 block w-full"
                                    accept="image/*" />
                                <div class="mt-3">
                                    <div id="croppie-container" class="hidden"></div>
                                    <div class="mt-3 flex items-center gap-2">
                                        <button type="button" id="crop-image"
                                            class="hidden inline-flex items-center px-3 py-2 bg-[#7A0C0C] text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-[#5F0A0A] transition">
                                            Crop 800x800
                                        </button>
                                        <span id="crop-status" class="text-xs text-gray-500"></span>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('image')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="product_category_id" :value="__('Kategori')" />
                                <select id="product_category_id" name="product_category_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#E53935] focus:ring-[#E53935]"
                                    required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($productCategories as $category)
                                        <option value="{{ $category->id }}" @selected(old('product_category_id', $product->product_category_id) == $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('product_category_id')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <x-primary-button type="submit">
                                {{ __('Simpan') }}
                            </x-primary-button>
                            <a href="{{ route('products.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Batal') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css">
    @endpush
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const imageInput = document.getElementById('image');
                const croppieContainer = document.getElementById('croppie-container');
                const cropButton = document.getElementById('crop-image');
                const cropStatus = document.getElementById('crop-status');
                const form = document.querySelector('form');
                let croppieInstance = null;

                const resetCroppie = () => {
                    if (croppieInstance) {
                        croppieInstance.destroy();
                        croppieInstance = null;
                    }
                    croppieContainer.classList.add('hidden');
                    cropButton.classList.add('hidden');
                    cropStatus.textContent = '';
                };

                // image validation + croppie setup
                imageInput.addEventListener('change', function() {
                    const file = this.files[0];
                    resetCroppie();

                    if (!file) {
                        return;
                    }

                    const fileType = file.type;
                    const validImageTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
                    if (!validImageTypes.includes(fileType)) {
                        alert('File harus berupa gambar (jpg, png, webp).');
                        this.value = '';
                        return;
                    }

                    // file size validation (max 2MB)
                    const maxSize = 2 * 1024 * 1024; // 2MB
                    if (file.size > maxSize) {
                        alert('Ukuran file tidak boleh lebih dari 2MB.');
                        this.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(event) {
                        croppieContainer.classList.remove('hidden');
                        cropButton.classList.remove('hidden');
                        croppieInstance = new Croppie(croppieContainer, {
                            viewport: {
                                width: 300,
                                height: 300,
                                type: 'square'
                            },
                            boundary: {
                                width: 360,
                                height: 360
                            },
                            enableOrientation: true,
                            minZoom: 0.5,
                            maxZoom: 2
                        });
                        croppieInstance.bind({
                            url: event.target.result
                        });
                        cropStatus.textContent = 'Silakan crop ke 1:1 sebelum simpan.';
                    };
                    reader.readAsDataURL(file);
                });

                cropButton.addEventListener('click', function() {
                    if (!croppieInstance) {
                        return;
                    }
                    croppieInstance.result({
                        type: 'blob',
                        size: {
                            width: 800,
                            height: 800
                        },
                        format: 'jpeg',
                        quality: 0.9
                    }).then(function(blob) {
                        const croppedFile = new File([blob], 'product-800x800.jpg', {
                            type: 'image/jpeg'
                        });
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(croppedFile);
                        imageInput.files = dataTransfer.files;
                        cropStatus.textContent = 'Crop selesai. Gambar siap diupload.';
                    });
                });

                form.addEventListener('submit', function(event) {
                    const price = document.getElementById('price').value;
                    const stock = document.getElementById('stock').value;

                    if (price < 0) {
                        alert('Harga tidak boleh negatif.');
                        event.preventDefault();
                        return;
                    }

                    if (stock < 0) {
                        alert('Stock tidak boleh negatif.');
                        event.preventDefault();
                        return;
                    }

                    if (imageInput.files.length && croppieInstance) {
                        cropStatus.textContent = 'Klik tombol Crop 800x800 dulu.';
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
