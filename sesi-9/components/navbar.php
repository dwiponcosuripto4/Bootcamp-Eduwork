<?php
$currentPage = isset($navbarCurrentPage) ? (string) $navbarCurrentPage : '';
$basePath = isset($navbarBasePath) ? (string) $navbarBasePath : '';

$homeLink = $basePath . 'home.php';
$cartLink = $basePath . 'cart.php';
$productsLink = $basePath . 'admin/products/index.php';
$createProductLink = $basePath . 'admin/products/create.php';

// Get search and category params
$currentSearch = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$currentCategory = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : '';

// Get cart count
$cartTotalQty = 0;
if (session_status() === PHP_SESSION_ACTIVE) {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    foreach ($_SESSION['cart'] as $qty) {
        $cartTotalQty += (int) $qty;
    }
}
?>

<style>
    /* Custom Color Palette */
    :root {
        --dark-red: #7A0C0C;
        --red: #E53935;
        --light-pink: #FFF1F1;
        --accent-yellow: #FFDD5E;
    }

    /* Bootstrap Primary Color Override */
    .btn-primary {
        background-color: var(--red) !important;
        border-color: var(--red) !important;
        color: white !important;
    }

    .btn-primary:hover,
    .btn-primary:focus {
        background-color: var(--accent-yellow) !important;
        border-color: var(--accent-yellow) !important;
        color: #333 !important;
    }

    .bg-primary {
        background-color: var(--red) !important;
    }

    .text-primary {
        color: var(--red) !important;
    }

    .border-primary {
        border-color: var(--red) !important;
    }

    .badge.bg-primary {
        background-color: var(--red) !important;
    }

    /* Custom Navbar */
    .navbar-custom {
        background: linear-gradient(135deg, var(--red) 0%, var(--dark-red) 100%) !important;
        box-shadow: 0 2px 10px rgba(229, 57, 53, 0.15);
    }

    .navbar-custom .nav-link.active {
        color: var(--light-pink) !important;
        font-weight: 600;
    }

    .navbar-custom .nav-link:hover {
        color: var(--light-pink) !important;
    }

    /* Cart Badge with Red */
    .badge.bg-danger {
        background-color: var(--red) !important;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container">
        <div class="row w-100 g-0 align-items-center position-relative">
            <!-- Row 1: Logo, Search, Cart -->
            <div class="col-12 d-flex align-items-center justify-content-between">
                <!-- Logo -->
                <a class="navbar-brand fw-bold mb-0" href="<?php echo htmlspecialchars($homeLink); ?>" style="width: 180px;">
                    Natlan Store
                </a>

                <!-- Search Bar (Desktop Center) -->
                <div class="d-none d-lg-block position-absolute top-50 start-50" style="width: 45vw; max-width: 620px; transform: translate(-50%, -50%);">
                    <form method="GET" action="<?php echo htmlspecialchars($homeLink); ?>" class="w-100">
                        <div class="input-group w-100">
                            <input type="text"
                                class="form-control"
                                name="search"
                                placeholder="Cari produk..."
                                value="<?php echo $currentSearch; ?>"
                                aria-label="Search">
                            <button class="btn btn-light" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Side: Navigation Links + Cart + Menu Toggle -->
                <div class="d-flex align-items-center gap-0">
                    <!-- Navigation Links (Desktop) -->
                    <ul class="navbar-nav d-none d-lg-flex flex-row mb-0">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'home' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($homeLink); ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'products-index' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($productsLink); ?>">Produk</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'create-product' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($createProductLink); ?>">Tambah Produk</a>
                        </li>
                    </ul>

                    <!-- Cart Button -->
                    <a href="<?php echo htmlspecialchars($cartLink); ?>" class="btn btn-outline-light position-relative">
                        <i class="bi bi-cart3"></i>
                        <?php if ($cartTotalQty > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $cartTotalQty; ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- Mobile Menu Toggle -->
                    <button class="navbar-toggler border-0 p-1 ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
            </div>

            <!-- Row 2: Collapsible Menu -->
            <div class="col-12">
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <!-- Search Bar (Mobile) -->
                    <form method="GET" action="<?php echo htmlspecialchars($homeLink); ?>" class="d-lg-none mt-3 mb-2">
                        <div class="input-group">
                            <input type="text"
                                class="form-control"
                                name="search"
                                placeholder="Cari produk..."
                                value="<?php echo $currentSearch; ?>"
                                aria-label="Search">
                            <button class="btn btn-light" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Navigation Links (Mobile) -->
                    <ul class="navbar-nav d-lg-none">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'home' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($homeLink); ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'products-index' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($productsLink); ?>">Produk</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'create-product' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($createProductLink); ?>">Tambah Produk</a>
                        </li>
                    </ul>

                </div>
            </div>
        </div>
    </div>
</nav>