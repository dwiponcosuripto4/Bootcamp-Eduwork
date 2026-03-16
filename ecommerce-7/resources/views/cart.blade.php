@extends('template.layouts')
@section('title', 'Shopping Cart')
@section('content')
    @php
        $successMessage = 'Produk ditambahkan ke keranjang.';
        $errors = [];

        $cartItems = [
            [
                'id' => 1,
                'name' => 'Sepatu Lari Pro Max',
                'price' => 250000,
                'qty' => 2,
                'image' =>
                    'https://cdn.getswift.asia/unsafe/500x500/filters:format(webp):quality(80)/https://bo.asics.co.id/media/catalog/product/cache/4a5bef1eb0b3e9b20c2d6e32e87a7fc1/1/2/1203a763.100_3.jpg',
                'category' => 'Sepatu',
            ],
            [
                'id' => 2,
                'name' => 'Tas Ransel Urban Daily',
                'price' => 320000,
                'qty' => 1,
                'image' => 'https://img.lazcdn.com/g/p/35002e4af2fe096ceb96e56c7ccf3e44.png_720x720q80.png',
                'category' => 'Aksesoris',
            ],
        ];

        $cartGrandTotal = 0;
        $cartTotalQty = 0;
        foreach ($cartItems as &$item) {
            $item['line_total'] = $item['price'] * $item['qty'];
            $cartGrandTotal += $item['line_total'];
            $cartTotalQty += $item['qty'];
        }
        unset($item);
    @endphp

    <style>
        .cart-header {
            background: #fff;
            padding: 1rem 0;
            border-bottom: 1px solid #e5e5e5;
            margin-bottom: 1rem;
        }

        .cart-item {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: .75rem;
            transition: box-shadow .2s, border-color .2s;
        }

        .cart-item:hover {
            box-shadow: 0 2px 12px rgba(255, 221, 94, 0.2);
            border-color: #FFDD5E;
        }

        .cart-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #f0f0f0;
        }

        .qty-control {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
            width: fit-content;
        }

        .qty-control button {
            border: none;
            background: #fff;
            padding: .25rem .5rem;
            cursor: pointer;
            color: #888;
            transition: all .2s;
        }

        .qty-control button:hover {
            background: #f5f5f5;
            color: #E53935;
        }

        .qty-control input {
            border: none;
            width: 50px;
            text-align: center;
            font-weight: 600;
        }

        .summary-card {
            background: #fff;
            border: 2px solid #FFDD5E;
            border-radius: 8px;
            padding: 1.25rem;
            position: sticky;
            top: 80px;
            box-shadow: 0 4px 15px rgba(255, 221, 94, 0.15);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: .5rem 0;
        }

        .summary-total {
            font-size: 1.25rem;
            color: #E53935;
            font-weight: 700;
        }

        .btn-checkout {
            background: #E53935;
            color: #fff;
            border: none;
            padding: .75rem 2rem;
            border-radius: 4px;
            font-weight: 600;
            transition: all .2s;
        }

        .btn-checkout:hover {
            background: #FFDD5E;
            color: #333;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 221, 94, 0.4);
        }

        .empty-cart {
            text-align: center;
            padding: 3rem 1rem;
        }

        .customer-form {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
    </style>

    <div class="container py-3">
        <div class="cart-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold"><i class="bi bi-cart3 me-2"></i>Keranjang Belanja</h4>
                    <a href="/" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Lanjut Belanja
                    </a>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            @if ($successMessage !== '')
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ $successMessage }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (!empty($errors))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <ul class="mb-0">
                        @foreach ($errors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (empty($cartItems))
                <div class="empty-cart">
                    <div class="card border-0 shadow-sm py-5">
                        <div class="card-body">
                            <i class="bi bi-cart-x" style="font-size: 5rem; color: #ccc;"></i>
                            <h5 class="mt-3 mb-2">Keranjang Belanja Kosong</h5>
                            <p class="text-muted mb-4">Yuk, isi keranjangmu dengan produk-produk menarik!</p>
                            <a href="/" class="btn btn-primary">
                                <i class="bi bi-shop me-2"></i>Belanja Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="row g-3">
                    <div class="col-lg-8">
                        @foreach ($cartItems as $cartItem)
                            <div class="cart-item">
                                <div class="row g-3 align-items-center">
                                    <div class="col-auto">
                                        <img src="{{ $cartItem['image'] }}" alt="{{ $cartItem['name'] }}" class="cart-img"
                                            onerror="this.onerror=null;this.src='https://via.placeholder.com/80x80?text=No+Image';">
                                    </div>

                                    <div class="col">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="mb-1 fw-semibold">{{ $cartItem['name'] }}</h6>
                                                <p class="mb-2 small text-muted">
                                                    <span
                                                        class="badge bg-light text-dark">{{ $cartItem['category'] }}</span>
                                                </p>
                                                <p class="mb-0 fw-bold" style="color: #E53935; font-size: 1.1rem;">
                                                    Rp {{ number_format((float) $cartItem['price'], 0, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-auto">
                                        <div
                                            class="d-flex align-items-center gap-3 justify-content-between justify-content-md-end">
                                            <form class="qty-control" onsubmit="return false;">
                                                <button type="button" onclick="decreaseQty('qty-{{ $cartItem['id'] }}')">
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                                <input type="number" id="qty-{{ $cartItem['id'] }}"
                                                    value="{{ (int) $cartItem['qty'] }}" min="1" readonly>
                                                <button type="button" onclick="increaseQty('qty-{{ $cartItem['id'] }}')">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </form>

                                            <div class="text-end" style="min-width: 120px;">
                                                <small class="text-muted d-block">Total</small>
                                                <strong class="text-dark">Rp
                                                    {{ number_format((float) $cartItem['line_total'], 0, ',', '.') }}</strong>
                                            </div>

                                            <button type="button" class="btn btn-sm btn-light text-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="col-lg-4">
                        <div class="summary-card">
                            <h5 class="mb-3 fw-bold">Ringkasan Belanja</h5>

                            <div class="summary-row border-bottom">
                                <span class="text-muted">Total Item</span>
                                <span class="fw-semibold">{{ $cartTotalQty }} produk</span>
                            </div>

                            <div class="summary-row border-bottom pb-3">
                                <span class="text-muted">Total Harga</span>
                                <span class="fw-bold">Rp {{ number_format((float) $cartGrandTotal, 0, ',', '.') }}</span>
                            </div>

                            <div class="summary-row pt-2">
                                <span class="fw-bold fs-6">Total Pembayaran</span>
                                <span class="summary-total">Rp
                                    {{ number_format((float) $cartGrandTotal, 0, ',', '.') }}</span>
                            </div>

                            <form class="mt-4" onsubmit="return false;">
                                <div class="customer-form">
                                    <h6 class="mb-3 fw-bold"><i class="bi bi-person-circle me-2"></i>Data Pembeli</h6>

                                    <div class="mb-2">
                                        <label class="form-label small mb-1">Nama Lengkap <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm"
                                            placeholder="Nama lengkap">
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label small mb-1">Email <span
                                                class="text-danger">*</span></label>
                                        <input type="email" class="form-control form-control-sm"
                                            placeholder="email@example.com">
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label small mb-1">Nomor Telepon <span
                                                class="text-danger">*</span></label>
                                        <input type="tel" class="form-control form-control-sm"
                                            placeholder="08xxxxxxxxxx">
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label small mb-1">Alamat <span
                                                class="text-danger">*</span></label>
                                        <textarea class="form-control form-control-sm" rows="2" placeholder="Alamat lengkap"></textarea>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-checkout w-100 mt-3">
                                    <i class="bi bi-bag-check me-2"></i>Checkout ({{ $cartTotalQty }} Item)
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function increaseQty(inputId) {
            const input = document.getElementById(inputId);
            input.value = parseInt(input.value, 10) + 1;
        }

        function decreaseQty(inputId) {
            const input = document.getElementById(inputId);
            const value = parseInt(input.value, 10);
            if (value > 1) {
                input.value = value - 1;
            }
        }
    </script>
@endsection
