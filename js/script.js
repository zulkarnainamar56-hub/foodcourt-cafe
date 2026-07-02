// Toggle dropdown menu
function toggleDropdown(event) {
    event.preventDefault();
    const dropdown = event.target.closest('.dropdown').querySelector('.dropdown-menu');
    dropdown.classList.toggle('active');
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('active');
            });
        }
    });
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

// Show alert message
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-' + type;
    alertDiv.textContent = message;
    
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.insertBefore(alertDiv, mainContent.firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}

// Add to cart via AJAX
function addToCart(menuItemId, quantity = 1) {
    if (quantity < 1) {
        showAlert('Jumlah tidak valid!', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('menu_item_id', menuItemId);
    formData.append('quantity', quantity);
    
    fetch('../../api/add-to-cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Produk berhasil ditambahkan ke keranjang!', 'success');
            // Update cart count
            location.reload();
        } else {
            showAlert(data.message || 'Gagal menambahkan ke keranjang', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Terjadi kesalahan!', 'error');
    });
}

// Remove from cart
function removeFromCart(cartId) {
    if (confirm('Hapus item dari keranjang?')) {
        const formData = new FormData();
        formData.append('cart_id', cartId);
        
        fetch('../../api/remove-from-cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showAlert(data.message || 'Gagal menghapus item', 'error');
            }
        });
    }
}

// Update quantity in cart
function updateCartQuantity(cartId, quantity) {
    if (quantity < 1) {
        removeFromCart(cartId);
        return;
    }
    
    const formData = new FormData();
    formData.append('cart_id', cartId);
    formData.append('quantity', quantity);
    
    fetch('../../api/update-cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showAlert(data.message || 'Gagal mengupdate keranjang', 'error');
        }
    });
}

// Format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}

// Generate order status badge
function getStatusBadge(status) {
    const statusMap = {
        'pending': { class: 'warning', text: 'Menunggu' },
        'confirmed': { class: 'info', text: 'Dikonfirmasi' },
        'preparing': { class: 'info', text: 'Sedang Disiapkan' },
        'ready': { class: 'success', text: 'Siap' },
        'completed': { class: 'success', text: 'Selesai' },
        'cancelled': { class: 'danger', text: 'Dibatalkan' }
    };
    
    const statusInfo = statusMap[status] || { class: 'secondary', text: status };
    return `<span class="badge badge-${statusInfo.class}">${statusInfo.text}</span>`;
}

// Validate form before submit
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = '#dc3545';
            isValid = false;
        } else {
            input.style.borderColor = '';
        }
    });
    
    return isValid;
}
