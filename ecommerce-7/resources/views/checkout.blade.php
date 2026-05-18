@extends('template.layouts')
@section('title', 'Checkout Page')
@section('content')
    <style>
        .checkout-hero {
            background: linear-gradient(135deg, #EAF3FF 0%, #ffffff 65%);
            border: 1px solid #dbe7f7;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
        }

        .checkout-title {
            font-weight: 700;
            color: #24324a;
            letter-spacing: .2px;
        }

        .checkout-card {
            background: #fff;
            border: 1px solid #f0f0f0;
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(122, 12, 12, 0.08);
        }

        .checkout-table thead th {
            font-size: .85rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #324a6a;
            border-bottom: 2px solid #e3ecf7;
        }

        .checkout-table tbody td {
            vertical-align: middle;
            border-color: #f6f1f1;
        }

        .price-badge {
            background: #E6F6F2;
            color: #0E6B5A;
            font-weight: 600;
            padding: .25rem .6rem;
            border-radius: 999px;
        }

        .summary-panel {
            border: 2px dashed #7fc8a9;
            background: #f0fbf5;
            border-radius: 14px;
            padding: 1rem 1.25rem;
        }

        .btn-checkout {
            background: #E53935;
            border: none;
            color: #fff;
            font-weight: 600;
            padding: .75rem 1.5rem;
            border-radius: 10px;
            transition: all .2s ease;
        }

        .btn-checkout:hover {
            background: #FFDD5E;
            color: #3b1b1b;
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(255, 221, 94, 0.4);
        }

        .address-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1rem;
        }
    </style>

    <div class="container py-4">
        <div
            class="checkout-hero mb-4 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <h2 class="checkout-title mb-1">Checkout</h2>
                <p class="text-muted mb-0">Konfirmasi pesanan dan lengkapi alamat pengiriman.</p>
            </div>
            <a href="/" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Lanjut Belanja
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if (isset($cartItems) && count($cartItems) > 0)
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="checkout-card p-3 p-md-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="mb-0 fw-bold">Ringkasan Item</h5>
                            <span class="text-muted small">Periksa detail produk sebelum bayar</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table checkout-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cartItems as $item)
                                        <tr>
                                            <td class="fw-semibold">{{ $item->product->name }}</td>
                                            <td><span class="price-badge">Rp
                                                    {{ number_format((float) $item->product->price, 0, ',', '.') }}</span>
                                            </td>
                                            <td>{{ $item->quantity }}</td>
                                            <td class="fw-semibold">Rp
                                                {{ number_format((float) ($item->product->price * $item->quantity), 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="checkout-card p-3 p-md-4">
                        <h5 class="fw-bold mb-3">Alamat Pengiriman</h5>
                        <form method="POST" action="{{ route('make.order') }}">
                            @csrf
                            <div class="address-card">
                                <label for="address" class="form-label small fw-semibold">Alamat</label>
                                <textarea class="form-control" id="address" name="address" rows="4" required
                                    placeholder="Masukkan alamat lengkap"></textarea>
                            </div>
                            <div class="summary-panel mt-3">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Total Pembayaran</span>
                                    <span class="fw-bold">Rp {{ number_format((float) $cartTotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <button type="submit" class="btn-checkout w-100 mt-3">
                                <i class="bi bi-bag-check me-2"></i>Proses Checkout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <div class="checkout-card p-4 text-center">
                <i class="bi bi-cart-x" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3 mb-0">Keranjang kamu masih kosong.</p>
            </div>
        @endif
    </div>
@endsection
