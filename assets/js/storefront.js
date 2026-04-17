/**
 * UniMerch — Storefront JavaScript
 * Product grid loading, category filtering, search, and product detail modal
 */

// ============================================================
// Global State
// ============================================================
let currentCategory = 'all';
let currentSearch = '';
let currentSort = 'newest';
let currentPage = 1;
let isLoading = false;
let hasMore = true;

// ============================================================
// Product Loading
// ============================================================
function loadProducts(append = false) {
  if (isLoading) return;
  isLoading = true;

  if (!append) {
    currentPage = 1;
    $('#productsGrid').html(generateSkeletons(8));
    $('#emptyState').hide();
    $('#loadMoreContainer').hide();
  }

  $.ajax({
    url: `${BASE_URL}/api/products.php`,
    data: {
      category: currentCategory,
      search: currentSearch,
      sort: currentSort,
      page: currentPage,
      limit: 12
    },
    success(res) {
      if (!res.success) return;

      const { data, pagination } = res;
      hasMore = pagination.hasMore;

      if (!append) {
        $('#productsGrid').empty();
      }

      if (data.length === 0 && !append) {
        $('#emptyState').show();
        $('#productsCount').text('No products found');
        return;
      }

      data.forEach((product, i) => {
        const card = createProductCard(product);
        card.css('animation-delay', `${i * 60}ms`);
        $('#productsGrid').append(card);
      });

      $('#productsCount').text(`Showing ${$('#productsGrid .product-card').length} of ${pagination.total} products`);
      $('#loadMoreContainer').toggle(hasMore);
    },
    error() {
      if (!append) {
        $('#productsGrid').html(`
          <div class="col-12 text-center py-5">
            <i class="bi bi-wifi-off" style="font-size:3rem; color:var(--gray-300);"></i>
            <h4 class="mt-3" style="color:var(--gray-600);">Connection Error</h4>
            <p class="text-muted">Make sure XAMPP Apache and MySQL are running.</p>
            <button class="btn btn-primary-gradient" onclick="loadProducts()">
              <i class="bi bi-arrow-clockwise me-2"></i>Retry
            </button>
          </div>
        `);
      }
    },
    complete() {
      isLoading = false;
    }
  });
}

function createProductCard(product) {
  const sizes = product.sizes;
  const sizesHTML = sizes 
    ? sizes.slice(0, 4).map(s => `<span>${s}</span>`).join('') + (sizes.length > 4 ? `<span>+${sizes.length - 4}</span>` : '')
    : '';

  const stockClass = product.stock <= 10 ? 'low' : '';
  const stockText = product.stock <= 10 
    ? `<i class="bi bi-exclamation-circle me-1"></i>Only ${product.stock} left`
    : `${product.stock} in stock`;

  const badges = [];
  if (product.featured) badges.push('<span class="product-card-badge featured"><i class="bi bi-star-fill me-1"></i>Featured</span>');
  if (product.stock <= 5 && product.stock > 0) badges.push('<span class="product-card-badge low-stock">Low Stock</span>');

  const imgSrc = `${BASE_URL}/uploads/${product.image}`;
  const fallbackSrc = `https://placehold.co/400x260/${product.category_color ? product.category_color.replace('#','') : '1e40af'}/ffffff?text=${encodeURIComponent(product.category_code)}`;

  return $(`
    <div class="product-card animate-in" data-product-id="${product.id}">
      <div class="product-card-img">
        <img src="${imgSrc}" alt="${product.name}" 
             onerror="this.onerror=null; this.src='${fallbackSrc}';" loading="lazy">
        ${badges.join('')}
        <div class="product-card-quick-add">
          <button onclick="openProductModal(${product.id}); event.stopPropagation();">
            <i class="bi bi-bag-plus me-1"></i>Quick Add
          </button>
        </div>
      </div>
      <div class="product-card-body">
        <div class="product-card-category">${product.category_code}</div>
        <h3 class="product-card-title">${product.name}</h3>
        <div class="product-card-price">
          <span class="currency">₱</span>${parseFloat(product.price).toLocaleString('en-PH', {minimumFractionDigits: 2})}
        </div>
        <div class="product-card-meta">
          <span class="product-card-stock ${stockClass}">${stockText}</span>
          ${sizesHTML ? `<div class="product-card-sizes">${sizesHTML}</div>` : ''}
        </div>
      </div>
    </div>
  `).on('click', function() { openProductModal(product.id); });
}

function generateSkeletons(count) {
  let html = '';
  for (let i = 0; i < count; i++) {
    html += `
      <div class="product-card skeleton-card">
        <div class="skeleton" style="height: 260px;"></div>
        <div style="padding: 1.25rem;">
          <div class="skeleton" style="height: 14px; width: 60px; margin-bottom: 8px;"></div>
          <div class="skeleton" style="height: 18px; width: 80%; margin-bottom: 8px;"></div>
          <div class="skeleton" style="height: 22px; width: 40%;"></div>
        </div>
      </div>
    `;
  }
  return html;
}

// ============================================================
// Product Detail Modal
// ============================================================
let selectedProduct = null;
let selectedSize = null;

function openProductModal(productId) {
  $.get(`${BASE_URL}/api/products.php`, { search: '', category: 'all', page: 1, limit: 100 }, function(res) {
    if (!res.success) return;
    
    const product = res.data.find(p => p.id == productId);
    if (!product) return;

    selectedProduct = product;
    selectedSize = null;

    const imgSrc = `${BASE_URL}/uploads/${product.image}`;
    const fallback = `https://placehold.co/500x350/${product.category_color ? product.category_color.replace('#','') : '1e40af'}/ffffff?text=${encodeURIComponent(product.name)}`;

    $('#modalProductImg').attr('src', imgSrc).attr('onerror', `this.onerror=null;this.src='${fallback}'`);
    $('#modalProductName').text(product.name);
    $('#modalProductCategory').text(product.category_code + ' — ' + product.category_name);
    $('#modalProductPrice').html(`₱${parseFloat(product.price).toLocaleString('en-PH', {minimumFractionDigits: 2})}`);
    $('#modalProductDesc').text(product.description || 'No description available.');
    $('#modalStock').text(`${product.stock} in stock`);
    $('#qtyInput').val(1).attr('max', product.stock);

    // Sizes
    if (product.sizes && product.sizes.length > 0) {
      $('#modalSizeSection').show();
      let sizeBtns = '';
      product.sizes.forEach(s => {
        sizeBtns += `<button class="size-btn" data-size="${s}">${s}</button>`;
      });
      $('#modalSizes').html(sizeBtns);
    } else {
      $('#modalSizeSection').hide();
    }

    const modalEl = document.getElementById('productModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  });
}

// Reset logic after closing to ensure clean states
$('#productModal').on('hidden.bs.modal', function () {
  selectedProduct = null;
  selectedSize = null;
  $('#modalSizes').empty();
  $('#qtyInput').val(1);
});

// Admin-style forced closure for the X button
$(document).on('click', '#productModal .btn-close', function(e) {
  e.preventDefault();
  const modalEl = document.getElementById('productModal');
  if (modalEl) {
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) {
      modal.hide();
    }
  }
});

// Size selection
$(document).on('click', '.size-btn', function() {
  $('.size-btn').removeClass('active');
  $(this).addClass('active');
  selectedSize = $(this).data('size');
});

// Quantity controls
$(document).on('click', '#qtyMinus', function() {
  let val = parseInt($('#qtyInput').val());
  if (val > 1) $('#qtyInput').val(val - 1);
});

$(document).on('click', '#qtyPlus', function() {
  let val = parseInt($('#qtyInput').val());
  let max = selectedProduct ? selectedProduct.stock : 99;
  if (val < max) $('#qtyInput').val(val + 1);
});

// Add to Cart
$(document).on('click', '#addToCartBtn', function() {
  if (!selectedProduct) return;

  // Validate size selection
  if (selectedProduct.sizes && selectedProduct.sizes.length > 0 && !selectedSize) {
    showToast('Please select a size', 'warning');
    return;
  }

  const btn = $(this);
  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Adding...');

  $.ajax({
    url: `${BASE_URL}/api/cart.php`,
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({
      product_id: selectedProduct.id,
      quantity: parseInt($('#qtyInput').val()),
      size: selectedSize
    }),
    success(res) {
      if (res.success) {
        showToast(res.message, 'success');
        updateCartBadge(res.cart_count);
        bootstrap.Modal.getInstance('#productModal')?.hide();
      } else {
        showToast(res.message, 'error');
      }
    },
    error() {
      showToast('Failed to add to cart', 'error');
    },
    complete() {
      btn.prop('disabled', false).html('<i class="bi bi-bag-plus me-2"></i>Add to Cart');
    }
  });
});

// ============================================================
// Category Filter
// ============================================================
$(document).on('click', '.category-pill', function() {
  $('.category-pill').removeClass('active');
  $(this).addClass('active');
  currentCategory = $(this).data('category');
  loadProducts();
});

// ============================================================
// Search (Debounced)
// ============================================================
let searchTimeout;
$(document).on('input', '#searchInput', function() {
  clearTimeout(searchTimeout);
  const val = $(this).val().trim();
  searchTimeout = setTimeout(() => {
    currentSearch = val;
    loadProducts();
  }, 350);
});

// ============================================================
// Sort
// ============================================================
$(document).on('click', '.sort-option', function(e) {
  e.preventDefault();
  $('.sort-option').removeClass('active');
  $(this).addClass('active');
  currentSort = $(this).data('sort');
  loadProducts();
});

// ============================================================
// Load More
// ============================================================
$(document).on('click', '#loadMoreBtn', function() {
  currentPage++;
  loadProducts(true);
});

// ============================================================
// Cart Badge
// ============================================================
function updateCartBadge(count) {
  const badge = $('#navCartBadge');
  if (count > 0) {
    badge.text(count).show().addClass('bounce');
    setTimeout(() => badge.removeClass('bounce'), 500);
  } else {
    badge.hide();
  }
}

function loadCartCount() {
  $.get(`${BASE_URL}/api/cart.php`, function(res) {
    if (res.success) {
      updateCartBadge(res.summary.item_count);
    }
  });
}

// ============================================================
// Navbar scroll effect
// ============================================================
$(window).on('scroll', function() {
  if ($(this).scrollTop() > 10) {
    $('.um-navbar').addClass('scrolled');
  } else {
    $('.um-navbar').removeClass('scrolled');
  }
});

// ============================================================
// Init
// ============================================================
$(document).ready(function() {
  loadProducts();
  loadCartCount();
});
