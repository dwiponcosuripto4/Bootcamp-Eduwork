<?php
require_once 'koneksi.php';

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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Produk - Toko Online</title>
    <!-- Bootstrap 5 CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet" />
</head>

<body>
    <div class="container py-5">
        <h1 class="mb-4 text-center">Daftar Produk</h1>

        <form method="GET" class="card card-body mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-5">
                    <label for="search" class="form-label">Cari Nama Produk</label>
                    <input
                        type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        placeholder="Contoh: Laptop"
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-12 col-md-4">
                    <label for="category" class="form-label">Filter Kategori</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-grid">
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                </div>
            </div>
        </form>

        <?php if (empty($products)): ?>
            <div class="alert alert-info text-center">Data produk tidak ditemukan.</div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($products as $product):
                    // Check if image exists in storage
                    $imagePath = isset($product['image']) && !empty($product['image']) ? $product['image'] : '';
                    $imageUrl = '';
                    $hasImage = false;

                    if ($imagePath && file_exists($imagePath)) {
                        $imageUrl = $imagePath;
                        $hasImage = true;
                    } else {
                        // Use base64 placeholder image (1x1 gray pixel)
                        $imageUrl = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="300"%3E%3Crect width="400" height="300" fill="%23e9ecef"/%3E%3Ctext x="50%25" y="50%25" font-size="20" text-anchor="middle" fill="%236c757d" dy=".3em"%3ENo Image%3C/text%3E%3C/svg%3E';
                    }
                ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm">
                            <img src="<?php echo $hasImage ? htmlspecialchars($imageUrl) : $imageUrl; ?>"
                                class="card-img-top"
                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                style="height: 200px; object-fit: cover; background-color: #e9ecef;"
                                onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22%3E%3Crect width=%22400%22 height=%22300%22 fill=%22%23e9ecef%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 font-size=%2220%22 text-anchor=%22middle%22 fill=%22%236c757d%22 dy=%22.3em%22%3ENo Image%3C/text%3E%3C/svg%3E';">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($product['category']); ?></span>
                                </div>
                                <p class="card-text text-muted flex-grow-1"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                                <div class="mt-3">
                                    <h4 class="text-success mb-2">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></h4>
                                    <p class="mb-0 text-secondary"><small><i class="bi bi-box-seam"></i> Stock: <?php echo htmlspecialchars($product['stock']); ?> unit</small></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>