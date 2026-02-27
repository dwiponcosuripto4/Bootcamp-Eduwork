<?php
session_start();

require_once 'koneksi.php';
require_once __DIR__ . '/components/template.php';
require_once __DIR__ . '/config_wa.php';

$transactionId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($transactionId <= 0) {
    header('Location: home.php');
    exit;
}

// Get transaction details
$transactionSql = "SELECT id, status, total, user_id, customer_name, customer_email, customer_phone, customer_address, created_at FROM transactions WHERE id = ?";
$transactionStmt = $conn->prepare($transactionSql);

if (!$transactionStmt) {
    die("Query gagal: " . $conn->error);
}

$transactionStmt->bind_param('i', $transactionId);
$transactionStmt->execute();
$transactionResult = $transactionStmt->get_result();

if ($transactionResult->num_rows === 0) {
    header('Location: home.php');
    exit;
}

$transaction = $transactionResult->fetch_assoc();
$transactionStmt->close();

// Get transaction items with product details
$itemsSql = "
    SELECT 
        ti.quantity,
        ti.total_price,
        p.name as product_name,
        p.price as product_price,
        p.image as product_image,
        p.category as product_category
    FROM transaction_items ti
    JOIN products p ON ti.product_id = p.id
    WHERE ti.transaction_id = ?
    ORDER BY ti.id ASC
";

$itemsStmt = $conn->prepare($itemsSql);
if (!$itemsStmt) {
    die("Query gagal: " . $conn->error);
}

$itemsStmt->bind_param('i', $transactionId);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();

$items = [];
if ($itemsResult) {
    while ($row = $itemsResult->fetch_assoc()) {
        $items[] = $row;
    }
}
$itemsStmt->close();

$statusBadge = [
    'paid' => 'success',
    'pending' => 'warning',
    'cancelled' => 'danger',
    'completed' => 'primary'
];

$statusLabel = [
    'paid' => 'Dibayar',
    'pending' => 'Menunggu Pembayaran',
    'cancelled' => 'Dibatalkan',
    'completed' => 'Selesai'
];

$badgeClass = isset($statusBadge[$transaction['status']]) ? $statusBadge[$transaction['status']] : 'secondary';
$statusText = isset($statusLabel[$transaction['status']]) ? $statusLabel[$transaction['status']] : ucfirst($transaction['status']);

// Prepare WhatsApp message
$waAdminNumber = WA_ADMIN_NUMBER; // Nomor WhatsApp admin dari config_wa.php
$waMessage = "*KONFIRMASI PESANAN*\n\n";
$waMessage .= "ID Transaksi: #" . $transaction['id'] . "\n";
$waMessage .= "Tanggal: " . date('d F Y H:i', strtotime($transaction['created_at'])) . "\n";
$waMessage .= "Status: " . $statusText . "\n\n";

if (!empty($transaction['customer_name'])) {
    $waMessage .= "*Data Pembeli:*\n";
    $waMessage .= "Nama: " . $transaction['customer_name'] . "\n";
    $waMessage .= "Email: " . $transaction['customer_email'] . "\n";
    $waMessage .= "Telepon: " . $transaction['customer_phone'] . "\n";
    $waMessage .= "Alamat: " . str_replace("\n", " ", $transaction['customer_address']) . "\n\n";
}

$waMessage .= "*Detail Pesanan:*\n";
foreach ($items as $idx => $item) {
    $waMessage .= ($idx + 1) . ". " . $item['product_name'] . " (" . $item['quantity'] . "x) - Rp " . number_format($item['total_price'], 0, ',', '.') . "\n";
}

$waMessage .= "\n*Total: Rp " . number_format($transaction['total'], 0, ',', '.') . "*\n\n";
$waMessage .= "Mohon konfirmasi pesanan ini. Terima kasih!";

$waLink = 'https://wa.me/' . $waAdminNumber . '?text=' . urlencode($waMessage);

renderTemplateStart('Status Transaksi #' . $transactionId, '', '');
?>

<style>
    /* Transaction Status Styles */
    .success-header {
        background: linear-gradient(135deg, #E53935 0%, #7A0C0C 100%);
        color: #fff;
        padding: 2.5rem 0;
        margin-bottom: 2rem;
    }

    .success-icon {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, .2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        animation: checkmark .6s ease-in-out;
        border: 3px solid #FFDD5E;
        box-shadow: 0 0 20px rgba(255, 221, 94, 0.3);
    }

    @keyframes checkmark {
        0% {
            transform: scale(0);
        }

        50% {
            transform: scale(1.1);
        }

        100% {
            transform: scale(1);
        }
    }

    .success-icon i {
        font-size: 3rem;
    }

    .status-card {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        transition: box-shadow .3s;
    }

    .status-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, .1);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
        margin-top: 1rem;
    }

    .info-item {
        background: #FFF1F1;
        padding: 1rem;
        border-radius: 8px;
        border-left: 3px solid #E53935;
    }

    .info-item .label {
        font-size: .785rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: .25rem;
    }

    .info-item .value {
        font-size: 1rem;
        font-weight: 600;
        color: #212529;
    }

    .product-item {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: .75rem;
        transition: all .2s;
    }

    .product-item:hover {
        border-color: #E53935;
        box-shadow: 0 2px 12px rgba(229, 57, 53, .15);
    }

    .product-img {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #f0f0f0;
    }

    .product-name {
        font-weight: 600;
        color: #212529;
        margin-bottom: .25rem;
    }

    .product-category {
        display: inline-block;
        background: #FFDD5E;
        color: #333;
        padding: .2rem .6rem;
        border-radius: 4px;
        font-size: .75rem;
        font-weight: 600;
        border: 1px solid #f5c842;
    }

    .qty-badge {
        background: #E53935;
        color: #fff;
        padding: .35rem .75rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: .9rem;
        border: 2px solid #FFDD5E;
        box-shadow: 0 2px 8px rgba(255, 221, 94, 0.2);
    }

    .total-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1.5rem;
        border-radius: 10px;
        margin-top: 1rem;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: .75rem 0;
        border-bottom: 1px dashed #dee2e6;
    }

    .total-row:last-child {
        border: none;
        padding-top: 1rem;
    }

    .total-label {
        font-size: 1.1rem;
        font-weight: 600;
        color: #495057;
    }

    .total-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #28a745;
    }

    .action-btn {
        padding: .75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all .3s;
        border: none;
    }

    .btn-wa {
        background: #25D366;
        color: #fff;
    }

    .btn-wa:hover {
        background: #128C7E;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 211, 102, .3);
    }

    .btn-print {
        background: #fff;
        border: 2px solid #E53935;
        color: #E53935;
    }

    .btn-print:hover {
        background: #E53935;
        color: #fff;
    }

    .btn-shop {
        background: #fff;
        border: 2px solid #e5e5e5;
        color: #495057;
    }

    .btn-shop:hover {
        background: #f8f9fa;
        border-color: #E53935;
        color: #E53935;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #212529;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .section-title i {
        color: #E53935;
    }

    @media print {
        @page {
            margin: 0.5cm;
            size: A4 portrait;
        }

        body {
            margin: 0;
            padding: 0;
            font-size: 10pt;
        }

        .btn,
        .action-btn,
        nav,
        footer,
        .btn-wa,
        .btn-print,
        .btn-shop,
        .action-buttons {
            display: none !important;
        }

        .success-header {
            background: #fff !important;
            color: #000 !important;
            padding: 0.5rem 0 !important;
            margin-bottom: 0.5rem !important;
            page-break-after: avoid;
        }

        .success-icon {
            width: 40px !important;
            height: 40px !important;
            margin-bottom: 0.25rem !important;
        }

        .success-icon i {
            font-size: 1.5rem !important;
        }

        .success-header h2 {
            font-size: 1.2rem !important;
            margin-bottom: 0.25rem !important;
        }

        .success-header p {
            font-size: 0.8rem !important;
        }

        .container {
            max-width: 100% !important;
            padding: 0 !important;
        }

        .status-card {
            margin-bottom: 0.5rem !important;
            padding: 0.75rem !important;
            box-shadow: none !important;
            border: 1px solid #dee2e6 !important;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 1rem !important;
            margin-bottom: 0.5rem !important;
        }

        .info-grid {
            gap: 0.5rem !important;
            margin-top: 0.5rem !important;
        }

        .info-item {
            padding: 0.5rem !important;
            page-break-inside: avoid;
        }

        .info-item .label {
            font-size: 0.65rem !important;
        }

        .info-item .value {
            font-size: 0.8rem !important;
        }

        .product-item {
            padding: 0.5rem !important;
            margin-bottom: 0.4rem !important;
            page-break-inside: avoid;
        }

        .product-img {
            width: 40px !important;
            height: 40px !important;
        }

        .product-name {
            font-size: 0.85rem !important;
        }

        .product-category {
            font-size: 0.65rem !important;
            padding: 0.1rem 0.3rem !important;
        }

        .qty-badge {
            padding: 0.2rem 0.4rem !important;
            font-size: 0.75rem !important;
        }

        .product-item .row {
            font-size: 0.8rem !important;
        }

        .product-item .col-auto {
            padding: 0 0.25rem !important;
        }

        .total-section {
            padding: 0.75rem !important;
            margin-top: 0.5rem !important;
            page-break-inside: avoid;
        }

        .total-row {
            padding: 0.4rem 0 !important;
            font-size: 0.85rem !important;
        }

        .total-label {
            font-size: 0.9rem !important;
        }

        .total-value {
            font-size: 1.2rem !important;
        }

        h4 {
            font-size: 1.1rem !important;
        }

        .badge {
            font-size: 0.7rem !important;
            padding: 0.25rem 0.5rem !important;
        }

        .pb-4 {
            padding-bottom: 0 !important;
        }

        /* Force single page */
        .col-lg-9 {
            transform: scale(0.85);
            transform-origin: top center;
        }
    }

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            flex-direction: column;
        }

        .action-btn {
            width: 100%;
        }
    }
</style>

<div class="pb-4">
    <!-- Success Header -->
    <?php if (isset($_GET['checkout']) && $_GET['checkout'] === 'success'): ?>
        <div class="success-header">
            <div class="container">
                <div class="text-center">
                    <div class="success-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h2 class="fw-bold mb-2">Pesanan Berhasil Dibuat!</h2>
                    <p class="mb-0 opacity-90">Terima kasih atas pembelian Anda. Pesanan Anda sedang diproses.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="row">
            <div class="col-12 col-lg-9 mx-auto">
                <!-- Transaction Info Card -->
                <div class="status-card">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-receipt-cutoff fs-4 text-primary"></i>
                                <h4 class="mb-0 fw-bold">Transaksi #<?php echo str_pad($transaction['id'], 6, '0', STR_PAD_LEFT); ?></h4>
                            </div>
                            <p class="text-muted mb-0">
                                <i class="bi bi-calendar3 me-1"></i>
                                <?php echo date('d F Y, H:i', strtotime($transaction['created_at'])); ?> WIB
                            </p>
                        </div>
                        <span class="badge bg-<?php echo $badgeClass; ?> px-3 py-2 fs-6">
                            <i class="bi bi-clock-history me-1"></i><?php echo $statusText; ?>
                        </span>
                    </div>
                </div>

                <!-- Customer Info Card -->
                <?php if (!empty($transaction['customer_name'])): ?>
                    <div class="status-card">
                        <h5 class="section-title">
                            <i class="bi bi-person-circle"></i>
                            Informasi Pembeli
                        </h5>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="label"><i class="bi bi-person me-1"></i>Nama Lengkap</div>
                                <div class="value"><?php echo htmlspecialchars($transaction['customer_name']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="label"><i class="bi bi-envelope me-1"></i>Email</div>
                                <div class="value"><?php echo htmlspecialchars($transaction['customer_email']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="label"><i class="bi bi-telephone me-1"></i>Nomor Telepon</div>
                                <div class="value"><?php echo htmlspecialchars($transaction['customer_phone']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="label"><i class="bi bi-geo-alt me-1"></i>Alamat Pengiriman</div>
                                <div class="value"><?php echo nl2br(htmlspecialchars($transaction['customer_address'])); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Products Card -->
                <div class="status-card">
                    <h5 class="section-title">
                        <i class="bi bi-bag-check"></i>
                        Detail Produk (<?php echo count($items); ?> Item)
                    </h5>

                    <?php foreach ($items as $item):
                        $imagePath = isset($item['product_image']) && !empty($item['product_image']) ? $item['product_image'] : '';
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
                            $imageUrl = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="70" height="70"%3E%3Crect width="70" height="70" fill="%23e9ecef"/%3E%3C/svg%3E';
                        }
                    ?>
                        <div class="product-item">
                            <div class="row align-items-center g-3">
                                <div class="col-auto">
                                    <img src="<?php echo $hasImage ? htmlspecialchars($imageUrl) : $imageUrl; ?>"
                                        alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                        class="product-img">
                                </div>
                                <div class="col">
                                    <div class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                    <span class="product-category"><?php echo htmlspecialchars($item['product_category']); ?></span>
                                </div>
                                <div class="col-auto text-center">
                                    <div class="qty-badge"><?php echo $item['quantity']; ?>x</div>
                                </div>
                                <div class="col-auto text-end">
                                    <div class="text-muted small">Harga Satuan</div>
                                    <div class="fw-semibold">Rp <?php echo number_format($item['product_price'], 0, ',', '.'); ?></div>
                                </div>
                                <div class="col-auto text-end">
                                    <div class="text-muted small">Subtotal</div>
                                    <div class="fw-bold fs-5" style="color: #E53935;">
                                        Rp <?php echo number_format($item['total_price'], 0, ',', '.'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Total Section -->
                    <div class="total-section">
                        <div class="total-row">
                            <span class="text-muted">Subtotal Produk</span>
                            <span class="fw-semibold">Rp <?php echo number_format($transaction['total'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="total-row">
                            <span class="text-muted">Biaya Pengiriman</span>
                            <span class="fw-semibold text-success">Gratis</span>
                        </div>
                        <div class="total-row">
                            <span class="total-label">
                                <i class="bi bi-cash-coin me-2"></i>Total Pembayaran
                            </span>
                            <span class="total-value">Rp <?php echo number_format($transaction['total'], 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-3 justify-content-between flex-wrap action-buttons mb-4">
                    <a href="home.php" class="btn btn-shop action-btn">
                        <i class="bi bi-arrow-left me-2"></i>Kembali Belanja
                    </a>
                    <div class="d-flex gap-2 flex-wrap">
                        <button onclick="window.print()" class="btn btn-print action-btn">
                            <i class="bi bi-printer me-2"></i>Cetak Invoice
                        </button>
                        <a href="<?php echo htmlspecialchars($waLink); ?>" target="_blank" class="btn btn-wa action-btn">
                            <i class="bi bi-whatsapp me-2"></i>Konfirmasi via WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php renderTemplateEnd(); ?>