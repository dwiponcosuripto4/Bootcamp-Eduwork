<?php
session_start();

require_once 'koneksi.php';
require_once __DIR__ . '/components/template.php';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$errors = [];
$successMessage = '';

$redirectTo = 'cart.php';
if (isset($_POST['redirect_to']) && is_string($_POST['redirect_to']) && $_POST['redirect_to'] !== '') {
    $candidate = $_POST['redirect_to'];
    if (strpos($candidate, 'http://') !== 0 && strpos($candidate, 'https://') !== 0 && strpos($candidate, '//') !== 0) {
        $redirectTo = $candidate;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_action'])) {
    $cartAction = isset($_POST['cart_action']) ? trim((string) $_POST['cart_action']) : '';

    if ($cartAction === 'add') {
        $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;
        if ($quantity < 1) {
            $quantity = 1;
        }

        if ($productId > 0) {
            if (!isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId] = 0;
            }
            $_SESSION['cart'][$productId] += $quantity;

            $separator = strpos($redirectTo, '?') === false ? '?' : '&';
            header('Location: ' . $redirectTo . $separator . 'cart=added');
            exit;
        }
    }

    if ($cartAction === 'update') {
        $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;

        if ($productId > 0) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$productId]);
            } else {
                $_SESSION['cart'][$productId] = $quantity;
            }

            header('Location: cart.php?cart=updated');
            exit;
        }
    }

    if ($cartAction === 'remove') {
        $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
        if ($productId > 0) {
            unset($_SESSION['cart'][$productId]);
            header('Location: cart.php?cart=removed');
            exit;
        }
    }

    if ($cartAction === 'checkout') {
        if (empty($_SESSION['cart'])) {
            $errors[] = 'Keranjang masih kosong.';
        } else {
            // Validasi data customer
            $customerName = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
            $customerEmail = isset($_POST['customer_email']) ? trim($_POST['customer_email']) : '';
            $customerPhone = isset($_POST['customer_phone']) ? trim($_POST['customer_phone']) : '';
            $customerAddress = isset($_POST['customer_address']) ? trim($_POST['customer_address']) : '';

            $checkoutErrors = [];
            if ($customerName === '') {
                $checkoutErrors[] = 'Nama harus diisi.';
            }
            if ($customerEmail === '') {
                $checkoutErrors[] = 'Email harus diisi.';
            } elseif (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
                $checkoutErrors[] = 'Format email tidak valid.';
            }
            if ($customerPhone === '') {
                $checkoutErrors[] = 'Nomor telepon harus diisi.';
            }
            if ($customerAddress === '') {
                $checkoutErrors[] = 'Alamat harus diisi.';
            }

            if (!empty($checkoutErrors)) {
                $errors = array_merge($errors, $checkoutErrors);
            } else {
                $cartProductIds = array_keys($_SESSION['cart']);
                $placeholders = implode(',', array_fill(0, count($cartProductIds), '?'));
                $types = str_repeat('i', count($cartProductIds));

                $productSql = "SELECT id, name, price FROM products WHERE id IN ($placeholders)";
                $productStmt = $conn->prepare($productSql);
                if (!$productStmt) {
                    $errors[] = 'Gagal memproses checkout.';
                } else {
                    $productStmt->bind_param($types, ...$cartProductIds);
                    $productStmt->execute();
                    $productResult = $productStmt->get_result();
                    $productMap = [];

                    if ($productResult) {
                        while ($item = $productResult->fetch_assoc()) {
                            $productMap[(int) $item['id']] = $item;
                        }
                    }
                    $productStmt->close();

                    $itemsToInsert = [];
                    $transactionTotal = 0;

                    foreach ($_SESSION['cart'] as $productId => $quantity) {
                        $productId = (int) $productId;
                        $quantity = (int) $quantity;

                        if ($quantity < 1 || !isset($productMap[$productId])) {
                            continue;
                        }

                        $price = (float) $productMap[$productId]['price'];
                        $lineTotal = $price * $quantity;
                        $transactionTotal += $lineTotal;

                        $itemsToInsert[] = [
                            'product_id' => $productId,
                            'quantity' => $quantity,
                            'total_price' => $lineTotal,
                        ];
                    }

                    if (empty($itemsToInsert)) {
                        $errors[] = 'Item keranjang tidak valid untuk checkout.';
                    } else {
                        $status = 'paid';
                        $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

                        $conn->begin_transaction();
                        try {
                            $transactionStmt = $conn->prepare('INSERT INTO transactions (status, total, user_id, customer_name, customer_email, customer_phone, customer_address, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
                            if (!$transactionStmt) {
                                throw new Exception('Gagal membuat transaksi.');
                            }

                            $transactionStmt->bind_param('sdissss', $status, $transactionTotal, $userId, $customerName, $customerEmail, $customerPhone, $customerAddress);
                            if (!$transactionStmt->execute()) {
                                throw new Exception('Gagal menyimpan transaksi.');
                            }

                            $transactionId = (int) $conn->insert_id;
                            $transactionStmt->close();

                            $itemStmt = $conn->prepare('INSERT INTO transaction_items (quantity, total_price, product_id, transaction_id) VALUES (?, ?, ?, ?)');
                            if (!$itemStmt) {
                                throw new Exception('Gagal membuat detail transaksi.');
                            }

                            foreach ($itemsToInsert as $item) {
                                $itemQuantity = (int) $item['quantity'];
                                $itemTotalPrice = (float) $item['total_price'];
                                $itemProductId = (int) $item['product_id'];
                                $itemStmt->bind_param('idii', $itemQuantity, $itemTotalPrice, $itemProductId, $transactionId);
                                if (!$itemStmt->execute()) {
                                    throw new Exception('Gagal menyimpan item transaksi.');
                                }
                            }

                            $itemStmt->close();
                            $conn->commit();

                            $_SESSION['cart'] = [];
                            header('Location: transaction_status.php?id=' . $transactionId . '&checkout=success');
                            exit;
                        } catch (Exception $exception) {
                            $conn->rollback();
                            $errors[] = $exception->getMessage();
                        }
                    }
                }
            }
        }
    }
}

if (isset($_GET['cart'])) {
    if ($_GET['cart'] === 'added') {
        $successMessage = 'Produk ditambahkan ke keranjang.';
    } elseif ($_GET['cart'] === 'updated') {
        $successMessage = 'Keranjang berhasil diperbarui.';
    } elseif ($_GET['cart'] === 'removed') {
        $successMessage = 'Produk dihapus dari keranjang.';
    }
}

if (isset($_GET['checkout']) && $_GET['checkout'] === 'success') {
    $successMessage = 'Transaksi berhasil disimpan.';
}

$cartProductIds = array_keys($_SESSION['cart']);
$cartItems = [];
$cartGrandTotal = 0;
$cartTotalQty = 0;

if (!empty($cartProductIds)) {
    $cartPlaceholders = implode(',', array_fill(0, count($cartProductIds), '?'));
    $cartTypes = str_repeat('i', count($cartProductIds));
    $cartSql = "SELECT id, name, price, image, category FROM products WHERE id IN ($cartPlaceholders)";
    $cartStmt = $conn->prepare($cartSql);

    if ($cartStmt) {
        $cartStmt->bind_param($cartTypes, ...$cartProductIds);
        $cartStmt->execute();
        $cartResult = $cartStmt->get_result();

        if ($cartResult) {
            while ($cartProduct = $cartResult->fetch_assoc()) {
                $productId = (int) $cartProduct['id'];
                $qty = isset($_SESSION['cart'][$productId]) ? (int) $_SESSION['cart'][$productId] : 0;

                if ($qty < 1) {
                    continue;
                }

                $price = (float) $cartProduct['price'];
                $lineTotal = $price * $qty;
                $cartGrandTotal += $lineTotal;
                $cartTotalQty += $qty;

                $cartItems[] = [
                    'id' => $productId,
                    'name' => $cartProduct['name'],
                    'price' => $price,
                    'qty' => $qty,
                    'line_total' => $lineTotal,
                    'image' => $cartProduct['image'],
                    'category' => $cartProduct['category'],
                ];
            }
        }

        $cartStmt->close();
    }
}

renderTemplateStart('Keranjang Belanja', 'cart', '');
?>

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

    .empty-cart img {
        max-width: 200px;
        opacity: .5;
        margin-bottom: 1rem;
    }

    .customer-form {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        margin-top: 1rem;
    }
</style>

<div class="py-3">
    <!-- Header -->
    <div class="cart-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0 fw-bold"><i class="bi bi-cart3 me-2"></i>Keranjang Belanja</h4>
                <a href="home.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Lanjut Belanja
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($successMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <div class="card border-0 shadow-sm py-5">
                    <div class="card-body">
                        <i class="bi bi-cart-x" style="font-size: 5rem; color: #ccc;"></i>
                        <h5 class="mt-3 mb-2">Keranjang Belanja Kosong</h5>
                        <p class="text-muted mb-4">Yuk, isi keranjangmu dengan produk-produk menarik!</p>
                        <a href="home.php" class="btn btn-primary">
                            <i class="bi bi-shop me-2"></i>Belanja Sekarang
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <!-- Left: Cart Items -->
                <div class="col-lg-8">
                    <!-- Cart Items List -->
                    <?php foreach ($cartItems as $cartItem):
                        $imagePath = isset($cartItem['image']) && !empty($cartItem['image']) ? $cartItem['image'] : '';
                        $imageUrl = '';
                        $hasImage = false;

                        if ($imagePath) {
                            $absoluteImagePath = __DIR__ . '/' . ltrim($imagePath, '/');
                            if (file_exists($absoluteImagePath)) {
                                $imageUrl = $imagePath;
                                $hasImage = true;
                            }
                        }

                        if (!$hasImage) {
                            $imageUrl = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="80" height="80"%3E%3Crect width="80" height="80" fill="%23e9ecef"/%3E%3C/svg%3E';
                        }
                    ?>
                        <div class="cart-item">
                            <div class="row g-3 align-items-center">
                                <!-- Image -->
                                <div class="col-auto">
                                    <img src="<?php echo $hasImage ? htmlspecialchars($imageUrl) : $imageUrl; ?>"
                                        alt="<?php echo htmlspecialchars($cartItem['name']); ?>"
                                        class="cart-img">
                                </div>

                                <!-- Product Info -->
                                <div class="col">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-1 fw-semibold"><?php echo htmlspecialchars($cartItem['name']); ?></h6>
                                            <p class="mb-2 small text-muted">
                                                <span class="badge bg-light text-dark"><?php echo htmlspecialchars($cartItem['category']); ?></span>
                                            </p>
                                            <p class="mb-0 fw-bold" style="color: #E53935; font-size: 1.1rem;">
                                                Rp <?php echo number_format($cartItem['price'], 0, ',', '.'); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quantity & Actions -->
                                <div class="col-12 col-md-auto">
                                    <div class="d-flex align-items-center gap-3 justify-content-between justify-content-md-end">
                                        <!-- Quantity Control -->
                                        <form method="POST" class="qty-control" id="form-<?php echo $cartItem['id']; ?>">
                                            <input type="hidden" name="cart_action" value="update">
                                            <input type="hidden" name="product_id" value="<?php echo (int) $cartItem['id']; ?>">
                                            <button type="button" onclick="decreaseQty(<?php echo $cartItem['id']; ?>, <?php echo $cartItem['qty']; ?>)">
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <input type="number" name="quantity" id="qty-<?php echo $cartItem['id']; ?>"
                                                value="<?php echo (int) $cartItem['qty']; ?>" min="1" readonly>
                                            <button type="button" onclick="increaseQty(<?php echo $cartItem['id']; ?>)">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </form>

                                        <!-- Total -->
                                        <div class="text-end" style="min-width: 120px;">
                                            <small class="text-muted d-block">Total</small>
                                            <strong class="text-dark">Rp <?php echo number_format($cartItem['line_total'], 0, ',', '.'); ?></strong>
                                        </div>

                                        <!-- Delete -->
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="cart_action" value="remove">
                                            <input type="hidden" name="product_id" value="<?php echo (int) $cartItem['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-light text-danger"
                                                onclick="return confirm('Hapus produk dari keranjang?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Right: Summary & Checkout -->
                <div class="col-lg-4">
                    <div class="summary-card">
                        <h5 class="mb-3 fw-bold">Ringkasan Belanja</h5>

                        <div class="summary-row border-bottom">
                            <span class="text-muted">Total Item</span>
                            <span class="fw-semibold"><?php echo $cartTotalQty; ?> produk</span>
                        </div>

                        <div class="summary-row border-bottom pb-3">
                            <span class="text-muted">Total Harga</span>
                            <span class="fw-bold">Rp <?php echo number_format($cartGrandTotal, 0, ',', '.'); ?></span>
                        </div>

                        <div class="summary-row pt-2">
                            <span class="fw-bold fs-6">Total Pembayaran</span>
                            <span class="summary-total">Rp <?php echo number_format($cartGrandTotal, 0, ',', '.'); ?></span>
                        </div>

                        <!-- Customer Form -->
                        <form method="POST" id="checkoutForm" class="mt-4">
                            <input type="hidden" name="cart_action" value="checkout">

                            <div class="customer-form">
                                <h6 class="mb-3 fw-bold"><i class="bi bi-person-circle me-2"></i>Data Pembeli</h6>

                                <div class="mb-2">
                                    <label class="form-label small mb-1">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="customer_name" required
                                        value="<?php echo isset($_POST['customer_name']) ? htmlspecialchars($_POST['customer_name']) : ''; ?>"
                                        placeholder="Nama lengkap">
                                </div>

                                <div class="mb-2">
                                    <label class="form-label small mb-1">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control form-control-sm" name="customer_email" required
                                        value="<?php echo isset($_POST['customer_email']) ? htmlspecialchars($_POST['customer_email']) : ''; ?>"
                                        placeholder="email@example.com">
                                </div>

                                <div class="mb-2">
                                    <label class="form-label small mb-1">Nomor Telepon <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control form-control-sm" name="customer_phone" required
                                        value="<?php echo isset($_POST['customer_phone']) ? htmlspecialchars($_POST['customer_phone']) : ''; ?>"
                                        placeholder="08xxxxxxxxxx">
                                </div>

                                <div class="mb-0">
                                    <label class="form-label small mb-1">Alamat <span class="text-danger">*</span></label>
                                    <textarea class="form-control form-control-sm" name="customer_address" rows="2" required
                                        placeholder="Alamat lengkap"><?php echo isset($_POST['customer_address']) ? htmlspecialchars($_POST['customer_address']) : ''; ?></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-checkout w-100 mt-3">
                                <i class="bi bi-bag-check me-2"></i>Checkout (<?php echo $cartTotalQty; ?> Item)
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function increaseQty(productId) {
        const input = document.getElementById('qty-' + productId);
        input.value = parseInt(input.value) + 1;
        document.getElementById('form-' + productId).submit();
    }

    function decreaseQty(productId, currentQty) {
        if (currentQty > 1) {
            const input = document.getElementById('qty-' + productId);
            input.value = parseInt(input.value) - 1;
            document.getElementById('form-' + productId).submit();
        }
    }
</script>

<?php renderTemplateEnd(); ?>