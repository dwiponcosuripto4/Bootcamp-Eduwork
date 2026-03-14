<?php
require_once '../../koneksi.php';
require_once __DIR__ . '/../../components/template.php';

$errors = [];
$successMessage = '';

if (isset($_POST['action']) && $_POST['action'] === 'bulk_delete') {
    $deleteIds = isset($_POST['product_ids']) && is_array($_POST['product_ids']) ? $_POST['product_ids'] : [];
    $deletedCount = 0;

    foreach ($deleteIds as $rawId) {
        $deleteId = (int) $rawId;
        if ($deleteId <= 0) {
            continue;
        }

        $checkStmt = $conn->prepare('SELECT COUNT(*) as total FROM transaction_items WHERE product_id = ?');
        if (!$checkStmt) {
            $errors[] = 'Gagal memeriksa data transaksi produk.';
            continue;
        }

        $checkStmt->bind_param('i', $deleteId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $checkData = $checkResult ? $checkResult->fetch_assoc() : null;
        $checkStmt->close();

        if ($checkData && (int) $checkData['total'] > 0) {
            $errors[] = 'Produk ID ' . $deleteId . ' tidak dapat dihapus karena sudah digunakan dalam transaksi.';
            continue;
        }

        $selectStmt = $conn->prepare('SELECT image FROM products WHERE id = ?');
        if (!$selectStmt) {
            $errors[] = 'Data produk tidak bisa diambil untuk proses hapus.';
            continue;
        }

        $selectStmt->bind_param('i', $deleteId);
        $selectStmt->execute();
        $deleteResult = $selectStmt->get_result();
        $productToDelete = $deleteResult ? $deleteResult->fetch_assoc() : null;
        $selectStmt->close();

        $deleteStmt = $conn->prepare('DELETE FROM products WHERE id = ?');
        if (!$deleteStmt) {
            $errors[] = 'Query hapus gagal diproses.';
            continue;
        }

        $deleteStmt->bind_param('i', $deleteId);
        if ($deleteStmt->execute()) {
            $deletedCount++;
            if (!empty($productToDelete['image'])) {
                $imageFullPath = __DIR__ . '/../../' . ltrim($productToDelete['image'], '/');
                if (file_exists($imageFullPath)) {
                    unlink($imageFullPath);
                }
            }
        } else {
            $errors[] = 'Gagal menghapus produk ID ' . $deleteId . ': ' . $deleteStmt->error;
        }
        $deleteStmt->close();
    }

    if ($deletedCount > 0) {
        header('Location: index.php?success=bulk_deleted&count=' . $deletedCount);
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $deleteId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($deleteId > 0) {
        // Cek apakah produk sudah digunakan dalam transaksi
        $checkStmt = $conn->prepare('SELECT COUNT(*) as total FROM transaction_items WHERE product_id = ?');
        if ($checkStmt) {
            $checkStmt->bind_param('i', $deleteId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $checkData = $checkResult ? $checkResult->fetch_assoc() : null;
            $checkStmt->close();

            if ($checkData && $checkData['total'] > 0) {
                // Produk sudah digunakan dalam transaksi, tidak bisa dihapus
                $errors[] = 'Produk tidak dapat dihapus karena sudah digunakan dalam ' . $checkData['total'] . ' transaksi. Hapus transaksi terkait terlebih dahulu atau nonaktifkan produk ini.';
            } else {
                // Produk belum digunakan, boleh dihapus
                $selectStmt = $conn->prepare('SELECT image FROM products WHERE id = ?');
                if ($selectStmt) {
                    $selectStmt->bind_param('i', $deleteId);
                    $selectStmt->execute();
                    $deleteResult = $selectStmt->get_result();
                    $productToDelete = $deleteResult ? $deleteResult->fetch_assoc() : null;
                    $selectStmt->close();

                    $deleteStmt = $conn->prepare('DELETE FROM products WHERE id = ?');
                    if ($deleteStmt) {
                        $deleteStmt->bind_param('i', $deleteId);
                        if ($deleteStmt->execute()) {
                            if (!empty($productToDelete['image'])) {
                                $imageFullPath = __DIR__ . '/../../' . ltrim($productToDelete['image'], '/');
                                if (file_exists($imageFullPath)) {
                                    unlink($imageFullPath);
                                }
                            }
                            header('Location: index.php?success=deleted');
                            exit;
                        }
                        $errors[] = 'Gagal menghapus produk: ' . $deleteStmt->error;
                        $deleteStmt->close();
                    } else {
                        $errors[] = 'Query hapus gagal diproses.';
                    }
                } else {
                    $errors[] = 'Data produk tidak bisa diambil untuk proses hapus.';
                }
            }
        } else {
            $errors[] = 'Gagal memeriksa data transaksi produk.';
        }
    }
}

$result = $conn->query('SELECT * FROM products ORDER BY id DESC');
$products = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'deleted') {
        $successMessage = 'Produk berhasil dihapus.';
    } elseif ($_GET['success'] === 'bulk_deleted') {
        $count = isset($_GET['count']) ? (int) $_GET['count'] : 0;
        $successMessage = $count . ' produk berhasil dihapus.';
    } elseif ($_GET['success'] === 'updated') {
        $successMessage = 'Produk berhasil diperbarui.';
    }
}

renderTemplateStart('CRUD Produk', 'products-index', '../../');
?>

<style>
    /* Admin Products Page Styles */
    .page-header {
        background: linear-gradient(135deg, #E53935 0%, #7A0C0C 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(229, 57, 53, 0.2);
    }

    .page-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: .25rem;
    }

    .page-header p {
        margin: 0;
        opacity: .9;
    }

    .btn-add-product {
        background: #fff;
        color: #E53935;
        border: 2px solid #E53935;
        padding: .75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .15);
        transition: all .3s;
    }

    .btn-add-product:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 221, 94, 0.4);
        background: #FFDD5E;
        color: #333;
        border-color: #FFDD5E;
    }

    .stats-card {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        transition: all .3s;
    }

    .stats-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, .1);
        transform: translateY(-2px);
    }

    .stats-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        border: 2px solid transparent;
        transition: all .3s;
    }

    .stats-card:hover .stats-icon {
        border-color: #FFDD5E;
        transform: scale(1.1);
    }

    .stats-icon.primary {
        background: linear-gradient(135deg, #E53935 0%, #7A0C0C 100%);
        color: #fff;
    }

    .stats-icon.success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: #fff;
    }

    .stats-icon.warning {
        background: linear-gradient(135deg, #E53935 0%, #7A0C0C 100%);
        color: #fff;
    }

    .stats-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #212529;
        margin: .5rem 0 .25rem;
    }

    .stats-label {
        color: #6c757d;
        font-size: .875rem;
        font-weight: 500;
    }

    .content-card {
        background: #fff;
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
        overflow: hidden;
    }

    .content-card .card-header {
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        padding: 1.25rem 1.5rem;
    }

    .content-card .card-header h5 {
        font-weight: 700;
        color: #212529;
        margin: 0;
    }

    .product-img-table {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #f0f0f0;
        transition: all .3s;
    }

    .product-img-table:hover {
        transform: scale(1.5);
        box-shadow: 0 4px 15px rgba(0, 0, 0, .2);
        z-index: 999;
        position: relative;
    }

    .product-name-cell {
        font-weight: 600;
        color: #212529;
    }

    .product-desc-cell {
        color: #6c757d;
        font-size: .875rem;
    }

    .category-badge {
        background: #e7f3ff;
        color: #0066cc;
        padding: .35rem .75rem;
        border-radius: 6px;
        font-size: .8rem;
        font-weight: 600;
        display: inline-block;
    }

    .price-cell {
        font-weight: 700;
        color: #28a745;
        font-size: 1rem;
    }

    .btn-action {
        padding: .4rem .85rem;
        border-radius: 6px;
        font-size: .875rem;
        font-weight: 600;
        border: none;
        transition: all .2s;
    }

    .btn-action.btn-edit {
        background: #ffc107;
        color: #000;
    }

    .btn-action.btn-edit:hover {
        background: #ffb300;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(255, 193, 7, .4);
    }

    .btn-action.btn-delete {
        background: #dc3545;
        color: #fff;
    }

    .btn-action.btn-delete:hover {
        background: #c82333;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(220, 53, 69, .4);
    }

    /* DataTables Custom Styling */
    #productsTable_wrapper .dataTables_length,
    #productsTable_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }

    #productsTable_wrapper .dataTables_length label,
    #productsTable_wrapper .dataTables_filter label {
        font-weight: 600;
        color: #495057;
    }

    #productsTable_wrapper .dataTables_length select,
    #productsTable_wrapper .dataTables_filter input {
        border: 2px solid #e9ecef;
        border-radius: 6px;
        padding: .4rem .75rem;
        transition: all .2s;
    }

    #productsTable_wrapper .dataTables_length select:focus,
    #productsTable_wrapper .dataTables_filter input:focus {
        border-color: #E53935;
        outline: none;
        box-shadow: 0 0 0 3px rgba(229, 57, 53, .1);
    }

    #productsTable thead th {
        background: #f8f9fa;
        color: #2d3748;
        font-weight: 700;
        font-size: .875rem;
        letter-spacing: .3px;
        border: none;
        border-bottom: 3px solid #E53935;
        padding: 1.25rem 1rem;
        position: relative;
        white-space: nowrap;
    }

    #productsTable thead th::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        width: 0;
        height: 3px;
        background: #FFDD5E;
        transition: width .3s ease;
    }


    #productsTable thead th i {
        color: #E53935;
        margin-right: .5rem;
        font-size: 1rem;
    }

    #productsTable thead th.sortable {
        cursor: pointer;
    }


    #productsTable tbody tr {
        transition: all .2s;
    }

    #productsTable tbody tr:hover {
        background-color: #FFF1F1 !important;
        box-shadow: 0 2px 8px rgba(229, 57, 53, .1);
    }

    #productsTable tbody td {
        vertical-align: middle;
        padding: 1rem .75rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .dataTables_info {
        font-weight: 600;
        color: #495057;
    }

    .dataTables_paginate .paginate_button {
        border: 2px solid #e9ecef !important;
        background: #fff !important;
        color: #495057 !important;
        border-radius: 6px !important;
        margin: 0 .15rem;
        font-weight: 600;
        transition: all .2s;
    }

    .dataTables_paginate .paginate_button:hover {
        background: #E53935 !important;
        color: #fff !important;
        border-color: #E53935 !important;
    }

    .dataTables_paginate .paginate_button.current {
        background: #E53935 !important;
        color: #fff !important;
        border-color: #E53935 !important;
    }

    .alert-modern {
        border: none;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .alert-modern i {
        font-size: 1.5rem;
    }

    .product-checkbox,
    .select-all-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #E53935;
    }

    #bulkDeleteBtn {
        display: none;
    }

    #bulkDeleteBtn.show {
        display: inline-block;
    }

    .btn-bulk-delete {
        background: #dc3545;
        color: #fff;
        border: none;
        padding: .5rem 1rem;
        border-radius: 6px;
        font-weight: 600;
        transition: all .3s;
    }

    .btn-bulk-delete:hover {
        background: #c82333;
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(220, 53, 69, .4);
    }
</style>

<div class="row">
    <div class="col-12">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1><i class="bi bi-box-seam me-2"></i>Manajemen Produk</h1>
                    <p>Kelola dan monitor semua produk Anda</p>
                </div>
                <a href="create.php" class="btn btn-add-product">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Produk Baru
                </a>
            </div>
        </div>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success alert-modern alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <div>
                    <strong>Berhasil!</strong> <?php echo htmlspecialchars($successMessage); ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-modern alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    <strong>Error!</strong>
                    <ul class="mb-0 mt-1">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon primary">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stats-value"><?php echo count($products); ?></div>
                            <div class="stats-label">Total Produk</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon success">
                            <i class="bi bi-tags"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <?php
                            $categories = array_unique(array_column($products, 'category'));
                            ?>
                            <div class="stats-value"><?php echo count($categories); ?></div>
                            <div class="stats-label">Kategori</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon warning">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <?php
                            $totalValue = array_sum(array_column($products, 'price'));
                            ?>
                            <div class="stats-value">Rp <?php echo number_format($totalValue, 0, ',', '.'); ?></div>
                            <div class="stats-label">Total Nilai</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="content-card card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-table me-2"></i>Daftar Produk</h5>
                <button type="button" id="bulkDeleteBtn" class="btn btn-bulk-delete" onclick="bulkDeleteProducts()">
                    <i class="bi bi-trash me-2"></i>Hapus yang Dipilih (<span id="selectedCount">0</span>)
                </button>
            </div>
            <div class="card-body">
                <form id="bulkDeleteForm" method="POST" action="">
                    <input type="hidden" name="action" value="bulk_delete">
                    <table id="productsTable" class="table align-middle mb-0" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 30px;">
                                    <input type="checkbox" class="select-all-checkbox" id="selectAll" title="Pilih Semua">
                                </th>
                                <th class="sortable"><i class="bi bi-hash"></i>ID</th>
                                <th><i class="bi bi-image"></i>Gambar</th>
                                <th class="sortable"><i class="bi bi-box-seam"></i>Nama Produk</th>
                                <th class="sortable"><i class="bi bi-tag"></i>Kategori</th>
                                <th class="sortable"><i class="bi bi-currency-dollar"></i>Harga</th>
                                <th class="text-end"><i class="bi bi-gear"></i>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Belum ada data produk.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <?php
                                    $imageUrl = '';
                                    $imageRelativePath = isset($product['image']) ? (string) $product['image'] : '';
                                    $imageAbsolutePath = $imageRelativePath !== '' ? (__DIR__ . '/../../' . ltrim($imageRelativePath, '/')) : '';
                                    $descriptionText = isset($product['description']) ? (string) $product['description'] : '';
                                    $descriptionPreview = strlen($descriptionText) > 80 ? (substr($descriptionText, 0, 80) . '...') : $descriptionText;
                                    if ($imageRelativePath !== '' && file_exists($imageAbsolutePath)) {
                                        $imageUrl = '../../' . ltrim($imageRelativePath, '/');
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="product_ids[]" value="<?php echo (int) $product['id']; ?>" class="product-checkbox">
                                        </td>
                                        <td><strong>#<?php echo str_pad($product['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                                        <td>
                                            <?php if ($imageUrl !== ''): ?>
                                                <img src="<?php echo htmlspecialchars($imageUrl); ?>"
                                                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                    class="product-img-table"
                                                    title="Klik untuk perbesar">
                                            <?php else: ?>
                                                <div style="width: 60px; height: 60px; background: #e9ecef; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="product-name-cell"><?php echo htmlspecialchars($product['name']); ?></div>
                                            <div class="product-desc-cell"><?php echo htmlspecialchars($descriptionPreview); ?></div>
                                        </td>
                                        <td>
                                            <span class="category-badge">
                                                <i class="bi bi-tag me-1"></i><?php echo htmlspecialchars($product['category']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="price-cell">Rp <?php echo number_format((float) $product['price'], 0, ',', '.'); ?></span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <a href="edit.php?id=<?php echo (int) $product['id']; ?>"
                                                    class="btn btn-action btn-edit"
                                                    title="Edit Produk">
                                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                                </a>
                                                <a href="index.php?action=delete&id=<?php echo (int) $product['id']; ?>"
                                                    class="btn btn-action btn-delete"
                                                    onclick="return confirm('⚠️ PERINGATAN!\n\nYakin ingin menghapus produk ini?\n\nCatatan: Produk yang sudah digunakan dalam transaksi tidak dapat dihapus untuk menjaga integritas data.');"
                                                    title="Hapus Produk">
                                                    <i class="bi bi-trash me-1"></i>Hapus
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let table = new DataTable('#productsTable', {
            order: [
                [1, 'desc']
            ],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            columnDefs: [{
                orderable: false,
                targets: [0, 2, 6]
            }],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                zeroRecords: 'Data tidak ditemukan',
                paginate: {
                    first: 'Awal',
                    last: 'Akhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya'
                }
            }
        });

        const selectAllCheckbox = document.getElementById('selectAll');
        const productCheckboxes = document.querySelectorAll('.product-checkbox');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const selectedCountSpan = document.getElementById('selectedCount');

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                productCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                updateBulkDeleteButton();
            });
        }

        productCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateSelectAllState();
                updateBulkDeleteButton();
            });
        });

        function updateSelectAllState() {
            const totalCheckboxes = productCheckboxes.length;
            const checkedCheckboxes = document.querySelectorAll('.product-checkbox:checked').length;

            if (selectAllCheckbox) {
                selectAllCheckbox.checked = totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes;
                selectAllCheckbox.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
            }
        }

        function updateBulkDeleteButton() {
            const checkedCount = document.querySelectorAll('.product-checkbox:checked').length;
            if (selectedCountSpan) {
                selectedCountSpan.textContent = checkedCount;
            }

            if (checkedCount > 0) {
                bulkDeleteBtn.classList.add('show');
            } else {
                bulkDeleteBtn.classList.remove('show');
            }
        }
    });

    function bulkDeleteProducts() {
        const checkedCount = document.querySelectorAll('.product-checkbox:checked').length;

        if (checkedCount === 0) {
            alert('Pilih minimal satu produk untuk dihapus.');
            return;
        }

        const confirmMessage = '⚠️ PERINGATAN!\n\nYakin ingin menghapus ' + checkedCount + ' produk yang dipilih?\n\nProduk yang sudah digunakan dalam transaksi tidak dapat dihapus.';

        if (confirm(confirmMessage)) {
            document.getElementById('bulkDeleteForm').submit();
        }
    }
</script>

<?php renderTemplateEnd(); ?>