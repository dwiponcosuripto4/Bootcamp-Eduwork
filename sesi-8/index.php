<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- Bootstrap 5 CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet" />
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <h1 class="text-center mt-5">Form Input Produk</h1>

                <!-- Form Input Produk -->
                <form action="form_process.php" method="post" id="productForm" novalidate enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama Anda">
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Harga</label>
                        <input type="number" class="form-control" id="price" name="price" placeholder="Masukkan harga produk">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" placeholder="Masukkan deskripsi produk"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Kategori</label>
                        <select class="form-control" id="category" name="category">
                            <option value="">Pilih Kategori</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Makanan">Makanan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock</label>
                        <input type="number" class="form-control" id="stock" name="stock" placeholder="Masukkan jumlah stok produk">
                    </div>
                    <!-- Image Upload -->
                    <div class="mb-3">
                        <label for="image" class="form-label">Gambar Produk</label>
                        <input type="file" class="form-control" id="image" name="image">
                        <div class="invalid-feedback">Please upload an image</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Produk</button>
                </form>
            </div>
        </div>
    </div>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Validation JS -->
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            // Remove previous error messages
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach(msg => msg.remove());

            // Remove error styling
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => input.classList.remove('is-invalid'));

            let isValid = true;

            // Validate Name
            const name = document.getElementById('name');
            if (name.value.trim() === '') {
                showError(name, 'Nama produk wajib diisi');
                isValid = false;
            } else if (name.value.trim().length < 3) {
                showError(name, 'Nama produk minimal 3 karakter');
                isValid = false;
            }

            // Validate Price
            const price = document.getElementById('price');
            if (price.value.trim() === '') {
                showError(price, 'Harga produk wajib diisi');
                isValid = false;
            } else if (parseFloat(price.value) <= 0) {
                showError(price, 'Harga harus lebih besar dari 0');
                isValid = false;
            }

            // Validate Description
            const description = document.getElementById('description');
            if (description.value.trim() === '') {
                showError(description, 'Deskripsi produk wajib diisi');
                isValid = false;
            } else if (description.value.trim().length < 10) {
                showError(description, 'Deskripsi minimal 10 karakter');
                isValid = false;
            }

            // Validate Category
            const category = document.getElementById('category');
            if (category.value === '') {
                showError(category, 'Kategori wajib dipilih');
                isValid = false;
            }

            // Validate Stock
            const stock = document.getElementById('stock');
            if (stock.value.trim() === '') {
                showError(stock, 'Jumlah stok wajib diisi');
                isValid = false;
            } else if (parseInt(stock.value) < 0) {
                showError(stock, 'Stok tidak boleh negatif');
                isValid = false;
            }

            // Prevent form submission if validation fails
            if (!isValid) {
                e.preventDefault();
            }
        });

        // Function to show error message
        function showError(input, message) {
            input.classList.add('is-invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message text-danger small mt-1';
            errorDiv.textContent = message;
            input.parentElement.appendChild(errorDiv);
        }
    </script>
</body>

</html>