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

    [x-cloak] {
        display: none !important;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container">
        <div class="row w-100 g-0 align-items-center position-relative" x-data="{ mobileSearchOpen: false }">
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
                    <button class="btn btn-outline-light border-0 p-1 ms-2 d-lg-none" type="button"
                        aria-controls="mobileSearch" :aria-expanded="mobileSearchOpen.toString()"
                        aria-label="Toggle search" @click="mobileSearchOpen = !mobileSearchOpen">
                        <i class="bi bi-search"></i>
                    </button>
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('cart.index') }}" class="btn btn-outline-light position-relative">
                                <i class="bi bi-cart3"></i>
                                @if ($cartTotalQty > 0)
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $cartTotalQty }}
                                    </span>
                                @endif
                            </a>
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="btn btn-sm btn-light d-inline-flex align-items-center">
                                        <span class="me-1">{{ Auth::user()->name }}</span>
                                        <svg class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            width="16" height="16">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    @if (Auth::user()->role !== 'customer')
                                        <x-dropdown-link :href="url('/dashboard')">
                                            {{ __('Dashboard') }}
                                        </x-dropdown-link>
                                    @endif
                                    <x-dropdown-link :href="route('profile.edit')">
                                        {{ __('Profile') }}
                                    </x-dropdown-link>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                            {{ __('Log Out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        @else
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="btn btn-sm btn-light d-inline-flex align-items-center">
                                        <span class="me-1">Account</span>
                                        <svg class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            width="16" height="16">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('login')">
                                        {{ __('Login') }}
                                    </x-dropdown-link>
                                    @if (Route::has('register'))
                                        <x-dropdown-link :href="route('register')">
                                            {{ __('Register') }}
                                        </x-dropdown-link>
                                    @endif
                                </x-slot>
                            </x-dropdown>
                        @endauth
                    @endif
                </div>

            </div>

            <div class="col-12">
                <div class="d-lg-none" id="mobileSearch" x-show="mobileSearchOpen" x-transition x-cloak>
                    <form method="GET" action="{{ url('/') }}" class="mt-3 mb-2" role="search">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Cari produk..."
                                value="{{ $currentSearch }}" aria-label="Search">
                            <button class="btn btn-light" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </div>
</nav>
