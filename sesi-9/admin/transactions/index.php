<?php
require_once '../../koneksi.php';
require_once __DIR__ . '/../../components/template.php';

$errors = [];
$successMessage = '';

// Handle bulk delete action
if (isset($_POST['action']) && $_POST['action'] === 'bulk_delete') {
    $deleteIds = isset($_POST['transaction_ids']) ? $_POST['transaction_ids'] : [];

    if (!empty($deleteIds) && is_array($deleteIds)) {
        $conn->begin_transaction();
        $deletedCount = 0;

        try {
            foreach ($deleteIds as $deleteId) {
                $deleteId = (int) $deleteId;
                if ($deleteId > 0) {
                    // Delete transaction items first
                    $deleteItemsStmt = $conn->prepare('DELETE FROM transaction_items WHERE transaction_id = ?');
                    if ($deleteItemsStmt) {
                        $deleteItemsStmt->bind_param('i', $deleteId);
                        $deleteItemsStmt->execute();
                        $deleteItemsStmt->close();
                    }

                    // Then delete the transaction
                    $deleteTransStmt = $conn->prepare('DELETE FROM transactions WHERE id = ?');
                    if ($deleteTransStmt) {
                        $deleteTransStmt->bind_param('i', $deleteId);
                        if ($deleteTransStmt->execute()) {
                            $deletedCount++;
                        }
                        $deleteTransStmt->close();
                    }
                }
            }

            $conn->commit();
            header('Location: index.php?success=bulk_deleted&count=' . $deletedCount);
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = 'Gagal menghapus transaksi: ' . $e->getMessage();
        }
    }
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $deleteId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($deleteId > 0) {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Delete transaction items first (foreign key constraint)
            $deleteItemsStmt = $conn->prepare('DELETE FROM transaction_items WHERE transaction_id = ?');
            if ($deleteItemsStmt) {
                $deleteItemsStmt->bind_param('i', $deleteId);
                $deleteItemsStmt->execute();
                $deleteItemsStmt->close();
            }

            // Then delete the transaction
            $deleteTransStmt = $conn->prepare('DELETE FROM transactions WHERE id = ?');
            if ($deleteTransStmt) {
                $deleteTransStmt->bind_param('i', $deleteId);
                if ($deleteTransStmt->execute()) {
                    $conn->commit();
                    header('Location: index.php?success=deleted');
                    exit;
                } else {
                    throw new Exception('Gagal menghapus transaksi: ' . $deleteTransStmt->error);
                }
                $deleteTransStmt->close();
            } else {
                throw new Exception('Query hapus transaksi gagal diproses.');
            }
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = $e->getMessage();
        }
    }
}

// Fetch all transactions with total items count
$query = "SELECT t.*, 
          COUNT(ti.id) as total_items,
          GROUP_CONCAT(p.name SEPARATOR ', ') as product_names
          FROM transactions t
          LEFT JOIN transaction_items ti ON t.id = ti.transaction_id
          LEFT JOIN products p ON ti.product_id = p.id
          GROUP BY t.id
          ORDER BY t.id DESC";

$result = $conn->query($query);
$transactions = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
}

// Calculate statistics
$totalTransactions = count($transactions);
$totalRevenue = 0;
$paidCount = 0;
$pendingCount = 0;

foreach ($transactions as $trans) {
    $totalRevenue += (float) $trans['total'];
    if ($trans['status'] === 'paid') {
        $paidCount++;
    } elseif ($trans['status'] === 'pending') {
        $pendingCount++;
    }
}

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'deleted') {
        $successMessage = 'Transaksi berhasil dihapus.';
    } elseif ($_GET['success'] === 'bulk_deleted') {
        $count = isset($_GET['count']) ? (int)$_GET['count'] : 0;
        $successMessage = $count . ' transaksi berhasil dihapus.';
    } elseif ($_GET['success'] === 'updated') {
        $successMessage = 'Transaksi berhasil diperbarui.';
    }
}

renderTemplateStart('Manajemen Transaksi', 'transactions-index', '../../');
?>

<style>
    /* Admin Transactions Page Styles */
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
        background: linear-gradient(135deg, #f09819 0%, #edde5d 100%);
        color: #fff;
    }

    .stats-icon.info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

    .status-badge {
        padding: .35rem .85rem;
        border-radius: 6px;
        font-size: .8rem;
        font-weight: 600;
        display: inline-block;
    }

    .status-badge.paid {
        background: #d4edda;
        color: #155724;
    }

    .status-badge.pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-badge.cancelled {
        background: #f8d7da;
        color: #721c24;
    }

    .transaction-id-cell {
        font-weight: 700;
        color: #E53935;
        font-size: 1rem;
    }

    .customer-name-cell {
        font-weight: 600;
        color: #212529;
    }

    .customer-info-cell {
        color: #6c757d;
        font-size: .875rem;
    }

    .products-cell {
        color: #495057;
        font-size: .875rem;
        max-width: 300px;
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

    .btn-action.btn-view {
        background: #17a2b8;
        color: #fff;
    }

    .btn-action.btn-view:hover {
        background: #138496;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(23, 162, 184, .4);
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
    #transactionsTable_wrapper .dataTables_length,
    #transactionsTable_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }

    #transactionsTable_wrapper .dataTables_length label,
    #transactionsTable_wrapper .dataTables_filter label {
        font-weight: 600;
        color: #495057;
    }

    #transactionsTable_wrapper .dataTables_length select,
    #transactionsTable_wrapper .dataTables_filter input {
        border: 2px solid #e9ecef;
        border-radius: 6px;
        padding: .4rem .75rem;
        transition: all .2s;
    }

    #transactionsTable_wrapper .dataTables_length select:focus,
    #transactionsTable_wrapper .dataTables_filter input:focus {
        border-color: #E53935;
        outline: none;
        box-shadow: 0 0 0 3px rgba(229, 57, 53, .1);
    }

    #transactionsTable thead th {
        background: #f8f9fa;
        color: #2d3748;
        font-weight: 700;
        font-size: .875rem;
        letter-spacing: .3px;
        border: none;
        border-bottom: 3px solid #E53935;
        padding: 1.25rem 1rem;
        white-space: nowrap;
    }

    #transactionsTable thead th i {
        color: #E53935;
        margin-right: .5rem;
        font-size: 1rem;
    }

    #transactionsTable tbody tr {
        transition: all .2s;
    }

    #transactionsTable tbody tr:hover {
        background-color: #FFF1F1 !important;
        box-shadow: 0 2px 8px rgba(229, 57, 53, .1);
    }

    #transactionsTable tbody td {
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

    .items-badge {
        background: #e7f3ff;
        color: #0066cc;
        padding: .25rem .5rem;
        border-radius: 4px;
        font-size: .75rem;
        font-weight: 600;
    }

    /* Checkbox Styling */
    .transaction-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #E53935;
    }

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
                    <h1><i class="bi bi-receipt me-2"></i>Manajemen Transaksi</h1>
                    <p>Monitor dan kelola semua transaksi pelanggan</p>
                </div>
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
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon primary">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stats-value"><?php echo $totalTransactions; ?></div>
                            <div class="stats-label">Total Transaksi</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon success">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stats-value">Rp <?php echo number_format($totalRevenue, 0, ',', '.'); ?></div>
                            <div class="stats-label">Total Pendapatan</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon info">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stats-value"><?php echo $paidCount; ?></div>
                            <div class="stats-label">Transaksi Lunas</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon warning">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stats-value"><?php echo $pendingCount; ?></div>
                            <div class="stats-label">Transaksi Pending</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="content-card card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-table me-2"></i>Daftar Transaksi</h5>
                <button type="button" id="bulkDeleteBtn" class="btn btn-bulk-delete" onclick="bulkDeleteTransactions()">
                    <i class="bi bi-trash me-2"></i>Hapus yang Dipilih (<span id="selectedCount">0</span>)
                </button>
            </div>
            <div class="card-body">
                <form id="bulkDeleteForm" method="POST" action="">
                    <input type="hidden" name="action" value="bulk_delete">
                    <table id="transactionsTable" class="table align-middle mb-0" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 30px;">
                                    <input type="checkbox" class="select-all-checkbox" id="selectAll" title="Pilih Semua">
                                </th>
                                <th><i class="bi bi-hash"></i>ID</th>
                                <th><i class="bi bi-person"></i>Pelanggan</th>
                                <th><i class="bi bi-box-seam"></i>Produk</th>
                                <th><i class="bi bi-currency-dollar"></i>Total</th>
                                <th><i class="bi bi-flag"></i>Status</th>
                                <th><i class="bi bi-calendar"></i>Tanggal</th>
                                <th class="text-end"><i class="bi bi-gear"></i>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transactions)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Belum ada data transaksi.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transactions as $transaction): ?>
                                    <?php
                                    $statusClass = 'pending';
                                    $statusText = 'Pending';
                                    if ($transaction['status'] === 'paid') {
                                        $statusClass = 'paid';
                                        $statusText = 'Lunas';
                                    } elseif ($transaction['status'] === 'cancelled') {
                                        $statusClass = 'cancelled';
                                        $statusText = 'Dibatalkan';
                                    }

                                    $customerName = !empty($transaction['customer_name']) ? $transaction['customer_name'] : 'Guest';
                                    $customerEmail = !empty($transaction['customer_email']) ? $transaction['customer_email'] : '-';
                                    $customerPhone = !empty($transaction['customer_phone']) ? $transaction['customer_phone'] : '-';

                                    $productNames = !empty($transaction['product_names']) ? $transaction['product_names'] : '-';
                                    $productPreview = strlen($productNames) > 50 ? (substr($productNames, 0, 50) . '...') : $productNames;
                                    ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="transaction_ids[]" value="<?php echo $transaction['id']; ?>" class="transaction-checkbox">
                                        </td>
                                        <td>
                                            <span class="transaction-id-cell">#TRX-<?php echo str_pad($transaction['id'], 5, '0', STR_PAD_LEFT); ?></span>
                                        </td>
                                        <td>
                                            <div class="customer-name-cell"><?php echo htmlspecialchars($customerName); ?></div>
                                            <div class="customer-info-cell">
                                                <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($customerEmail); ?>
                                            </div>
                                            <div class="customer-info-cell">
                                                <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($customerPhone); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="products-cell" title="<?php echo htmlspecialchars($productNames); ?>">
                                                <?php echo htmlspecialchars($productPreview); ?>
                                            </div>
                                            <span class="items-badge mt-1">
                                                <i class="bi bi-box me-1"></i><?php echo $transaction['total_items']; ?> item
                                            </span>
                                        </td>
                                        <td>
                                            <span class="price-cell">Rp <?php echo number_format((float) $transaction['total'], 0, ',', '.'); ?></span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo $statusText; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="font-size: .875rem;">
                                                <i class="bi bi-calendar3 me-1"></i><?php echo date('d M Y', strtotime($transaction['created_at'])); ?>
                                            </div>
                                            <div style="font-size: .75rem; color: #6c757d;">
                                                <i class="bi bi-clock me-1"></i><?php echo date('H:i', strtotime($transaction['created_at'])); ?>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <a href="../../transaction_status.php?transaction_id=<?php echo (int) $transaction['id']; ?>"
                                                    class="btn btn-action btn-view"
                                                    title="Lihat Detail">
                                                    <i class="bi bi-eye me-1"></i>Detail
                                                </a>
                                                <a href="index.php?action=delete&id=<?php echo (int) $transaction['id']; ?>"
                                                    class="btn btn-action btn-delete"
                                                    onclick="return confirm('⚠️ PERINGATAN!\n\nYakin ingin menghapus transaksi #TRX-<?php echo str_pad($transaction['id'], 5, '0', STR_PAD_LEFT); ?>?\n\nSemua item transaksi juga akan dihapus.');"
                                                    title="Hapus Transaksi">
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
        let table = new DataTable('#transactionsTable', {
            order: [
                [1, 'desc'] // Sort by ID column (after checkbox column)
            ],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            columnDefs: [{
                orderable: false,
                targets: [0, 3, 7] // disable sorting for checkbox, products and actions columns
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

        // Handle checkbox selection
        const selectAllCheckbox = document.getElementById('selectAll');
        const transactionCheckboxes = document.querySelectorAll('.transaction-checkbox');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const selectedCountSpan = document.getElementById('selectedCount');

        // Select all functionality
        selectAllCheckbox.addEventListener('change', function() {
            transactionCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updateBulkDeleteButton();
        });

        // Individual checkbox change
        transactionCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectAllState();
                updateBulkDeleteButton();
            });
        });

        // Update select all checkbox state
        function updateSelectAllState() {
            const totalCheckboxes = transactionCheckboxes.length;
            const checkedCheckboxes = document.querySelectorAll('.transaction-checkbox:checked').length;

            selectAllCheckbox.checked = totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes;
            selectAllCheckbox.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
        }

        // Update bulk delete button visibility
        function updateBulkDeleteButton() {
            const checkedCount = document.querySelectorAll('.transaction-checkbox:checked').length;
            selectedCountSpan.textContent = checkedCount;

            if (checkedCount > 0) {
                bulkDeleteBtn.classList.add('show');
            } else {
                bulkDeleteBtn.classList.remove('show');
            }
        }
    });

    // Bulk delete function
    function bulkDeleteTransactions() {
        const checkedCount = document.querySelectorAll('.transaction-checkbox:checked').length;

        if (checkedCount === 0) {
            alert('Pilih minimal satu transaksi untuk dihapus.');
            return;
        }

        const confirmMessage = `⚠️ PERINGATAN!\n\nYakin ingin menghapus ${checkedCount} transaksi yang dipilih?\n\nSemua item transaksi juga akan dihapus.`;

        if (confirm(confirmMessage)) {
            document.getElementById('bulkDeleteForm').submit();
        }
    }
</script>

<?php renderTemplateEnd(); ?>