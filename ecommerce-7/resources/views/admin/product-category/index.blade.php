<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#7A0C0C] leading-tight">
            {{ __('Product Category') }}
        </h2>
    </x-slot>
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    @endpush

    <div class="py-12 bg-[#FFF7F2] min-h-[70vh]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-[#E53935]/20">
                <div class="p-6 text-[#7A0C0C]">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold">Daftar Kategori Produk</h3>
                        <a href="{{ route('product-categories.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-[#E53935] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#c62828] focus:outline-none focus:ring-2 focus:ring-[#E53935] focus:ring-offset-2 transition ease-in-out duration-150">
                            Tambah kategori
                        </a>
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
                                @foreach ($productCategories as $index => $category)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->slug }}</td>
                                        <td>{{ $category->products_count }}</td>
                                        <td>{{ $category->total_stock }}</td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('product-categories.edit', $category->id) }}"
                                                    class="inline-flex items-center px-3 py-1.5 bg-amber-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                    Edit
                                                </a>

                                                <form action="{{ route('product-categories.destroy', $category->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus kategori ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 transition ease-in-out duration-150">
                                                        Hapus
                                                    </button>
                                                </form>
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
