<?php
require_once '../../koneksi.php';
require_once __DIR__ . '/../../components/template.php';

$errors = [];
$successMessage = '';

$name = '';
$description = '';
$price = '';
$category = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? trim($_POST['price']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';

    if ($name === '') {
        $errors['name'] = 'Nama produk wajib diisi.';
    }

    if ($description === '') {
        $errors['description'] = 'Deskripsi wajib diisi.';
    }

    if ($category === '') {
        $errors['category'] = 'Kategori wajib diisi.';
    }

    if ($price === '' || !is_numeric($price) || (float) $price < 0) {
        $errors['price'] = 'Harga harus berupa angka dan tidak boleh negatif.';
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors['image'] = 'Gambar produk wajib diunggah.';
    }

    $dbImagePath = '';
    if (!isset($errors['image']) && isset($_FILES['image'])) {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileTmpName = $_FILES['image']['tmp_name'];
        $fileSize = (int) $_FILES['image']['size'];

        if (!is_uploaded_file($fileTmpName)) {
            $errors['image'] = 'Upload gambar tidak valid.';
        } else {
            $detectedMime = mime_content_type($fileTmpName);

            if (!in_array($detectedMime, $allowedMimeTypes, true)) {
                $errors['image'] = 'Format gambar harus JPG, PNG, atau WEBP.';
            }

            if ($fileSize > 2 * 1024 * 1024) {
                $errors['image'] = 'Ukuran gambar maksimal 2MB.';
            }
        }

        if (!isset($errors['image'])) {
            $uploadDirectory = __DIR__ . '/../../image/';
            if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0777, true)) {
                $errors['image'] = 'Folder upload gambar gagal dibuat.';
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
                    $errors['image'] = 'Gagal menyimpan gambar ke folder image.';
                } else {
                    $dbImagePath = 'image/' . $generatedFileName;
                }
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare('INSERT INTO products (name, description, price, category, image) VALUES (?, ?, ?, ?, ?)');

        if (!$stmt) {
            $errors['database'] = 'Prepare query gagal: ' . $conn->error;
        } else {
            $priceValue = (float) $price;
            $stmt->bind_param('ssdss', $name, $description, $priceValue, $category, $dbImagePath);

            if ($stmt->execute()) {
                $successMessage = 'Produk berhasil disimpan.';
                $name = '';
                $description = '';
                $price = '';
                $category = '';
            } else {
                if ($dbImagePath !== '' && file_exists(__DIR__ . '/../../' . $dbImagePath)) {
                    unlink(__DIR__ . '/../../' . $dbImagePath);
                }
                $errors['database'] = 'Gagal menyimpan data ke database: ' . $stmt->error;
            }

            $stmt->close();
        }
    }
}

renderTemplateStart('Input Produk', 'create-product', '../../');
?>

<style>
    /* Create Product Page Styles */
    .page-header-create {
        background: linear-gradient(135deg, #E53935 0%, #7A0C0C 100%);
        color: #fff;
        padding: 2.5rem 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(229, 57, 53, .3);
        text-align: center;
    }

    .page-header-create h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: .5rem;
    }

    .page-header-create p {
        margin: 0;
        opacity: .9;
        font-size: 1rem;
    }

    .form-card {
        background: #fff;
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, .08);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .form-card .card-header {
        background: linear-gradient(135deg, #FFF1F1 0%, #e9ecef 100%);
        border-bottom: 3px solid #E53935;
        padding: 1.5rem;
    }

    .form-card .card-header h5 {
        font-weight: 700;
        color: #2d3748;
        margin: 0;
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .form-card .card-header h5 i {
        color: #E53935;
        font-size: 1.5rem;
    }

    .form-card .card-body {
        padding: 2rem;
    }

    .form-group-modern {
        margin-bottom: 1.75rem;
        position: relative;
    }

    .form-group-modern label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: .5rem;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .form-group-modern label i {
        color: #E53935;
        font-size: 1.1rem;
    }

    .form-group-modern label .required {
        color: #dc3545;
        margin-left: .25rem;
    }

    .form-control-modern {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: .75rem 1rem;
        font-size: .95rem;
        transition: all .3s;
    }

    .form-control-modern:focus {
        border-color: #E53935;
        box-shadow: 0 0 0 4px rgba(229, 57, 53, .1);
        outline: none;
    }

    .form-control-modern:disabled {
        background: #f8f9fa;
    }

    .form-hint {
        font-size: .85rem;
        color: #6c757d;
        margin-top: .5rem;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .form-hint i {
        color: #FFDD5E;
    }

    .image-upload-area {
        border: 3px dashed #e9ecef;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        background: #f8f9fa;
        transition: all .3s;
        cursor: pointer;
        position: relative;
    }

    .image-upload-area:hover {
        border-color: #FFDD5E;
        background: #fffbf0;
        box-shadow: 0 4px 15px rgba(255, 221, 94, 0.2);
    }

    .image-upload-area.has-file {
        border-color: #28a745;
        background: #f0fff4;
    }

    .image-upload-area .upload-icon {
        font-size: 3rem;
        color: #E53935;
        margin-bottom: 1rem;
    }

    .image-upload-area.has-file .upload-icon {
        color: #28a745;
    }

    .image-preview {
        max-width: 300px;
        max-height: 300px;
        margin: 1rem auto;
        border-radius: 8px;
        display: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, .15);
    }

    .image-preview.show {
        display: block;
    }

    .btn-submit-modern {
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

    .btn-submit-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(229, 57, 53, .4);
        color: #fff;
    }

    .btn-back-modern {
        background: #fff;
        color: #495057;
        border: 2px solid #e9ecef;
        padding: .875rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all .3s;
    }

    .btn-back-modern:hover {
        background: #f8f9fa;
        border-color: #E53935;
        color: #E53935;
    }

    .alert-modern-create {
        border: none;
        border-radius: 10px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .1);
        display: flex;
        align-items: start;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .alert-modern-create i {
        font-size: 1.75rem;
        flex-shrink: 0;
        margin-top: .25rem;
    }

    .alert-modern-create .alert-content {
        flex: 1;
    }

    .alert-modern-create .alert-heading {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: .5rem;
    }

    .category-select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23E53935' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 16px;
        padding-right: 2.5rem;
    }
</style>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Page Header -->
        <div class="page-header-create">
            <i class="bi bi-plus-circle-fill" style="font-size: 3rem; margin-bottom: 1rem;"></i>
            <h1>Tambah Produk Baru</h1>
            <p>Lengkapi formulir di bawah untuk menambahkan produk ke dalam sistem</p>
        </div>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success alert-modern-create" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <div class="alert-content">
                    <div class="alert-heading">Berhasil!</div>
                    <p class="mb-0"><?php echo htmlspecialchars($successMessage); ?></p>
                    <a href="index.php" class="btn btn-sm btn-success mt-2">
                        <i class="bi bi-list-ul me-1"></i>Lihat Daftar Produk
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-modern-create" role="alert">
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
        <div class="form-card card">
            <div class="card-header">
                <h5>
                    <i class="bi bi-pencil-square"></i>
                    Informasi Produk
                </h5>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data" id="productForm">
                    <!-- Product Name -->
                    <div class="form-group-modern">
                        <label for="name">
                            <i class="bi bi-box-seam"></i>
                            Nama Produk
                            <span class="required">*</span>
                        </label>
                        <input type="text"
                            class="form-control form-control-modern"
                            id="name"
                            name="name"
                            value="<?php echo htmlspecialchars($name); ?>"
                            placeholder="Contoh: Laptop ASUS ROG"
                            required>
                        <div class="form-hint">
                            <i class="bi bi-info-circle"></i>
                            Masukkan nama produk yang jelas dan deskriptif
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group-modern">
                        <label for="description">
                            <i class="bi bi-text-paragraph"></i>
                            Deskripsi Produk
                            <span class="required">*</span>
                        </label>
                        <textarea class="form-control form-control-modern"
                            id="description"
                            name="description"
                            rows="5"
                            placeholder="Jelaskan detail produk, spesifikasi, keunggulan, dll..."
                            required><?php echo htmlspecialchars($description); ?></textarea>
                        <div class="form-hint">
                            <i class="bi bi-info-circle"></i>
                            Berikan deskripsi lengkap untuk menarik minat pembeli
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="form-group-modern">
                        <label for="category">
                            <i class="bi bi-tag"></i>
                            Kategori Produk
                            <span class="required">*</span>
                        </label>
                        <select class="form-select form-control-modern category-select"
                            id="category"
                            name="category"
                            required>
                            <option value="" disabled <?php echo $category === '' ? 'selected' : ''; ?>>Pilih kategori produk</option>
                            <option value="Minuman" <?php echo $category === 'Minuman' ? 'selected' : ''; ?>>🥤 Minuman</option>
                            <option value="Makanan" <?php echo $category === 'Makanan' ? 'selected' : ''; ?>>🍜 Makanan</option>
                            <option value="Snack" <?php echo $category === 'Snack' ? 'selected' : ''; ?>>🍿 Snack</option>
                            <option value="Lainnya" <?php echo $category === 'Lainnya' ? 'selected' : ''; ?>>📦 Lainnya</option>
                        </select>
                        <div class="form-hint">
                            <i class="bi bi-info-circle"></i>
                            Pilih kategori yang sesuai untuk memudahkan pencarian
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="form-group-modern">
                        <label for="price">
                            <i class="bi bi-currency-dollar"></i>
                            Harga Produk
                            <span class="required">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text" style="border: 2px solid #e9ecef; border-right: none; background: #f8f9fa; font-weight: 600; border-radius: 8px 0 0 8px;">Rp</span>
                            <input type="number"
                                class="form-control form-control-modern"
                                id="price"
                                name="price"
                                min="0"
                                step="0.01"
                                value="<?php echo htmlspecialchars($price); ?>"
                                placeholder="50000"
                                style="border-left: none; border-radius: 0 8px 8px 0;"
                                required>
                        </div>
                        <div class="form-hint">
                            <i class="bi bi-info-circle"></i>
                            Masukkan harga dalam Rupiah (bebas desimal)
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div class="form-group-modern">
                        <label for="image">
                            <i class="bi bi-image"></i>
                            Gambar Produk
                            <span class="required">*</span>
                        </label>
                        <div class="image-upload-area" id="uploadArea" onclick="document.getElementById('image').click()">
                            <i class="bi bi-cloud-upload upload-icon"></i>
                            <h6 id="uploadText">Klik untuk memilih gambar</h6>
                            <p class="text-muted mb-0" id="uploadHint">atau drag & drop file di sini</p>
                            <img id="imagePreview" class="image-preview" alt="Preview">
                        </div>
                        <input type="file"
                            class="d-none"
                            id="image"
                            name="image"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            required>
                        <div class="form-hint">
                            <i class="bi bi-info-circle"></i>
                            Format: JPG, PNG, WEBP. Maksimal 2MB
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-3 justify-content-between flex-wrap mt-4">
                        <a href="index.php" class="btn btn-back-modern">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-submit-modern">
                            <i class="bi bi-check-circle me-2"></i>Simpan Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Image Preview functionality
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const uploadArea = document.getElementById('uploadArea');
        const uploadText = document.getElementById('uploadText');
        const uploadHint = document.getElementById('uploadHint');
        const preview = document.getElementById('imagePreview');
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
                uploadHint.textContent = 'Klik untuk mengubah gambar';
            };
            reader.readAsDataURL(file);
        }
    });

    // Drag and drop functionality
    const uploadArea = document.getElementById('uploadArea');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.style.borderColor = '#FFDD5E';
            uploadArea.style.background = '#fffbf0';
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            if (!uploadArea.classList.contains('has-file')) {
                uploadArea.style.borderColor = '#e9ecef';
                uploadArea.style.background = '#f8f9fa';
            }
        }, false);
    });

    uploadArea.addEventListener('drop', function(e) {
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