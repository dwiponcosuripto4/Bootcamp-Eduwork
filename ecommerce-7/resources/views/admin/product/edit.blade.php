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
                                <x-text-input id="image" name="image_file" type="file" class="mt-1 block w-full"
                                    accept="image/*" />
                                <input type="hidden" id="image_cropped" name="image" />
                                @if ($product->image)
                                    <div class="mt-3">
                                        <p class="text-xs text-gray-500">Gambar saat ini</p>
                                        <img src="{{ Storage::disk('images')->url($product->image) }}"
                                            alt="{{ $product->name }}"
                                            class="mt-2 h-24 w-24 rounded-md border border-gray-200 object-cover" />
                                    </div>
                                @endif
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
            document.addEventListener('DOMContentLoaded', () => {
                const imageInput = document.getElementById('image');
                const imageCropped = document.getElementById('image_cropped');
                const croppieContainer = document.getElementById('croppie-container');
                const cropButton = document.getElementById('crop-image');
                const cropStatus = document.getElementById('crop-status');
                const form = document.querySelector('form');

                let croppieInstance = null;
                let outputFormat = 'webp';

                const resetCroppie = () => {
                    if (croppieInstance) {
                        croppieInstance.destroy();
                        croppieInstance = null;
                    }
                    croppieContainer.innerHTML = '';
                    croppieContainer.classList.add('hidden');
                    cropButton.classList.add('hidden');
                    cropStatus.textContent = '';
                    imageCropped.value = '';
                };

                imageInput.addEventListener('change', (event) => {
                    const file = event.target.files && event.target.files[0];
                    if (!file) {
                        resetCroppie();
                        return;
                    }

                    const fileType = file.type;
                    const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
                    if (!validTypes.includes(fileType)) {
                        resetCroppie();
                        cropStatus.textContent = 'Format gambar tidak didukung.';
                        return;
                    }

                    outputFormat = fileType === 'image/png' ? 'png' : (fileType === 'image/webp' ? 'webp' :
                        'jpeg');

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        resetCroppie();
                        croppieContainer.classList.remove('hidden');
                        cropButton.classList.remove('hidden');
                        cropStatus.textContent = 'Silakan crop gambar.';

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
                            maxZoom: 2,
                        });

                        croppieInstance.bind({
                            url: e.target.result
                        });
                    };
                    reader.readAsDataURL(file);
                });

                cropButton.addEventListener('click', async () => {
                    if (!croppieInstance) {
                        return;
                    }

                    cropStatus.textContent = 'Memproses crop...';

                    const result = await croppieInstance.result({
                        type: 'base64',
                        size: {
                            width: 800,
                            height: 800
                        },
                        format: outputFormat,
                        quality: 0.9,
                    });

                    imageCropped.value = result;
                    cropStatus.textContent = 'Gambar siap disimpan.';
                });

                form.addEventListener('submit', async (event) => {
                    if (!croppieInstance || imageCropped.value) {
                        return;
                    }

                    event.preventDefault();
                    cropStatus.textContent = 'Memproses crop...';

                    const result = await croppieInstance.result({
                        type: 'base64',
                        size: {
                            width: 800,
                            height: 800
                        },
                        format: outputFormat,
                        quality: 0.9,
                    });

                    imageCropped.value = result;
                    cropStatus.textContent = 'Gambar siap disimpan.';
                    form.submit();
                });
            });
        </script>
    @endpush
</x-app-layout>
