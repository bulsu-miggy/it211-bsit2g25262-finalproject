/**
 * UniMerch — Admin Products Management
 */

$(document).ready(function() {
  loadAdminProducts();

  // Filters
  $('#productSearch').on('input', debounce(loadAdminProducts, 350));
  $('#productCategoryFilter, #productStatusFilter').on('change', loadAdminProducts);

  // Image preview
  $('#productImage').on('change', function() {
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        $('#imagePreviewZone').addClass('has-image')
          .html(`<img src="${e.target.result}" alt="Preview" style="max-width:100%;">`);
      };
      reader.readAsDataURL(file);
    }
  });
});

function loadAdminProducts() {
  const search = $('#productSearch').val() || '';
  const category = $('#productCategoryFilter').val() || '';
  const status = $('#productStatusFilter').val() || '';

  $.get(`${BASE_URL}/api/admin/products.php`, { search, category, status }, function(res) {
    if (!res.success) return;

    if (res.data.length === 0) {
      $('#productsTableBody').html('<tr><td colspan="7" class="text-center text-muted py-4">No products found</td></tr>');
      return;
    }

    let html = '';
    res.data.forEach(p => {
      const imgSrc = p.image_url;
      const fallback = `https://placehold.co/40x40/e2e8f0/64748b?text=${p.category_code}`;
      const stockClass = p.stock <= 10 ? 'text-danger fw-bold' : '';

      html += `
        <tr>
          <td data-label="Product">
            <div class="product-cell">
              <img src="${imgSrc}" alt="" onerror="this.src='${fallback}'">
              <div class="flex-grow-1">
                <div class="d-flex align-items-center justify-content-between">
                  <div class="product-name">${p.name}</div>
                  ${p.featured ? '<i class="bi bi-star-fill text-warning d-md-none" title="Featured"></i>' : ''}
                </div>
                <div class="product-sku">ID: ${p.id}</div>
              </div>
            </div>
          </td>
          <td data-label="Category" class="text-md-center"><span class="badge bg-primary-subtle text-primary">${p.category_code}</span></td>
          <td data-label="Price" class="fw-bold text-end text-md-center">₱${parseFloat(p.price).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
          <td data-label="Stock" class="${stockClass} text-end text-md-center">${p.stock}</td>
          <td data-label="Status" class="d-none d-md-table-cell text-center">
            <div class="form-check form-switch d-inline-block">
              <input class="form-check-input" type="checkbox" ${p.status === 'active' ? 'checked' : ''} 
                     onchange="toggleProductStatus(${p.id}, this.checked)">
            </div>
          </td>
          <td data-label="Featured" class="d-none d-md-table-cell text-center" style="vertical-align: middle;">
            ${p.featured ? '<i class="bi bi-star-fill text-warning fs-5"></i>' : '<i class="bi bi-star text-muted fs-5"></i>'}
          </td>
          <td data-label="Actions" class="actions-cell text-center">
            <div class="action-toolbar justify-content-md-center">
              <div class="action-item status-toggle">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" ${p.status === 'active' ? 'checked' : ''} 
                         onchange="toggleProductStatus(${p.id}, this.checked)">
                </div>
              </div>
              <button class="action-item edit-btn" onclick="editProduct(${p.id})" title="Edit">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="action-item delete-btn text-danger" onclick="deleteProduct(${p.id}, '${p.name.replace(/'/g, "\\'")}')" title="Delete">
                <i class="bi bi-trash3"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    });

    $('#productsTableBody').html(html);
  });
}

function resetProductForm() {
  $('#productModalTitle').text('Add Product');
  $('#productId').val('');
  $('#productForm')[0].reset();
  $('#imagePreviewZone').removeClass('has-image').html(`
    <i class="bi bi-cloud-arrow-up" style="font-size:2rem; color:var(--gray-400);"></i>
    <p class="text-muted mb-0 mt-2" style="font-size:0.85rem;">Click to upload</p>
  `);
}

function editProduct(id) {
  $.get(`${BASE_URL}/api/admin/products.php`, { search: '' }, function(res) {
    if (!res.success) return;
    const product = res.data.find(p => p.id == id);
    if (!product) return;

    $('#productModalTitle').text('Edit Product');
    $('#productId').val(product.id);
    $('#productName').val(product.name);
    $('#productCategory').val(product.category_id);
    $('#productDescription').val(product.description);
    $('#productPrice').val(product.price);
    $('#productStock').val(product.stock);
    $('#productSizes').val(product.sizes ? product.sizes.join(',') : '');
    $('#productFeatured').prop('checked', product.featured == 1);

    // Show current image
    const imgSrc = product.image_url;
    $('#imagePreviewZone').addClass('has-image')
      .html(`<img src="${imgSrc}" alt="Current" style="max-width:100%;" onerror="this.parentElement.classList.remove('has-image');this.remove();">`);

    const modal = new bootstrap.Modal('#productModal');
    modal.show();
  });
}

function saveProduct() {
  const id = $('#productId').val();
  const formData = new FormData();

  formData.append('name', $('#productName').val());
  formData.append('category_id', $('#productCategory').val());
  formData.append('description', $('#productDescription').val());
  formData.append('price', $('#productPrice').val());
  formData.append('stock', $('#productStock').val());
  formData.append('sizes', $('#productSizes').val());
  formData.append('featured', $('#productFeatured').is(':checked') ? 1 : 0);

  const imageFile = $('#productImage')[0].files[0];
  if (imageFile) formData.append('image', imageFile);

  const btn = $('#saveProductBtn');
  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

  let url = `${BASE_URL}/api/admin/products.php`;
  let method = 'POST';

  if (id) {
    formData.append('id', id);
    // For PUT with FormData, we use POST with _method override, or just use POST
    url += `?id=${id}`;
  }

  $.ajax({
    url,
    method,
    data: formData,
    processData: false,
    contentType: false,
    success(res) {
      if (res.success) {
        showToast(res.message, 'success');
        bootstrap.Modal.getInstance('#productModal')?.hide();
        loadAdminProducts();
      } else {
        showToast(res.message, 'error');
      }
    },
    error() {
      showToast('Failed to save product', 'error');
    },
    complete() {
      btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Save Product');
    }
  });
}

function toggleProductStatus(id, isActive) {
  $.ajax({
    url: `${BASE_URL}/api/admin/products.php`,
    method: 'PUT',
    contentType: 'application/json',
    data: JSON.stringify({ id, status: isActive ? 'active' : 'inactive' }),
    success(res) {
      if (res.success) {
        showToast(`Product ${isActive ? 'activated' : 'deactivated'}`, 'success');
      }
    }
  });
}

function deleteProduct(id, name) {
  if (!confirm(`Delete "${name}"? This action cannot be undone.`)) return;

  $.ajax({
    url: `${BASE_URL}/api/admin/products.php?id=${id}`,
    method: 'DELETE',
    success(res) {
      if (res.success) {
        showToast('Product deleted', 'success');
        loadAdminProducts();
      } else {
        showToast(res.message, 'error');
      }
    }
  });
}

function debounce(func, wait) {
  let timeout;
  return function(...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), wait);
  };
}
