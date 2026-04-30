<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#7A0C0C] leading-tight">
            {{ __('Product Category') }}
        </h2>
    </x-slot>
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
        <style>
            #productCategoriesTable_wrapper .dataTables_length label {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }

            #productCategoriesTable_wrapper .dataTables_length select {
                min-width: 5rem;
                padding-right: 2rem;
            }
        </style>
    @endpush

    <div class="py-12 bg-slate-50 min-h-[70vh]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-[#E53935]/20">
                <div class="p-6 text-[#7A0C0C]">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold">Daftar Kategori Produk</h3>
                        <x-primary-button x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'create-new-category')">{{ __('Tambah Kategori') }}</x-primary-button>
                    </div>

                    @if (session('success'))
                        <div class="mb-4 rounded-md bg-green-100 text-green-800 px-4 py-3 text-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table id="productCategoriesTable" class="display stripe hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Kategori</th>
                                    <th>Slug</th>
                                    <th>Jumlah Produk</th>
                                    <th>Total Stok</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($productCategories as $index => $productCategory)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $productCategory->name }}</td>
                                        <td>{{ $productCategory->slug }}</td>
                                        <td>{{ $productCategory->products_count }}</td>
                                        <td>{{ $productCategory->total_stock }}</td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <x-primary-button x-data=""
                                                    x-on:click.prevent="$dispatch('open-modal', 'edit-category.{{ $productCategory->id }}')">{{ __('Edit') }}</x-primary-button>

                                                <form
                                                    action="{{ route('product-categories.destroy', $productCategory->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus kategori ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 transition ease-in-out duration-150">
                                                        Hapus
                                                    </button>
                                                </form>
                                                <x-modal name="edit-category.{{ $productCategory->id }}" max-width="md"
                                                    focusable>
                                                    <form method="POST"
                                                        action="{{ route('product-categories.update', $productCategory->id) }}"
                                                        class="p-6">
                                                        @csrf
                                                        @method('PUT')
                                                        <h2 class="text-lg font-medium text-gray-900">
                                                            {{ __('Edit Category') }}
                                                        </h2>
                                                        <div class="mt-4">
                                                            <x-input-label for="name"
                                                                value="{{ __('Name') }}" />
                                                            <x-text-input id="name" name="name" type="text"
                                                                class="mt-1 block w-full"
                                                                value="{{ old('name', $productCategory->name) }}"
                                                                required />
                                                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                                        </div>
                                                        <div class="mt-4">
                                                            <x-input-label for="slug"
                                                                value="{{ __('Slug') }}" />
                                                            <x-text-input id="slug" name="slug" type="text"
                                                                class="mt-1 block w-full"
                                                                value="{{ old('slug', $productCategory->slug) }}"
                                                                required />
                                                            <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                                                        </div>
                                                        <div class="mt-6 flex justify-end">
                                                            <x-secondary-button x-on:click="$dispatch('close')">
                                                                {{ __('Cancel') }}
                                                            </x-secondary-button>
                                                            <x-primary-button class="ms-3" type="submit">
                                                                {{ __('Update') }}
                                                            </x-primary-button>
                                                        </div>
                                                    </form>
                                                </x-modal>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <x-modal name="create-new-category" max-width="md" focusable>
            <form method="POST" action="{{ route('product-categories.store') }}" class="p-6">
                @csrf
                <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Create New Category') }}
                </h2>
                <div class="mt-4">
                    <x-input-label for="name" value="{{ __('Name') }}" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                        value="{{ old('name') }}" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div class="mt-4">
                    <x-input-label for="slug" value="{{ __('Slug') }}" />
                    <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full"
                        value="{{ old('slug') }}" required />
                    <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                </div>
                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-primary-button class="ms-3" type="submit">
                        {{ __('Create') }}
                    </x-primary-button>
            </form>
        </x-modal>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#productCategoriesTable').DataTable({
                    pageLength: 10,
                    order: [
                        [0, 'asc']
                    ],
                    language: {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                        infoEmpty: 'Data tidak tersedia',
                        paginate: {
                            first: 'Awal',
                            last: 'Akhir',
                            next: 'Berikutnya',
                            previous: 'Sebelumnya'
                        },
                        zeroRecords: 'Data tidak ditemukan'
                    }
                });
            });
        </script>
    @endpush

</x-app-layout>
