@extends('template.layouts')
@section('title', 'Order Page')
@section('content')
    <style>
        .order-hero {
            background: linear-gradient(135deg, #EEF5F2 0%, #ffffff 70%);
            border: 1px solid #d8e6e0;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
        }

        .order-title {
            font-weight: 700;
            color: #1f3a36;
            letter-spacing: .2px;
        }

        .order-card {
            background: #fff;
            border: 1px solid #f0f0f0;
            border-radius: 14px;
            box-shadow: 0 8px 22px rgba(122, 12, 12, 0.08);
        }

        .order-meta {
            background: #FFF7F7;
            border: 1px dashed #f2c9c9;
            border-radius: 12px;
            padding: .85rem 1rem;
        }

        .order-table thead th {
            font-size: .85rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #2e4a45;
            border-bottom: 2px solid #e3ece8;
        }

        .order-table tbody td {
            vertical-align: middle;
            border-color: #f6f1f1;
        }

        .total-chip {
            background: #DDEFE8;
            color: #1f3a36;
            font-weight: 700;
            padding: .35rem .75rem;
            border-radius: 999px;
        }

        .btn-wa {
            background: #25D366;
            border: none;
            color: #fff;
            font-weight: 600;
            padding: .7rem 1.25rem;
            border-radius: 10px;
            transition: all .2s ease;
        }

        .btn-wa:hover {
            background: #1fb458;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(37, 211, 102, 0.35);
        }
    </style>

    <div class="container py-4">
        <div
            class="order-hero mb-4 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <h2 class="order-title mb-1">Detail Pesanan</h2>
                <p class="text-muted mb-0">Terima kasih! Pesanan kamu sudah tercatat.</p>
            </div>
            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Pesanan
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="order-card p-3 p-md-4">
                    <h5 class="fw-bold mb-3">Ringkasan Pesanan</h5>
                    <div class="order-meta">
                        <div class="mb-2">
                            <div class="text-muted small">Nomor Pesanan</div>
                            <div class="fw-bold">{{ $order->order_number }}</div>
                        </div>
                        <div class="mb-2">
                            <div class="text-muted small">Tanggal</div>
                            <div class="fw-semibold">{{ $order->created_at->format('d M Y, H:i') }}</div>
                        </div>
                        <div>
                            <div class="text-muted small">Alamat Pengiriman</div>
                            <div class="fw-semibold">{{ $order->shipping_address }}</div>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <span class="text-muted">Total</span>
                        <span class="total-chip">Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="order-card p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0 fw-bold">Item Pesanan</h5>
                        <span class="text-muted small">Cek detail produk di bawah</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table order-table mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td class="fw-semibold">{{ $item->product->name }}</td>
                                        <td>Rp {{ number_format((float) $item->product->price, 0, ',', '.') }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td class="fw-semibold">Rp
                                            {{ number_format((float) ($item->product->price * $item->quantity), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <a href="https://wa.me/6281234567890?text=Halo%20Admin%2C%20saya%20ingin%20mengkonfirmasi%20pesanan%20dengan%20nomor%20{{ $order->order_number }}.%20Apakah%20pesanan%20saya%20sudah%20diproses%3F"
                            class="btn-wa" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-whatsapp me-2"></i>Konfirmasi via WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
