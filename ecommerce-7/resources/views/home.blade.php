@extends('template.layouts')
@section('title', 'Home')
@section('content')
    @php
        $selectedCategoryName = $productCategories->firstWhere('slug', $selectedCategory)?->name ?? 'Semua Produk';
    @endphp

    <div class="container py-4">
        <div class="d-flex gap-4 align-items-start">
            <div class="category-sidebar d-none d-lg-block flex-shrink-0" style="width: 200px;">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom fw-bold py-3">
                        Kategori
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li>
                            <a href="{{ route('home') }}"
                                class="d-flex align-items-center px-3 py-2 text-decoration-none category-link {{ !$selectedCategory ? 'active' : '' }}">
                                Semua Produk
                            </a>
                        </li>
                        @foreach ($productCategories as $productCategory)
                            <li>
                                <a href="{{ route('home', ['product_category' => $productCategory->slug]) }}"
                                    class="d-flex align-items-center px-3 py-2 text-decoration-none category-link {{ $selectedCategory === $productCategory->slug ? 'active' : '' }}">
                                    {{ $productCategory->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="flex-grow-1 min-w-0">
                <div class="d-lg-none mb-3">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('home') }}"
                            class="btn btn-sm {{ !$selectedCategory ? 'btn-dark' : 'btn-outline-secondary' }}">
                            Semua Produk
                        </a>
                        @foreach ($productCategories as $productCategory)
                            <a href="{{ route('home', ['product_category' => $productCategory->slug]) }}"
                                class="btn btn-sm {{ $selectedCategory === $productCategory->slug ? 'btn-dark' : 'btn-outline-secondary' }}">
                                {{ $productCategory->name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-semibold">
                        {{ $selectedCategoryName }}
                        <span class="text-muted fw-normal small">({{ $products->total() }} produk)</span>
                    </h5>
                </div>

                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-3 row-cols-xl-4 g-3">
                    @forelse ($products as $product)
                        <div class="col">
                            <x-product-card :name="$product->name" :price="$product->price" :image="$product->image" :category="$product->product_category?->name ?? 'Umum'"
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
                <div class="pagination-wrap mt-4">
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

        .pagination {
            gap: .45rem;
            margin-bottom: 0;
        }

        .pagination-wrap nav .d-none.flex-sm-fill.d-sm-flex {
            gap: 1.25rem;
        }

        .pagination-wrap nav .d-none.flex-sm-fill.d-sm-flex>div:first-child {
            margin-right: 1.25rem;
        }

        .pagination-wrap nav p.small.text-muted {
            margin-bottom: 0;
            white-space: nowrap;
        }

        .pagination .page-item .page-link {
            border: none;
            min-width: 42px;
            height: 42px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #7A0C0C;
            background: #fff;
            box-shadow: 0 2px 10px rgba(229, 57, 53, 0.14);
            transition: all .2s ease;
        }

        .pagination .page-item .page-link:hover {
            background: #FFF1F1;
            color: #E53935;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(229, 57, 53, 0.2);
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #E53935, #7A0C0C);
            color: #fff;
            box-shadow: 0 6px 16px rgba(122, 12, 12, 0.35);
        }

        .pagination .page-item.disabled .page-link {
            background: #f7f7f7;
            color: #b6b6b6;
            box-shadow: none;
            cursor: not-allowed;
        }
    </style>
@endsection
