@php
    $cartTotalQty = 0;
    $currentSearch = request('search', '');
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
        <div class="row w-100 g-0 align-items-center position-relative">
            <div class="col-12 d-flex align-items-center justify-content-between">
                <a class="navbar-brand fw-bold mb-0" href="/" style="width: 180px;">Natlan Store</a>

                <div class="d-none d-lg-block position-absolute top-50 start-50"
                    style="width: 45vw; max-width: 620px; transform: translate(-50%, -50%);">
                    <form method="GET" action="{{ url('/') }}" class="w-100" role="search">
                        <div class="input-group w-100">
                            <input type="text" class="form-control" name="search" placeholder="Cari produk..."
                                value="{{ $currentSearch }}" aria-label="Search">
                            <button class="btn btn-light" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <ul class="navbar-nav d-none d-lg-flex flex-row mb-0">
                    </ul>

                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn btn-sm btn-light">Dashboard</a>
                            <a href="{{ route('cart.index') }}" class="btn btn-outline-light position-relative">
                                <i class="bi bi-cart3"></i>
                                @if ($cartTotalQty > 0)
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $cartTotalQty }}
                                    </span>
                                @endif
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light">Login</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-sm btn-light">Register</a>
                            @endif
                        @endauth
                    @endif
                    <button class="navbar-toggler border-0 p-1 ms-2" type="button" data-bs-toggle="collapse"
                        data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                </div>

            </div>

            <div class="col-12">
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <form method="GET" action="{{ url('/') }}" class="d-lg-none mt-3 mb-2" role="search">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Cari produk..."
                                value="{{ $currentSearch }}" aria-label="Search">
                            <button class="btn btn-light" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>

                    <ul class="navbar-nav d-lg-none mt-2">
                        @if (Route::has('login'))
                            @auth
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ url('/dashboard') }}">Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('cart.index') ? 'active' : '' }}"
                                        href="{{ route('cart.index') }}">Keranjang</a>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">Login</a>
                                </li>
                                @if (Route::has('register'))
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('register') }}">Register</a>
                                    </li>
                                @endif
                            @endauth
                        @endif
                    </ul>

                </div>

            </div>

        </div>
    </div>
</nav>
