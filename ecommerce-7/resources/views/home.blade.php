@extends('template.layouts')
@section('title', 'Home')
@section('content')
    @php
        $categories = ['Semua Produk', 'Sepatu', 'Fashion', 'Aksesoris'];
    @endphp

    <div class="container py-4">
        <div class="d-flex gap-4 align-items-start">
            <div class="category-sidebar d-none d-lg-block flex-shrink-0" style="width: 200px;">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom fw-bold py-3">
                        Kategori
                    </div>
                    <ul class="list-unstyled mb-0">
                        @foreach ($categories as $index => $category)
                            <li>
                                <a href="#"
                                    class="d-flex align-items-center px-3 py-2 text-decoration-none category-link {{ $index === 0 ? 'active' : '' }}">
                                    {{ $category }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="flex-grow-1 min-w-0">
                <div class="d-lg-none mb-3">
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach ($categories as $index => $category)
                            <a href="#" class="btn btn-sm {{ $index === 0 ? 'btn-dark' : 'btn-outline-secondary' }}">
                                {{ $category }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-semibold">
                        Semua Produk
                        <span class="text-muted fw-normal small">({{ count($products) }} produk)</span>
                    </h5>
                </div>

                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-3 row-cols-xl-4 g-3">
                    @forelse ($products as $product)
                        <div class="col">
                            <x-product-card :name="$product->name" :price="$product->price" :image="$product->image" :category="$product->category ?? 'Umum'"
                                :slug="$product->slug" />
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-warning mb-0" role="alert">
                                Produk tidak ditemukan.
                            </div>
                        </div>
                    @endforelse
                </div>
                <div class="d-flex justify-content-center mt-4">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .product-card {
            border-radius: .5rem;
            overflow: hidden;
            transition: box-shadow .2s, transform .2s;
            border: 2px solid transparent;
        }

        .product-card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, .15) !important;
            transform: translateY(-2px);
            border-color: #FFDD5E;
        }

        .category-link {
            color: #333;
            font-size: .875rem;
            border-left: 3px solid transparent;
            transition: all .15s;
        }

        .category-link:hover {
            background: #FFF1F1;
            color: #E53935;
            border-left-color: #E53935;
        }

        .category-link.active {
            background: #FFF1F1;
            color: #E53935;
            font-weight: 600;
            border-left-color: #FFDD5E;
            border-left-width: 4px;
        }
    </style>
@endsection
