<?php
// Initialize error array
$errors = [];
$success = false;
$formattedPrice = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $price = isset($_POST['price']) ? trim($_POST['price']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $stock = isset($_POST['stock']) ? trim($_POST['stock']) : '';

    // Server-side validation
    if (empty($name)) {
        $errors['name'] = 'Nama produk wajib diisi';
    } elseif (strlen($name) < 3) {
        $errors['name'] = 'Nama produk minimal 3 karakter';
    }

    if (empty($price)) {
        $errors['price'] = 'Harga produk wajib diisi';
    } elseif (!is_numeric($price) || floatval($price) <= 0) {
        $errors['price'] = 'Harga harus lebih besar dari 0';
    }

    if (empty($description)) {
        $errors['description'] = 'Deskripsi produk wajib diisi';
    } elseif (strlen($description) < 10) {
        $errors['description'] = 'Deskripsi minimal 10 karakter';
    }

    if (empty($category)) {
        $errors['category'] = 'Kategori wajib dipilih';
    } elseif (!in_array($category, ['Elektronik', 'Furniture', 'Makanan'])) {
        $errors['category'] = 'Kategori tidak valid';
    }

    if (empty($stock)) {
        $errors['stock'] = 'Jumlah stok wajib diisi';
    } elseif (!is_numeric($stock) || intval($stock) < 0) {
        $errors['stock'] = 'Stok tidak boleh negatif';
    }

    // If no errors, set success flag
    if (empty($errors)) {
        $success = true;

        $priceValue = (float) $price;
        if ($priceValue < 1000) {
            $priceValue *= 1000;
        }
        $formattedPrice = 'Rp' . number_format($priceValue, 2, ',', '.');
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Form Input Product</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <h1 class="text-center mt-5">Hasil Form Input Produk</h1>

                <?php if ($success): ?>
                    <!-- Success Message -->
                    <div class="alert alert-success mt-4" role="alert">
                        <h4 class="alert-heading">Data Berhasil Disimpan!</h4>
                        <p>Produk telah berhasil ditambahkan ke sistem.</p>
                    </div>

                    <!-- Display Form Results -->
                    <div class="card mt-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Detail Produk</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <tbody>
                                    <tr>
                                        <th width="30%">Nama Produk</th>
                                        <td><?php echo htmlspecialchars($name); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Harga</th>
                                        <td><?php echo $formattedPrice; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Deskripsi</th>
                                        <td><?php echo nl2br(htmlspecialchars($description)); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Kategori</th>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($category); ?></span></td>
                                    </tr>
                                    <tr>
                                        <th>Stok</th>
                                        <td><?php echo htmlspecialchars($stock); ?> unit</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php elseif (!empty($errors)): ?>
                    <!-- Error Message -->
                    <div class="alert alert-danger mt-4" role="alert">
                        <h4 class="alert-heading">Validasi Gagal!</h4>
                        <p>Terdapat kesalahan pada data yang Anda masukkan:</p>
                        <ul class="mb-0">
                            <?php foreach ($errors as $field => $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                <?php else: ?>
                    <!-- No Data Message -->
                    <div class="alert alert-warning mt-4" role="alert">
                        <h4 class="alert-heading">Tidak Ada Data!</h4>
                        <p>Silakan isi form terlebih dahulu.</p>
                    </div>
                <?php endif; ?>

                <!-- Back Button -->
                <div class="text-center mt-4 mb-5">
                    <a href="index.php" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z" />
                        </svg>
                        Kembali ke Form
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>