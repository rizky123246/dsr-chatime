// Store Manager Dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Store Manager Dashboard loaded');

    // Initialize date display
    initializeDate();

    // Add click handlers for navigation
    const navBtns = document.querySelectorAll('.nav-btn');
    navBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            navBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Add hover effect for all bar elements
    const bars = document.querySelectorAll('.bar');
    bars.forEach(bar => {
        bar.addEventListener('mouseenter', function() {
            this.style.opacity = '0.8';
        });
        bar.addEventListener('mouseleave', function() {
            this.style.opacity = '1';
        });
    });
});

// ─── Date Functions ───────────────────────────────────────────────────────────

function initializeDate() {
    console.log('Date range picker initialized');
}

function updateDateRange() {
    const startDate = document.getElementById('startDate').value;
    const endDate   = document.getElementById('endDate').value;

    if (startDate && endDate && startDate <= endDate) {
        const options = { day: 'numeric', month: 'long', year: 'numeric' };
        const startObj = new Date(startDate);
        const endObj   = new Date(endDate);

        document.getElementById('dateRangeDisplay').textContent =
            `${startObj.toLocaleDateString('id-ID', options)} - ${endObj.toLocaleDateString('id-ID', options)}`;

        checkDataExists(startDate, endDate);
    } else {
        alert('Tanggal tidak valid. Pastikan tanggal mulai <= tanggal selesai.');
    }
}

async function checkDataExists(startDate, endDate) {
    try {
        showNotification('Memeriksa ketersediaan data...', 'info');

        const response = await fetch(`/dashboard/store-manager/check-data?start_date=${startDate}&end_date=${endDate}`);
        const data     = await response.json();

        if (data.success) {
            if (data.hasData) {
                window.location.href = `/dashboard/store-manager?start_date=${startDate}&end_date=${endDate}`;
            } else {
                showNotification(
                    `Tidak ada data untuk periode ${startDate} - ${endDate}. Silakan pilih tanggal lain.`,
                    'warning'
                );
                resetToDefaultDateRange();
            }
        } else {
            showNotification('Gagal memeriksa ketersediaan data', 'error');
        }
    } catch (error) {
        console.error('Error checking data existence:', error);
        showNotification('Gagal memeriksa ketersediaan data', 'error');
    }
}

function resetToDefaultDateRange() {
    const today        = new Date();
    const thirtyDaysAgo = new Date(today);
    thirtyDaysAgo.setDate(today.getDate() - 30);

    const startDate = thirtyDaysAgo.toISOString().split('T')[0];
    const endDate   = today.toISOString().split('T')[0];

    document.getElementById('startDate').value = startDate;
    document.getElementById('endDate').value   = endDate;

    const options  = { day: 'numeric', month: 'long', year: 'numeric' };
    const startObj = new Date(startDate);
    const endObj   = new Date(endDate);

    document.getElementById('dateRangeDisplay').textContent =
        `${startObj.toLocaleDateString('id-ID', options)} - ${endObj.toLocaleDateString('id-ID', options)}`;

    window.location.href = `/dashboard/store-manager?start_date=${startDate}&end_date=${endDate}`;
}

// ─── Notification ─────────────────────────────────────────────────────────────

function showNotification(message, type = 'info') {
    // Remove existing notifications
    document.querySelectorAll('.notification').forEach(n => n.remove());

    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;

    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 9999;
        animation: slideIn 0.3s ease-out;
        max-width: 300px;
        word-wrap: break-word;
    `;

    const colors = {
        success: '#10b981',
        error:   '#ef4444',
        warning: '#f59e0b',
        info:    '#3b82f6',
    };
    notification.style.backgroundColor = colors[type] || colors.info;

    document.body.appendChild(notification);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => {
            if (notification.parentNode) notification.remove();
        }, 300);
    }, 5000);

    // Inject animation keyframes once
    if (!document.querySelector('#notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to   { transform: translateX(0);    opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0);    opacity: 1; }
                to   { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
}

// ─── Snack Search ─────────────────────────────────────────────────────────────

// BUG FIX: dipindahkan ke scope global (sebelumnya ada di dalam showNotification)
let snackSearchTimeout = null;

function searchSnack(keyword) {
    clearTimeout(snackSearchTimeout);
    const resultsBox = document.getElementById('snackSearchResults');

    if (keyword.trim() === '') {
        resultsBox.innerHTML = '';
        return;
    }

    snackSearchTimeout = setTimeout(() => {
        const params = new URLSearchParams({
            q:          keyword,
            start_date: document.getElementById('startDate').value,
            end_date:   document.getElementById('endDate').value,
        });

        fetch(`/dashboard/store-manager/search-snack?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                if (data.length === 0) {
                    resultsBox.innerHTML = '<p class="no-result">Tidak ada produk ditemukan.</p>';
                    return;
                }

                let html = '<table><thead><tr><th>Produk</th><th>Qty</th><th>Sales</th></tr></thead><tbody>';
                data.forEach(item => {
                    html += `<tr>
                        <td>${item.article_name}</td>
                        <td>${Number(item.qty).toLocaleString('id-ID')}</td>
                        <td>Rp ${Number(item.sales).toLocaleString('id-ID')}</td>
                    </tr>`;
                });
                html += '</tbody></table>';
                resultsBox.innerHTML = html;
            })
            .catch(() => {
                resultsBox.innerHTML = '<p class="no-result">Gagal memuat data, coba lagi.</p>';
            });
    }, 300);
}