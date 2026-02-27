<?php
session_start();

require_once 'koneksi.php';
require_once __DIR__ . '/components/template.php';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Filter and search params
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Get category list for filter dropdown
$categoryResult = $conn->query("SELECT DISTINCT category FROM products ORDER BY category ASC");
$categories = [];
if ($categoryResult) {
    while ($categoryRow = $categoryResult->fetch_assoc()) {
        $categories[] = $categoryRow['category'];
    }
}

// Build products query with optional filters
$sql = "SELECT * FROM products";
$conditions = [];
$params = [];
$types = '';

if ($category !== '') {
    $conditions[] = "category = ?";
    $params[] = $category;
    $types .= 's';
}

if ($search !== '') {
    $conditions[] = "name LIKE ?";
    $params[] = '%' . $search . '%';
    $types .= 's';
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$products = [];

// Debug $result
if (!$result) {
    die("Query failed: " . $conn->error);
}
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$cartTotalQty = 0;
foreach ($_SESSION['cart'] as $qty) {
    $cartTotalQty += (int) $qty;
}

renderTemplateStart('Daftar Produk - Toko Online', 'home', '');
?>
<div class="py-4">
    <div class="d-flex gap-4 align-items-start">

        <!-- Sidebar Kategori -->
        <div class="category-sidebar d-none d-lg-block flex-shrink-0" style="width:200px;">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom fw-bold py-3">
                    <i class="bi bi-grid-3x3-gap me-2"></i> Kategori
                </div>
                <ul class="list-unstyled mb-0">
                    <li>
                        <a href="?<?php echo $search !== '' ? 'search=' . urlencode($search) : ''; ?>"
                            class="d-flex align-items-center px-3 py-2 text-decoration-none category-link <?php echo $category === '' ? 'active' : ''; ?>">
                            <i class="bi bi-shop me-2 small"></i> Semua Produk
                        </a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="?<?php echo $search !== '' ? 'search=' . urlencode($search) . '&' : ''; ?>category=<?php echo urlencode($cat); ?>"
                                class="d-flex align-items-center px-3 py-2 text-decoration-none category-link <?php echo $category === $cat ? 'active' : ''; ?>">
                                <i class="bi bi-chevron-right me-2 small"></i>
                                <?php echo htmlspecialchars($cat); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 min-w-0">
            <!-- Mobile: Category dropdown -->
            <div class="d-lg-none mb-3">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="?<?php echo $search !== '' ? 'search=' . urlencode($search) : ''; ?>"
                        class="btn btn-sm <?php echo $category === '' ? 'btn-dark' : 'btn-outline-secondary'; ?>">
                        Semua
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="?<?php echo $search !== '' ? 'search=' . urlencode($search) . '&' : ''; ?>category=<?php echo urlencode($cat); ?>"
                            class="btn btn-sm <?php echo $category === $cat ? 'btn-dark' : 'btn-outline-secondary'; ?>">
                            <?php echo htmlspecialchars($cat); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-semibold">
                    <?php echo $category !== '' ? htmlspecialchars($category) : 'Semua Produk'; ?>
                    <span class="text-muted fw-normal small">(<?php echo count($products); ?> produk)</span>
                </h5>
            </div>

            <?php if (empty($products)): ?>
                <div class="alert alert-info text-center">Data produk tidak ditemukan.</div>
            <?php else: ?>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-3 row-cols-xl-4 g-3">
                    <?php foreach ($products as $product):
                        $imagePath = isset($product['image']) && !empty($product['image']) ? $product['image'] : '';
                        $imageUrl  = '';
                        $hasImage  = false;

                        if ($imagePath) {
                            $absoluteImagePath = __DIR__ . '/' . ltrim($imagePath, '/');
                            if (file_exists($absoluteImagePath)) {
                                $imageUrl = $imagePath;
                                $hasImage = true;
                            }
                        }

                        if (!$hasImage) {
                            $imageUrl = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="400"%3E%3Crect width="400" height="400" fill="%23e9ecef"/%3E%3Ctext x="50%25" y="50%25" font-size="18" text-anchor="middle" fill="%236c757d" dy=".3em"%3ENo Image%3C/text%3E%3C/svg%3E';
                        }
                    ?>
                        <div class="col">
                            <div class="product-card card border-0 shadow-sm h-100">
                                <!-- Image wrapper -->
                                <div class="position-relative overflow-hidden" style="aspect-ratio:1/1;">
                                    <img src="<?php echo $hasImage ? htmlspecialchars($imageUrl) : $imageUrl; ?>"
                                        class="w-100 h-100"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                                        style="object-fit:cover;"
                                        onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22400%22%3E%3Crect width=%22400%22 height=%22400%22 fill=%22%23e9ecef%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 font-size=%2218%22 text-anchor=%22middle%22 fill=%22%236c757d%22 dy=%22.3em%22%3ENo Image%3C/text%3E%3C/svg%3E';">
                                    <span class="position-absolute top-0 start-0 m-2 badge"
                                        style="background:rgba(0,0,0,.45); font-size:.65rem; backdrop-filter:blur(2px);">
                                        <?php echo htmlspecialchars($product['category']); ?>
                                    </span>
                                </div>

                                <!-- Card body -->
                                <div class="card-body p-2 d-flex flex-column">
                                    <p class="product-name mb-1 small fw-semibold lh-sm"
                                        style="display:-webkit-box;-webkit-line-clamp:2;line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:2.5em;">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </p>

                                    <p class="mb-1 fw-bold" style="color:#E53935; font-size:1rem;">
                                        Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                    </p>

                                    <div class="d-flex align-items-center gap-2 mb-2" style="font-size:.72rem; color:#999;">
                                        <span style="color:#f8a740;">★★★★<span style="color:#ddd;">★</span></span>
                                        <span><?php echo rand(5, 500); ?> terjual</span>
                                    </div>

                                    <form method="POST" action="cart.php" class="mt-auto">
                                        <input type="hidden" name="cart_action" value="add">
                                        <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                                        <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-sm w-100"
                                            style="background:#E53935; color:#fff; font-size:.78rem; border:none;">
                                            + Keranjang
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div><!-- /Main Content -->
    </div><!-- /d-flex -->
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

<?php renderTemplateEnd(); ?>