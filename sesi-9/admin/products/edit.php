<?php
require_once '../../koneksi.php';
require_once __DIR__ . '/../../components/template.php';

$errors = [];
$successMessage = '';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare('SELECT * FROM products WHERE id = ?');
if (!$stmt) {
    die('Gagal menyiapkan query produk.');
}

$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$product) {
    header('Location: index.php');
    exit;
}

$name = isset($product['name']) ? (string) $product['name'] : '';
$description = isset($product['description']) ? (string) $product['description'] : '';
$price = isset($product['price']) ? (string) $product['price'] : '';
$category = isset($product['category']) ? (string) $product['category'] : '';
$currentImage = isset($product['image']) ? (string) $product['image'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? trim($_POST['price']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';

    if ($name === '') {
        $errors[] = 'Nama produk wajib diisi.';
    }

    if ($description === '') {
        $errors[] = 'Deskripsi wajib diisi.';
    }

    if ($category === '') {
        $errors[] = 'Kategori wajib diisi.';
    }

    if ($price === '' || !is_numeric($price) || (float) $price < 0) {
        $errors[] = 'Harga harus berupa angka dan tidak boleh negatif.';
    }

    $newImagePath = $currentImage;
    $newUploadedFullPath = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload gambar gagal.';
        } else {
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $fileTmpName = $_FILES['image']['tmp_name'];
            $fileSize = (int) $_FILES['image']['size'];

            if (!is_uploaded_file($fileTmpName)) {
                $errors[] = 'Upload gambar tidak valid.';
            } else {
                $detectedMime = mime_content_type($fileTmpName);
                if (!in_array($detectedMime, $allowedMimeTypes, true)) {
                    $errors[] = 'Format gambar harus JPG, PNG, atau WEBP.';
                }

                if ($fileSize > 2 * 1024 * 1024) {
                    $errors[] = 'Ukuran gambar maksimal 2MB.';
                }
            }

            if (empty($errors)) {
                $uploadDirectory = __DIR__ . '/../../image/';
                if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0777, true)) {
                    $errors[] = 'Folder upload gambar gagal dibuat.';
                } else {
                    $originalFileName = isset($_FILES['image']['name']) ? $_FILES['image']['name'] : '';
                    $originalBaseName = basename($originalFileName);
                    $fileInfo = pathinfo($originalBaseName);

                    $baseName = isset($fileInfo['filename']) ? $fileInfo['filename'] : 'image';
                    $extension = isset($fileInfo['extension']) ? strtolower($fileInfo['extension']) : '';

                    $safeBaseName = preg_replace('/[^A-Za-z0-9_-]/', '-', $baseName);
                    $safeBaseName = trim((string) $safeBaseName, '-');
                    if ($safeBaseName === '') {
                        $safeBaseName = 'image';
                    }

                    if ($extension === '') {
                        $mimeToExtension = [
                            'image/jpeg' => 'jpg',
                            'image/png' => 'png',
                            'image/webp' => 'webp',
                        ];
                        $extension = $mimeToExtension[$detectedMime];
                    }

                    $generatedFileName = $safeBaseName . '.' . $extension;
                    $targetFilePath = $uploadDirectory . $generatedFileName;

                    $counter = 1;
                    while (file_exists($targetFilePath)) {
                        $generatedFileName = $safeBaseName . '-' . $counter . '.' . $extension;
                        $targetFilePath = $uploadDirectory . $generatedFileName;
                        $counter++;
                    }

                    if (!move_uploaded_file($fileTmpName, $targetFilePath)) {
                        $errors[] = 'Gagal menyimpan gambar baru.';
                    } else {
                        $newImagePath = 'image/' . $generatedFileName;
                        $newUploadedFullPath = $targetFilePath;
                    }
                }
            }
        }
    }

    if (empty($errors)) {
        $priceValue = (float) $price;
        $updateStmt = $conn->prepare('UPDATE products SET name = ?, description = ?, price = ?, category = ?, image = ? WHERE id = ?');

        if (!$updateStmt) {
            if ($newUploadedFullPath !== '' && file_exists($newUploadedFullPath)) {
                unlink($newUploadedFullPath);
            }
            $errors[] = 'Gagal menyiapkan query update.';
        } else {
            $updateStmt->bind_param('ssdssi', $name, $description, $priceValue, $category, $newImagePath, $id);
            if ($updateStmt->execute()) {
                if ($newUploadedFullPath !== '' && $currentImage !== '' && $currentImage !== $newImagePath) {
                    $oldImageFullPath = __DIR__ . '/../../' . ltrim($currentImage, '/');
                    if (file_exists($oldImageFullPath)) {
                        unlink($oldImageFullPath);
                    }
                }

                header('Location: index.php?success=updated');
                exit;
            }

            if ($newUploadedFullPath !== '' && file_exists($newUploadedFullPath)) {
                unlink($newUploadedFullPath);
            }

            $errors[] = 'Gagal mengupdate produk: ' . $updateStmt->error;
            $updateStmt->close();
        }
    }
}

renderTemplateStart('Edit Produk', 'products-index', '../../');
?>

<style>
    /* Edit Product Page Styles */
    .page-header-edit {
        background: linear-gradient(135deg, #E53935 0%, #7A0C0C 100%);
        color: #fff;
        padding: 2.5rem 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(229, 57, 53, .3);
        text-align: center;
    }

    .page-header-edit h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: .5rem;
    }

    .page-header-edit p {
        margin: 0;
        opacity: .9;
        font-size: 1rem;
    }

    .form-card-edit {
        background: #fff;
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, .08);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .form-card-edit .card-header {
        background: linear-gradient(135deg, #FFF1F1 0%, #e9ecef 100%);
        border-bottom: 3px solid #E53935;
        padding: 1.5rem;
    }

    .form-card-edit .card-header h5 {
        font-weight: 700;
        color: #2d3748;
        margin: 0;
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .form-card-edit .card-header h5 i {
        color: #E53935;
        font-size: 1.5rem;
    }

    .form-card-edit .card-body {
        padding: 2rem;
    }

    .form-group-modern-edit {
        margin-bottom: 1.75rem;
        position: relative;
    }

    .form-group-modern-edit label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: .5rem;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .form-group-modern-edit label i {
        color: #E53935;
        font-size: 1.1rem;
    }

    .form-group-modern-edit label .required {
        color: #dc3545;
        margin-left: .25rem;
    }

    .form-control-modern-edit {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: .75rem 1rem;
        font-size: .95rem;
        transition: all .3s;
    }

    .form-control-modern-edit:focus {
        border-color: #E53935;
        box-shadow: 0 0 0 4px rgba(229, 57, 53, .1);
        outline: none;
    }

    .form-hint-edit {
        font-size: .85rem;
        color: #6c757d;
        margin-top: .5rem;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .form-hint-edit i {
        color: #FFDD5E;
    }

    .current-image-box {
        background: linear-gradient(135deg, #FFF1F1 0%, #fff 100%);
        border: 2px solid #FFDD5E;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        margin-bottom: 1rem;
        box-shadow: 0 4px 15px rgba(255, 221, 94, 0.15);
    }

    .current-image-box img {
        width: 200px;
        height: 200px;
        object-fit: cover;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, .15);
        margin-bottom: 1rem;
    }

    .current-image-box .image-label {
        font-weight: 600;
        color: #FFDD5E;
        margin-bottom: .5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
    }

    .image-upload-area-edit {
        border: 3px dashed #e9ecef;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        background: #f8f9fa;
        transition: all .3s;
        cursor: pointer;
        position: relative;
    }

    .image-upload-area-edit:hover {
        border-color: #FFDD5E;
        background: #fffbf0;
        box-shadow: 0 4px 15px rgba(255, 221, 94, 0.2);
    }

    .image-upload-area-edit.has-file {
        border-color: #28a745;
        background: #f0fff4;
    }

    .image-upload-area-edit .upload-icon {
        font-size: 3rem;
        color: #E53935;
        margin-bottom: 1rem;
    }

    .image-upload-area-edit.has-file .upload-icon {
        color: #28a745;
    }

    .image-preview-edit {
        max-width: 300px;
        max-height: 300px;
        margin: 1rem auto;
        border-radius: 8px;
        display: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, .15);
    }

    .image-preview-edit.show {
        display: block;
    }

    .btn-update-modern {
        background: linear-gradient(135deg, #E53935 0%, #7A0C0C 100%);
        color: #fff;
        border: none;
        padding: .875rem 2.5rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        transition: all .3s;
        box-shadow: 0 4px 12px rgba(229, 57, 53, .3);
    }

    .btn-update-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(229, 57, 53, .4);
        color: #fff;
    }

    .btn-back-modern-edit {
        background: #fff;
        color: #495057;
        border: 2px solid #e9ecef;
        padding: .875rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all .3s;
    }

    .btn-back-modern-edit:hover {
        background: #f8f9fa;
        border-color: #E53935;
        color: #E53935;
    }

    .alert-modern-edit {
        border: none;
        border-radius: 10px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .1);
        display: flex;
        align-items: start;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .alert-modern-edit i {
        font-size: 1.75rem;
        flex-shrink: 0;
        margin-top: .25rem;
    }

    .alert-modern-edit .alert-content {
        flex: 1;
    }

    .alert-modern-edit .alert-heading {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: .5rem;
    }

    .category-select-edit {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23E53935' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 16px;
        padding-right: 2.5rem;
    }

    .product-id-badge {
        background: linear-gradient(135deg, #E53935 0%, #FFDD5E 100%);
        color: #333;
        padding: .5rem 1rem;
        border-radius: 20px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: 1rem;
    }
</style>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Page Header -->
        <div class="page-header-edit">
            <i class="bi bi-pencil-square" style="font-size: 3rem; margin-bottom: 1rem;"></i>
            <h1>Edit Produk</h1>
            <p>Perbarui informasi produk yang sudah ada</p>
            <div class="product-id-badge mt-3">
                <i class="bi bi-hash"></i>
                ID: <?php echo str_pad($id, 4, '0', STR_PAD_LEFT); ?>
            </div>
        </div>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success alert-modern-edit" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <div class="alert-content">
                    <div class="alert-heading">Berhasil!</div>
                    <p class="mb-0"><?php echo htmlspecialchars($successMessage); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-modern-edit" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div class="alert-content">
                    <div class="alert-heading">Validasi Gagal!</div>
                    <p>Terdapat kesalahan pada data yang Anda masukkan:</p>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="form-card-edit card">
            <div class="card-header">
                <h5>
                    <i class="bi bi-pencil-square"></i>
                    Informasi Produk
                </h5>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data" id="editProductForm">
                    <!-- Product Name -->
                    <div class="form-group-modern-edit">
                        <label for="name">
                            <i class="bi bi-box-seam"></i>
                            Nama Produk
                            <span class="required">*</span>
                        </label>
                        <input type="text"
                            class="form-control form-control-modern-edit"
                            id="name"
                            name="name"
                            value="<?php echo htmlspecialchars($name); ?>"
                            placeholder="Contoh: Laptop ASUS ROG"
                            required>
                        <div class="form-hint-edit">
                            <i class="bi bi-info-circle"></i>
                            Masukkan nama produk yang jelas dan deskriptif
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group-modern-edit">
                        <label for="description">
                            <i class="bi bi-text-paragraph"></i>
                            Deskripsi Produk
                            <span class="required">*</span>
                        </label>
                        <textarea class="form-control form-control-modern-edit"
                            id="description"
                            name="description"
                            rows="5"
                            placeholder="Jelaskan detail produk, spesifikasi, keunggulan, dll..."
                            required><?php echo htmlspecialchars($description); ?></textarea>
                        <div class="form-hint-edit">
                            <i class="bi bi-info-circle"></i>
                            Berikan deskripsi lengkap untuk menarik minat pembeli
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="form-group-modern-edit">
                        <label for="category">
                            <i class="bi bi-tag"></i>
                            Kategori Produk
                            <span class="required">*</span>
                        </label>
                        <select class="form-select form-control-modern-edit category-select-edit"
                            id="category"
                            name="category"
                            required>
                            <option value="" disabled <?php echo $category === '' ? 'selected' : ''; ?>>Pilih kategori produk</option>
                            <option value="Minuman" <?php echo $category === 'Minuman' ? 'selected' : ''; ?>>🥤 Minuman</option>
                            <option value="Makanan" <?php echo $category === 'Makanan' ? 'selected' : ''; ?>>🍜 Makanan</option>
                            <option value="Snack" <?php echo $category === 'Snack' ? 'selected' : ''; ?>>🍿 Snack</option>
                            <option value="Lainnya" <?php echo $category === 'Lainnya' ? 'selected' : ''; ?>>📦 Lainnya</option>
                        </select>
                        <div class="form-hint-edit">
                            <i class="bi bi-info-circle"></i>
                            Pilih kategori yang sesuai untuk memudahkan pencarian
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="form-group-modern-edit">
                        <label for="price">
                            <i class="bi bi-currency-dollar"></i>
                            Harga Produk
                            <span class="required">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text" style="border: 2px solid #e9ecef; border-right: none; background: #f8f9fa; font-weight: 600; border-radius: 8px 0 0 8px;">Rp</span>
                            <input type="number"
                                class="form-control form-control-modern-edit"
                                id="price"
                                name="price"
                                min="0"
                                step="0.01"
                                value="<?php echo htmlspecialchars($price); ?>"
                                placeholder="50000"
                                style="border-left: none; border-radius: 0 8px 8px 0;"
                                required>
                        </div>
                        <div class="form-hint-edit">
                            <i class="bi bi-info-circle"></i>
                            Masukkan harga dalam Rupiah (bebas desimal)
                        </div>
                    </div>

                    <!-- Current Image -->
                    <div class="form-group-modern-edit">
                        <label>
                            <i class="bi bi-image-fill"></i>
                            Gambar Saat Ini
                        </label>
                        <?php
                        $currentImageUrl = '';
                        if ($currentImage !== '') {
                            $imageFullPath = __DIR__ . '/../../' . ltrim($currentImage, '/');
                            if (file_exists($imageFullPath)) {
                                $currentImageUrl = '../../' . ltrim($currentImage, '/');
                            }
                        }
                        ?>
                        <?php if ($currentImageUrl !== ''): ?>
                            <div class="current-image-box">
                                <div class="image-label">
                                    <i class="bi bi-image-fill"></i>
                                    Gambar Produk Saat Ini
                                </div>
                                <img src="<?php echo htmlspecialchars($currentImageUrl); ?>" alt="Gambar produk">
                                <p class="text-muted mb-0 small">Unggah gambar baru di bawah jika ingin menggantinya</p>
                            </div>
                        <?php else: ?>
                            <div class="current-image-box">
                                <i class="bi bi-image" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                                <p class="text-muted mb-0">Belum ada gambar. Silakan unggah gambar baru di bawah.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- New Image Upload -->
                    <div class="form-group-modern-edit">
                        <label for="image">
                            <i class="bi bi-upload"></i>
                            Ganti Gambar (Opsional)
                        </label>
                        <div class="image-upload-area-edit" id="uploadAreaEdit" onclick="document.getElementById('image').click()">
                            <i class="bi bi-cloud-upload upload-icon"></i>
                            <h6 id="uploadTextEdit">Klik untuk memilih gambar baru</h6>
                            <p class="text-muted mb-0" id="uploadHintEdit">atau drag & drop file di sini</p>
                            <img id="imagePreviewEdit" class="image-preview-edit" alt="Preview">
                        </div>
                        <input type="file"
                            class="d-none"
                            id="image"
                            name="image"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        <div class="form-hint-edit">
                            <i class="bi bi-info-circle"></i>
                            Format: JPG, PNG, WEBP. Maksimal 2MB. Kosongkan jika tidak ingin mengganti gambar.
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-3 justify-content-between flex-wrap mt-4">
                        <a href="index.php" class="btn btn-back-modern-edit">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
                        </a>
                        <button type="submit" class="btn btn-update-modern">
                            <i class="bi bi-check-circle me-2"></i>Update Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Image Preview functionality for edit
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const uploadArea = document.getElementById('uploadAreaEdit');
        const uploadText = document.getElementById('uploadTextEdit');
        const uploadHint = document.getElementById('uploadHintEdit');
        const preview = document.getElementById('imagePreviewEdit');
        const uploadIcon = uploadArea.querySelector('.upload-icon');

        if (file) {
            // Validate file size
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file terlalu besar! Maksimal 2MB');
                e.target.value = '';
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.add('show');
                uploadArea.classList.add('has-file');
                uploadIcon.className = 'bi bi-check-circle-fill upload-icon';
                uploadText.textContent = file.name;
                uploadHint.textContent = 'Gambar baru akan menggantikan gambar lama';
            };
            reader.readAsDataURL(file);
        }
    });

    // Drag and drop functionality for edit
    const uploadAreaEdit = document.getElementById('uploadAreaEdit');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadAreaEdit.addEventListener(eventName, preventDefaultsEdit, false);
    });

    function preventDefaultsEdit(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadAreaEdit.addEventListener(eventName, () => {
            uploadAreaEdit.style.borderColor = '#FFDD5E';
            uploadAreaEdit.style.background = '#fffbf0';
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadAreaEdit.addEventListener(eventName, () => {
            if (!uploadAreaEdit.classList.contains('has-file')) {
                uploadAreaEdit.style.borderColor = '#e9ecef';
                uploadAreaEdit.style.background = '#f8f9fa';
            }
        }, false);
    });

    uploadAreaEdit.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            document.getElementById('image').files = files;
            const event = new Event('change');
            document.getElementById('image').dispatchEvent(event);
        }
    }, false);
</script>

<?php renderTemplateEnd(); ?>