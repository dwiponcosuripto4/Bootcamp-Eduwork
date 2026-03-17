<div class="product-card card border-0 shadow-sm h-100 position-relative">
    <div class="position-relative overflow-hidden" style="aspect-ratio: 1 / 1;">
        <img src="{{ asset('images/' . $image) }}" class="w-100 h-100" alt="{{ $name }}" style="object-fit: cover;"
            onerror="this.onerror=null;this.src='https://via.placeholder.com/400x400?text=No+Image';">
        <span class="position-absolute top-0 start-0 m-2 badge"
            style="background: rgba(0, 0, 0, .45); font-size: .65rem; backdrop-filter: blur(2px);">
            {{ $category }}
        </span>
    </div>

    <div class="card-body p-2 d-flex flex-column">
        <p class="product-name mb-1 small fw-semibold lh-sm"
            style="display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.5em;">
            {{ $name }}
        </p>

        <p class="mb-1 fw-bold" style="color: #E53935; font-size: 1rem;">
            Rp {{ number_format((float) $price, 0, ',', '.') }}
        </p>

        <div class="d-flex align-items-center gap-2 mb-2" style="font-size: .72rem; color: #999;">
            <span style="color: #f8a740;">★★★★<span style="color: #ddd;">★</span></span>
            <span>120 terjual</span>
        </div>
        <a href="{{ route('product.detail', ['slug' => $slug]) }}" class="stretched-link"
            aria-label="Lihat detail {{ $name }}"></a>
        <a href="#" class="btn btn-sm w-100 mt-auto position-relative z-1"
            style="background: #E53935; color: #fff; font-size: .78rem; border: none;">
            + Keranjang
        </a>
    </div>


</div>
