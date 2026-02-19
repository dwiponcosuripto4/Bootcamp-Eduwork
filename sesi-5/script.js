// Data Produk
const products = [
  {
    id: 1,
    name: "Laptop Gaming ROG",
    description:
      "Laptop gaming dengan spesifikasi tinggi, prosesor Intel Core i7 dan RTX 3060",
    price: 15000000,
    image:
      "https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=400&h=300&fit=crop",
    category: "Elektronik",
  },
  {
    id: 2,
    name: "Smartphone Samsung Galaxy",
    description:
      "Smartphone flagship dengan kamera 108MP dan layar AMOLED 120Hz",
    price: 8000000,
    image:
      "https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=400&h=300&fit=crop",
    category: "Elektronik",
  },
  {
    id: 3,
    name: "Sepatu Olahraga Nike",
    description:
      "Sepatu running dengan teknologi Air Zoom untuk kenyamanan maksimal",
    price: 1500000,
    image:
      "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=300&fit=crop",
    category: "Fashion",
  },
  {
    id: 4,
    name: "Kamera DSLR Canon",
    description: "Kamera DSLR profesional dengan sensor 24MP dan video 4K",
    price: 12000000,
    image:
      "https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=400&h=300&fit=crop",
    category: "Elektronik",
  },
  {
    id: 5,
    name: "Jam Tangan Smartwatch",
    description: "Smartwatch dengan fitur kesehatan lengkap dan tahan air",
    price: 3000000,
    image:
      "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&h=300&fit=crop",
    category: "Aksesoris",
  },
  {
    id: 6,
    name: "Tas Kulit Premium",
    description: "Tas kulit asli dengan desain elegan dan ruang yang luas",
    price: 2500000,
    image:
      "https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=400&h=300&fit=crop",
    category: "Fashion",
  },
  {
    id: 7,
    name: "Headphone Wireless Sony",
    description: "Headphone noise-cancelling dengan kualitas audio premium",
    price: 4500000,
    image:
      "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=300&fit=crop",
    category: "Elektronik",
  },
  {
    id: 8,
    name: "Meja Kerja Minimalis",
    description:
      "Meja kerja ergonomis dengan desain modern dan bahan berkualitas",
    price: 2000000,
    image:
      "https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?w=400&h=300&fit=crop",
    category: "Furniture",
  },
  {
    id: 9,
    name: "Kursi Gaming",
    description:
      "Kursi gaming ergonomis dengan penyangga lumbar dan sandaran kepala",
    price: 3500000,
    image:
      "https://images.unsplash.com/photo-1598550476439-6847785fcea6?w=400&h=300&fit=crop",
    category: "Furniture",
  },
  {
    id: 10,
    name: "Jaket Denim",
    description: "Jaket denim klasik dengan bahan premium dan desain timeless",
    price: 800000,
    image:
      "https://images.unsplash.com/photo-1551028719-00167b16eac5?w=400&h=300&fit=crop",
    category: "Fashion",
  },
  {
    id: 11,
    name: "Tablet iPad Air",
    description: "Tablet powerful dengan chip M1 dan layar Liquid Retina",
    price: 9000000,
    image:
      "https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=400&h=300&fit=crop",
    category: "Elektronik",
  },
  {
    id: 12,
    name: "Kacamata Hitam Ray-Ban",
    description:
      "Kacamata hitam klasik dengan perlindungan UV dan desain ikonik",
    price: 1200000,
    image:
      "https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=400&h=300&fit=crop",
    category: "Aksesoris",
  },
];

// Variabel untuk menyimpan data yang difilter
let filteredProducts = [...products];

// Format Rupiah
function formatRupiah(number) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(number);
}

// Render Produk
function renderProducts(productsToRender) {
  const container = document.getElementById("productContainer");
  const noProductsMessage = document.getElementById("noProductsMessage");

  container.innerHTML = "";

  if (productsToRender.length === 0) {
    noProductsMessage.style.display = "block";
    return;
  }

  noProductsMessage.style.display = "none";

  productsToRender.forEach((product) => {
    const productCard = `
            <div class="col">
                <div class="card product-card">
                    <img src="${product.image}" class="card-img-top product-image" alt="${product.name}">
                    <div class="card-body">
                        <span class="badge bg-primary mb-2">${product.category}</span>
                        <h5 class="card-title">${product.name}</h5>
                        <p class="card-text">${product.description}</p>
                        <div class="mt-auto">
                            <h4 class="text-success">${formatRupiah(product.price)}</h4>
                            <button class="btn btn-primary w-100">Beli Sekarang</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    container.innerHTML += productCard;
  });
}

// Populate Category Filter
function populateCategoryFilter() {
  const categoryFilter = document.getElementById("categoryFilter");
  const categories = [...new Set(products.map((product) => product.category))];

  categories.forEach((category) => {
    const option = document.createElement("option");
    option.value = category;
    option.textContent = category;
    categoryFilter.appendChild(option);
  });
}

// Filter dan Sort Produk
function filterAndSortProducts() {
  const searchValue = document
    .getElementById("searchInput")
    .value.toLowerCase();
  const categoryValue = document.getElementById("categoryFilter").value;
  const sortValue = document.getElementById("priceSort").value;

  // Filter berdasarkan pencarian dan kategori
  filteredProducts = products.filter((product) => {
    const matchesSearch =
      product.name.toLowerCase().includes(searchValue) ||
      product.description.toLowerCase().includes(searchValue);
    const matchesCategory =
      categoryValue === "all" || product.category === categoryValue;

    return matchesSearch && matchesCategory;
  });

  // Sort berdasarkan harga
  if (sortValue === "low") {
    filteredProducts.sort((a, b) => a.price - b.price);
  } else if (sortValue === "high") {
    filteredProducts.sort((a, b) => b.price - a.price);
  } else {
    // Default: sort by id
    filteredProducts.sort((a, b) => a.id - b.id);
  }

  renderProducts(filteredProducts);
}

// Event Listeners
document.addEventListener("DOMContentLoaded", function () {
  // Populate category filter
  populateCategoryFilter();

  // Render semua produk saat halaman dimuat
  renderProducts(products);

  // Event listener untuk pencarian
  document
    .getElementById("searchInput")
    .addEventListener("input", filterAndSortProducts);

  // Event listener untuk filter kategori
  document
    .getElementById("categoryFilter")
    .addEventListener("change", filterAndSortProducts);

  // Event listener untuk sort harga
  document
    .getElementById("priceSort")
    .addEventListener("change", filterAndSortProducts);
});
