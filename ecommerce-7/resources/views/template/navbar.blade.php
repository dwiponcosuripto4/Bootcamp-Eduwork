@php
    $cartTotalQty = 0;
    $cart = session('cart', []);
    if (is_array($cart)) {
        foreach ($cart as $qty) {
            $cartTotalQty += (int) $qty;
        }
    }
@endphp

<style>
    :root {
        --dark-red: #7A0C0C;
        --red: #E53935;
        --light-pink: #FFF1F1;
        --accent-yellow: #FFDD5E;
    }

    .navbar-custom {
        background: linear-gradient(135deg, var(--red) 0%, var(--dark-red) 100%) !important;
        box-shadow: 0 2px 10px rgba(229, 57, 53, 0.15);
    }

    .navbar-custom .nav-link {
        color: #fff !important;
    }

    .navbar-custom .nav-link.active {
        color: var(--light-pink) !important;
        font-weight: 600;
    }

    .navbar-custom .nav-link:hover {
        color: var(--light-pink) !important;
    }

    .navbar-custom .badge.bg-danger {
        background-color: var(--red) !important;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container">
        <div class="row w-100 g-0 align-items-center">
            <div class="col-12 d-flex align-items-center justify-content-between">
                <a class="navbar-brand fw-bold mb-0" href="/" style="width: 180px;">Natlan Store</a>

                <div class="d-flex align-items-center gap-2">
                    <ul class="navbar-nav d-none d-lg-flex flex-row mb-0">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('cart.index') ? '' : 'active' }}"
                                href="/">Home</a>
                        </li>
                    </ul>

                    <a href="{{ route('cart.index') }}" class="btn btn-outline-light position-relative">
                        <i class="bi bi-cart3"></i>
                        @if ($cartTotalQty > 0)
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $cartTotalQty }}
                            </span>
                        @endif
                    </a>

                    <button class="navbar-toggler border-0 p-1 ms-2" type="button" data-bs-toggle="collapse"
                        data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
            </div>

            <div class="col-12">
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav d-lg-none mt-2">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('cart.index') ? '' : 'active' }}"
                                href="/">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('cart.index') ? 'active' : '' }}"
                                href="{{ route('cart.index') }}">Keranjang</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
