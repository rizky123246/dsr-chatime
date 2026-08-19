// ==========================================
// KASIR DASHBOARD JS FINAL
// ==========================================

document.addEventListener('DOMContentLoaded', function () {

    initializeDate();
    initializeTopMenu();
    initializeSnackChart();

    console.log('Kasir Dashboard Ready');
});

// ==========================================
// INITIALIZE
// ==========================================

function initializeDate() {

    console.log('Date range initialized');
}

function initializeTopMenu() {

    updateSelectedMenus();
}

// ==========================================
// NOTIFICATION
// ==========================================

function showNotification(message, type = 'info') {

    const old = document.querySelector('.notification');

    if (old) old.remove();

    const notification = document.createElement('div');

    notification.className = `notification notification-${type}`;

    notification.innerHTML = message;

    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 99999;
        padding: 14px 18px;
        border-radius: 12px;
        color: white;
        font-weight: 600;
        box-shadow: 0 10px 30px rgba(0,0,0,.12);
        animation: fadeIn .2s ease;
    `;

    if (type === 'success') {
        notification.style.background = '#10b981';
    } else if (type === 'error') {
        notification.style.background = '#ef4444';
    } else if (type === 'warning') {
        notification.style.background = '#f59e0b';
    } else {
        notification.style.background = '#3b82f6';
    }

    document.body.appendChild(notification);

    setTimeout(() => {

        notification.remove();

    }, 3000);
}

// ==========================================
// DATE FILTER
// ==========================================

function updateDateRange() {

    const startDate = document.getElementById('startDate')?.value;
    const endDate = document.getElementById('endDate')?.value;

    if (!startDate || !endDate) {

        showNotification('Tanggal belum lengkap', 'warning');
        return;
    }

    if (startDate > endDate) {

        showNotification('Tanggal tidak valid', 'error');
        return;
    }

    window.location.href =
        `/dashboard/kasir?start_date=${startDate}&end_date=${endDate}`;
}

// ==========================================
// TOP MENU
// ==========================================

function toggleMenuDropdown(event) {

    event.stopPropagation();

    const dropdown =
        document.getElementById('menuDropdown');

    dropdown.classList.toggle('show');
}

/* CLOSE ketika klik luar */
document.addEventListener('click', function(e){

    const dropdown =
        document.getElementById('menuDropdown');

    const selectBox =
        document.querySelector('.menu-select-container');

    if (!selectBox.contains(e.target)) {

        dropdown.classList.remove('show');
    }
});

function selectAllMenus() {

    document
        .querySelectorAll('.menu-checkbox')
        .forEach(cb => cb.checked = true);

    updateSelectedMenus();
}

function deselectAllMenus() {

    document
        .querySelectorAll('.menu-checkbox')
        .forEach(cb => cb.checked = false);

    updateSelectedMenus();
}

function removeMenu(menuName) {

    const checkbox =
        document.querySelector(
            `.menu-checkbox[value="${menuName}"]`
        );

    if (checkbox) {

        checkbox.checked = false;

        updateSelectedMenus();
    }
}

// ==========================================
// UPDATE SELECTED MENUS
// ==========================================

function updateSelectedMenus() {

    const selected =
        document.querySelectorAll('.menu-checkbox:checked');

    const selectedTags =
        document.getElementById('selectedTags');

    const selectedMenuCount =
        document.getElementById('selectedMenuCount');

    const totalSoldCups =
        document.getElementById('totalSoldCups');

    if (!selectedTags) return;

    selectedTags.innerHTML = '';

    let totalQty = 0;

    let selectedProducts = [];

    selected.forEach(cb => {

        const quantity =
            parseInt(cb.dataset.quantity || 0);

        const sales =
            parseInt(cb.dataset.sales || 0);

        const type =
            cb.dataset.type || '-';

        const series =
            cb.dataset.series || '-';

        totalQty += quantity;

        selectedProducts.push({

            name: cb.value,
            quantity,
            sales,
            type,
            series
        });

        const tag = document.createElement('span');

        tag.className = 'tag selected-tag';

        tag.innerHTML = `
            ${cb.value}
            <span
                class="remove-tag"
                onclick="removeMenu('${cb.value}')"
            >
                ×
            </span>
        `;

        selectedTags.appendChild(tag);
    });

    if (selectedMenuCount) {

        selectedMenuCount.innerText =
            `${selected.length} menu dipilih`;
    }

    if (totalSoldCups) {

        totalSoldCups.innerText =
            `${totalQty} cups`;
    }

    updateMenuStatistics(selectedProducts);

    updateComparisonChart(selectedProducts);
}

// ==========================================
// MENU STATISTICS
// ==========================================

function updateMenuStatistics(products) {

    const container =
        document.getElementById('menuStatsContainer');

    if (!container) return;

    container.innerHTML = '';

    if (products.length === 0) {

        container.innerHTML = `
            <div class="empty-state">
                Pilih menu untuk melihat statistik
            </div>
        `;

        return;
    }

    const dashboardTotalSales = Number(
        document.querySelector('.dashboard-content')
            ?.dataset
            ?.totalSales || 0
    );
    
    products.forEach(product => {
    
        const achievement =
            dashboardTotalSales > 0
                ? ((product.sales / dashboardTotalSales) * 100).toFixed(1)
                : 0;
    
        const card = document.createElement('div');

        card.className = 'menu-box';

        card.innerHTML = `

            <div class="menu-content">

                <div class="menu-top">

                    <h3 class="menu-name">
                        ${product.name}
                    </h3>

                    <span class="menu-type">
                        ${product.type}
                    </span>

                </div>

                <div class="menu-series">
                    ${product.series}
                </div>

                <div class="stats-grid">

                    <div class="stat-item">
                        <span class="stat-label">
                            Qty
                        </span>

                        <span class="stat-value text-green">
                            ${product.quantity}
                        </span>
                    </div>

                    <div class="stat-item">
                        <span class="stat-label">
                            Sales
                        </span>

                        <span class="stat-value text-blue">
                            Rp ${formatNumber(product.sales)}
                        </span>
                    </div>

                    <div class="stat-item">
                        <span class="stat-label">
                            Achievement
                        </span>

                        <span class="stat-value text-orange">
                            ${achievement}%
                        </span>
                    </div>

                </div>

                <div class="progress-bar-bg">

                    <div
                        class="progress-fill"
                        style="
                            width:${Math.min(achievement,100)}%;
                        "
                    ></div>

                </div>

            </div>
        `;

        container.appendChild(card);
    });
}

// ==========================================
// TOP MENU CHART
// ==========================================

function updateComparisonChart(products) {

    const chart =
        document.getElementById('comparisonChart');

    if (!chart) return;

    chart.innerHTML = '';

    if (products.length === 0) {

        chart.innerHTML = `
            <div class="empty-chart">
                Pilih menu untuk melihat grafik
            </div>
        `;

        return;
    }

    const maxQty =
        Math.max(...products.map(p => p.quantity), 1);

    products.forEach((product, index) => {

        const height =
            (product.quantity / maxQty) * 85;

        const position =
            (index / Math.max(products.length - 1, 1)) * 90;

        const barGroup =
            document.createElement('div');

        barGroup.className = 'bar-group';

        barGroup.style.left = `${position}%`;

        barGroup.innerHTML = `

            <div
                class="bar sold"
                style="height:${height}%"
                title="${product.name}"
            ></div>

            <div class="x-axis-label">
                ${truncate(product.name, 10)}
            </div>
        `;

        chart.appendChild(barGroup);
    });
}

// ==========================================
// SNACK
// ==========================================

function initializeSnackChart() {

    updateSnackChart();
}

function toggleSnackDropdown() {

    const dropdown =
        document.getElementById('snackDropdown');

    dropdown.classList.toggle('show');
}

function filterSnackCheckboxes() {

    const keyword =
        document.getElementById('snackSearch')
            .value
            .toLowerCase();

    const items =
        document.querySelectorAll('#snackCheckboxes .menu-item');

    items.forEach(item => {

        item.style.display =
            item.innerText.toLowerCase().includes(keyword)
                ? 'flex'
                : 'none';
    });
}

function selectAllSnacks() {

    document
        .querySelectorAll('.snack-checkbox')
        .forEach(cb => cb.checked = true);

    updateSelectedSnacks();
}

function deselectAllSnacks() {

    document
        .querySelectorAll('.snack-checkbox')
        .forEach(cb => cb.checked = false);

    updateSelectedSnacks();
}

function updateSelectedSnacks() {

    const count =
        document.querySelectorAll('.snack-checkbox:checked')
            .length;

    const label =
        document.getElementById('selectedSnackCount');

    if (label) {

        label.innerText =
            `${count} snack dipilih`;
    }

    updateSnackChart();
}

// ==========================================
// SNACK CHART
// ==========================================

function updateSnackChart() {

    const selected =
        Array.from(
            document.querySelectorAll('.snack-checkbox:checked')
        );

    const container =
        document.querySelector('.snack-chart-container .plot-area');

    if (!container) return;

    container.innerHTML = '';

    if (selected.length === 0) {

        container.innerHTML = `
            <div class="empty-chart">
                Pilih snack untuk melihat grafik
            </div>
        `;

        return;
    }

    const snacks = selected.map(cb => ({

        name: cb.value,
        qty: parseInt(cb.dataset.quantity || 0),
        sales: parseInt(cb.dataset.revenue || 0)
    }));

    const maxQty =
        Math.max(...snacks.map(s => s.qty), 1);

    snacks.forEach((snack, index) => {

        const height =
            (snack.qty / maxQty) * 85;

        const position =
            (index / Math.max(snacks.length - 1, 1)) * 90;

        const group =
            document.createElement('div');

        group.className = 'bar-group';

        group.style.left = `${position}%`;

        group.innerHTML = `

            <div
                class="snack-bar orange"
                style="height:${height}%"
            ></div>

            <div class="x-label">
                ${truncate(snack.name, 10)}
            </div>
        `;

        container.appendChild(group);
    });

    updateSnackSummary(snacks);
}

// ==========================================
// SNACK SUMMARY
// ==========================================

function updateSnackSummary(snacks) {

    const container =
        document.querySelector('.snack-summary-section');

    if (!container) return;

    if (snacks.length === 0) {

        container.innerHTML = `
            <div class="empty-state">
                Pilih snack terlebih dahulu
            </div>
        `;

        return;
    }

    const totalQty =
        snacks.reduce((a, b) => a + b.qty, 0);

    const totalSales =
        snacks.reduce((a, b) => a + b.sales, 0);

    container.innerHTML = `

        <div class="summary-panel">

            <div class="metric">
                <span>Total Qty</span>
                <strong>${formatNumber(totalQty)}</strong>
            </div>

            <div class="metric">
                <span>Total Sales</span>
                <strong>
                    Rp ${formatNumber(totalSales)}
                </strong>
            </div>

        </div>
    `;
}

// ==========================================
// HELPERS
// ==========================================

function formatNumber(num) {

    return Number(num)
        .toLocaleString('id-ID');
}

function truncate(text, max) {

    if (!text) return '';

    return text.length > max
        ? text.substring(0, max) + '...'
        : text;
}

// ==========================================
// AJAX MENU SEARCH
// ==========================================

let searchTimeout;

/*
|--------------------------------------------------------------------------
| SEARCH MENU
|--------------------------------------------------------------------------
*/
function searchMenus(keyword) {

    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {

        const startDate =
            document.querySelector('[name="start_date"]')?.value;

        const endDate =
            document.querySelector('[name="end_date"]')?.value;

        fetch(
            `/dashboard/kasir/search-top-menu?search=${encodeURIComponent(keyword)}&start_date=${startDate}&end_date=${endDate}`
        )

        .then(response => response.json())

        .then(products => {

            renderMenuResults(products);

        })

        .catch(error => {

            console.error(error);

            showNotification(
                'Gagal mengambil data menu',
                'error'
            );
        });

    }, 300);
}

/*
|--------------------------------------------------------------------------
| RENDER MENU RESULTS
|--------------------------------------------------------------------------
*/
function renderMenuResults(products) {

    const container =
        document.getElementById('menuCheckboxes');

    if (!container) return;

    /*
    |--------------------------------------------------------------------------
    | SIMPAN YANG SUDAH DICENTANG
    |--------------------------------------------------------------------------
    */
    const checkedMenus = Array.from(

        document.querySelectorAll('.menu-checkbox:checked')

    ).map(cb => cb.value);

    container.innerHTML = '';

    /*
    |--------------------------------------------------------------------------
    | EMPTY STATE
    |--------------------------------------------------------------------------
    */
    if (!products || products.length === 0) {

        container.innerHTML = `

            <div class="empty-state">
                Menu tidak ditemukan
            </div>
        `;

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | RENDER ITEMS
    |--------------------------------------------------------------------------
    */
    products.forEach(product => {

        const isChecked =
            checkedMenus.includes(product.name);

        const item = document.createElement('div');

        item.className = 'menu-item';

        item.innerHTML = `

            <label>

                <input
                    type="checkbox"
                    class="menu-checkbox"

                    value="${product.name}"

                    data-quantity="${product.total_quantity || 0}"
                    data-sales="${product.total_sales || 0}"
                    data-type="${product.type || '-'}"
                    data-series="${product.series || '-'}"
                    data-article-code="${product.article_code || ''}"

                    ${isChecked ? 'checked' : ''}

                    onchange="updateSelectedMenus()"
                >

                <span class="menu-name">
                    ${product.name}
                </span>

                <span class="menu-qty">
                    (${formatNumber(product.total_quantity || 0)})
                </span>

            </label>
        `;

        container.appendChild(item);
    });

    /*
    |--------------------------------------------------------------------------
    | REFRESH UI
    |--------------------------------------------------------------------------
    */
    updateSelectedMenus();
}