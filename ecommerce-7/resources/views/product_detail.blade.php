@extends('template.layouts')

@section('title', $product->name)
@section('content')
    <div class="container py-4">
        <div class="mb-3">
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">&larr; Kembali</a>
        </div>

        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="row g-0">
                <div class="col-lg-5">
                    <div class="h-100" style="background: #f8f9fa;">
                        <img src="{{ asset('images/' . $product->image) }}" alt="{{ $product->name }}" class="w-100 h-100"
                            style="object-fit: cover; min-height: 320px;"
                            onerror="this.onerror=null;this.src='https://via.placeholder.com/800x800?text=No+Image';">
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="p-4 p-lg-5 d-flex flex-column h-100">
                        <div class="mb-3">
                            <span class="badge text-bg-light border">
                                {{ optional($product->product_category)->name ?? 'Umum' }}
                            </span>
                        </div>

                        <h1 class="h3 fw-bold mb-2">{{ $product->name }}</h1>

                        <p class="fw-bold mb-3" style="font-size: 1.5rem; color: #E53935;">
                            Rp {{ number_format((float) $product->price, 0, ',', '.') }}
                        </p>

                        <div class="mb-4">
                            <span class="text-muted">Stok: </span>
                            <span class="fw-semibold">{{ $product->stock }}</span>
                        </div>

                        <div class="mb-4">
                            <h2 class="h6 fw-bold">Deskripsi Produk</h2>
                            <p class="text-muted mb-0" style="line-height: 1.7;">
                                {{ $product->description }}
                            </p>
                        </div>

                        <div class="mt-auto d-flex gap-2 flex-wrap">
                            <a href="#" class="btn btn-danger px-4">+ Keranjang</a>
                            <a href="{{ route('cart.index') }}" class="btn btn-outline-dark px-4">Lihat Keranjang</a>
                        </div>
                        <div class="col-12">
                            <h2 class="h6 fw-bold mt-4">Produk Terkait</h2>
                            <div class="row g-3 mt-2">
                                @foreach ($prouct_recommendation as $item)
                                    <div class="col-md-3">
                                        <x-product-card :name="$item->name" :price="$item->price" :image="$item->image"
                                            :category="$item->product_category->name ?? 'Umum'" :slug="$item->slug" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
