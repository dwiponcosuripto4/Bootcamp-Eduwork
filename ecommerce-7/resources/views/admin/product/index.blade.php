<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#7A0C0C] leading-tight">
            {{ __('Products') }}
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
                        <h3 class="text-lg font-semibold">Daftar Produk</h3>
                        <a href="{{ route('products.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-[#E53935] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#c62828] focus:outline-none focus:ring-2 focus:ring-[#E53935] focus:ring-offset-2 transition ease-in-out duration-150">
                            Tambah Produk
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="mb-4 rounded-md bg-green-100 text-green-800 px-4 py-3 text-sm">
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="mb-4">
                        <form action="{{ route('products.index') }}" method="GET">
                            <input type="text" name="search" placeholder="Cari produk..."
                                value="{{ request('search') }}"
                                class="border border-gray-300 rounded-md py-2 px-4 focus:outline-none focus:ring-2 focus:ring-[#E53935]">
                            <button type="submit"
                                class="ml-2 bg-[#E53935] text-white py-2 px-4 rounded-md hover:bg-[#c62828]">
                                Cari
                            </button>
                        </form>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-gray-300">
                            <thead class="bg-[#7A0C0C] text-white">
                                <tr>
                                    <th class="border border-gray-300 px-4 py-2 text-left">No</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Nama Produk</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Slug</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Deskripsi</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Harga</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Stock</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Gambar</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Kategori</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr class="hover:bg-gray-50 border-b border-gray-300">
                                        <td class="border border-gray-300 px-4 py-2">{{ $loop->iteration }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $product->name }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $product->slug }}</td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            {{ Str::limit($product->description, 50) }}</td>
                                        <td class="border border-gray-300 px-4 py-2">Rp
                                            {{ number_format($product->price, 2, ',', '.') }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $product->stock }}</td>
                                        <td class="border border-gray-300 px-4 py-2"><img
                                                src="{{ asset('images/' . $product->image) }}"
                                                alt="{{ $product->name }}" class="w-16 h-16 object-cover rounded"></td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            {{ $product->product_category ? $product->product_category->name : 'Uncategorized' }}
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('product.detail', ['slug' => $product->slug]) }}"
                                                    class="inline-flex items-center px-3 py-1.5 bg-blue-500 text-white text-xs font-semibold rounded hover:bg-blue-600 transition">Lihat</a>
                                                <a href="{{ route('products.edit', $product->id) }}"
                                                    class="inline-flex items-center px-3 py-1.5 bg-amber-500 text-white text-xs font-semibold rounded hover:bg-amber-600 transition">Edit</a>
                                                <form action="{{ route('products.destroy', $product->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 bg-red-500 text-white text-xs font-semibold rounded hover:bg-red-600 transition">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $products->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
