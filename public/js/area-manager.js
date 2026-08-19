// --- DATE FILTER FUNCTION ---
function updateDateFilter() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const storeCode = document.getElementById('store').value;

    // Build URL with date and store parameters
    let url = '/dashboard/area-manager';
    const params = new URLSearchParams();

    if (startDate) {
        params.set('start_date', startDate);
    }
    if (endDate) {
        params.set('end_date', endDate);
    }
    if (storeCode && storeCode !== 'all') {
        params.set('store', storeCode);
    }

    if (params.toString()) {
        url += '?' + params.toString();
    }

    window.location.href = url;
}

// --- STORE FILTER FUNCTION ---
function updateStoreFilter() {
    updateDateFilter(); // Use the unified date filter function
}

// --- TOP MENU SECTION SCRIPT ---
function toggleMenuDropdown() {
    const dropdown = document.getElementById('menuDropdown');
    dropdown.classList.toggle('show');

    function closeDropdown(e) {
        if (!e.target.closest('.tm-select-box')) {
            dropdown.classList.remove('show');
            document.removeEventListener('click', closeDropdown);
        }
    }
    document.addEventListener('click', closeDropdown);
}

function filterMenuCheckboxes() {
    const searchTerm = document.getElementById('menuSearch').value.toLowerCase();
    const menuItems = document.querySelectorAll('.tm-menu-item');

    menuItems.forEach(item => {
        const label = item.querySelector('label').textContent.toLowerCase();
        if (label.includes(searchTerm)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function selectAllMenus() {
    const checkboxes = document.querySelectorAll('.menu-checkbox');
    checkboxes.forEach(cb => {
        if (cb.closest('.tm-menu-item').style.display !== 'none') {
            cb.checked = true;
        }
    });
    updateSelectedMenus();
}

function deselectAllMenus() {
    const checkboxes = document.querySelectorAll('.menu-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    updateSelectedMenus();
}

function updateSelectedMenus() {
    const checkboxes = document.querySelectorAll('.menu-checkbox:checked');
    const selectedTags = document.getElementById('selectedTags');
    const totalSoldCups = document.getElementById('totalSoldCups');
    const selectedMenuCount = document.getElementById('selectedMenuCount');
    const menuStatsContainer = document.getElementById('menuStatsContainer');

    selectedTags.innerHTML = '';
    menuStatsContainer.innerHTML = '';

    let totalQuantity = 0;
    let selectedMenus = [];
    let selectedProducts = [];

    selectedMenuCount.textContent = `${checkboxes.length} menu dipilih`;

    checkboxes.forEach(checkbox => {
        const menuName = checkbox.value;
        const quantity = parseInt(checkbox.getAttribute('data-quantity') || 0);

        totalQuantity += quantity;
        selectedMenus.push(menuName);
        selectedProducts.push({ name: menuName, quantity: quantity });

        const tag = document.createElement('span');
        tag.className = 'tag';
        tag.innerHTML = `${menuName} <span class="remove-tag" onclick="removeMenu('${menuName}')">×</span>`;
        selectedTags.appendChild(tag);
    });

    totalSoldCups.textContent = `${totalQuantity} cups`;

    // Update menu statistics cards (like Kasir)
    updateMenuStatistics(selectedProducts, totalQuantity);

    renderComparisonChart(selectedProducts);
}

function updateMenuStatistics(selectedProducts, totalCups) {
    const container = document.getElementById('menuStatsContainer');
    container.innerHTML = '';

    selectedProducts.forEach((product, index) => {
        const soldCups = product.quantity;
        const targetCups = 100;
        const vsTarget = targetCups > 0 ? ((soldCups / targetCups) * 100).toFixed(1) : 0;
        const vsTotal = totalCups > 0 ? ((soldCups / totalCups) * 100).toFixed(1) : 0;

        const menuBox = document.createElement('div');
        menuBox.className = 'menu-box';
        menuBox.setAttribute('data-menu', product.name);

        menuBox.innerHTML = `
            <div class="menu-content">
                <h3 class="menu-name">${product.name.length > 30 ? product.name.substring(0, 30) + '...' : product.name}</h3>

                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-label">Sold Cups</span>
                        <span class="stat-value text-green">${soldCups}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Target</span>
                        <span class="stat-value">${targetCups}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">vs Target</span>
                        <span class="stat-value text-orange">${vsTarget}%</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">vs Total Cups</span>
                        <span class="stat-value text-blue">${vsTotal}%</span>
                    </div>
                </div>

                <div class="progress-group">
                    <div class="progress-bar-bg">
                        <div class="progress-fill" style="width: ${vsTotal}%;"></div>
                    </div>
                    <p class="progress-caption">${soldCups} dari ${totalCups} total cups (${vsTotal}%)</p>
                </div>
            </div>
        `;

        container.appendChild(menuBox);
    });
}

function removeMenu(menuName) {
    const checkboxes = document.querySelectorAll('.menu-checkbox');
    checkboxes.forEach(cb => {
        if (cb.value === menuName) {
            cb.checked = false;
        }
    });
    updateSelectedMenus();
}

function renderComparisonChart(data) {
    const chartContainer = document.getElementById('comparisonChart');

    if (data.length === 0) {
        chartContainer.innerHTML = `
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: #999;">
                <p>Silakan pilih menu untuk melihat grafik</p>
            </div>`;
        return;
    }

    chartContainer.innerHTML = '';

    const targetValue = 100;
    const maxChartValue = 500;
    const barWidthPercent = 8;
    const gapPercent = 4;

    const totalGroupWidth = data.length * (barWidthPercent + gapPercent);
    const startLeft = Math.max(0, (100 - totalGroupWidth) / 2);

    data.forEach((item, index) => {
        const soldHeight = Math.min((item.quantity / maxChartValue) * 100, 100);
        const targetHeight = (targetValue / maxChartValue) * 100;

        const leftPos = startLeft + (index * (barWidthPercent + gapPercent));

        const barHTML = `
            <div class="tm-bar-group" style="left: ${leftPos}%; width: ${barWidthPercent}%;">
                <div class="tm-bar sold" style="height: ${soldHeight}%;" title="Sold: ${item.quantity}"></div>
                <div class="tm-bar target" style="height: ${targetHeight}%;" title="Target: ${targetValue}"></div>
                <div class="tm-x-axis-label">${item.name.substring(0, 10)}...</div>
            </div>
        `;

        chartContainer.insertAdjacentHTML('beforeend', barHTML);
    });
}

// ─── Top Promotion Search (AJAX) ───────────────────────────────────────────

let promotionSearchTimeout = null;

function searchPromotion(keyword) {
    clearTimeout(promotionSearchTimeout);
    const resultsBox = document.getElementById('promotionSearchResults');

    if (keyword.trim() === '') {
        resultsBox.innerHTML = '';
        return;
    }

    promotionSearchTimeout = setTimeout(() => {
        // Ambil filter tanggal & store yang sedang aktif dari form/URL
        const params = new URLSearchParams({
            q: keyword,
            start_date: document.querySelector('input[name="start_date"]')?.value || '',
            end_date: document.querySelector('input[name="end_date"]')?.value || '',
            store: document.querySelector('select[name="store"]')?.value || 'all',
        });

        fetch(`/dashboard/area-manager/search-promotion?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    resultsBox.innerHTML = '<p class="no-result">Gagal memuat data, coba lagi.</p>';
                    return;
                }

                if (data.length === 0) {
                    resultsBox.innerHTML = '<p class="no-result">Tidak ada promo ditemukan.</p>';
                    return;
                }

                let html = `
                    <table class="promotion-table">
                        <thead>
                            <tr>
                                <th>Nama Promo</th>
                                <th>Kode</th>
                                <th style="text-align: right;">Dipakai</th>
                                <th style="text-align: right;">Qty</th>
                                <th style="text-align: right;">Diskon</th>
                                <th style="text-align: right;">Net Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                data.forEach(item => {
                    html += `
                        <tr>
                            <td>${item.promotion_name}</td>
                            <td>${item.promotion_code ?? '-'}</td>
                            <td style="text-align: right;">${Number(item.usage_count).toLocaleString('id-ID')}x</td>
                            <td style="text-align: right;">${Number(item.total_quantity).toLocaleString('id-ID')}</td>
                            <td style="text-align: right;">Rp ${Number(item.total_discount).toLocaleString('id-ID')}</td>
                            <td style="text-align: right;">Rp ${Number(item.total_sales).toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                });

                html += '</tbody></table>';
                resultsBox.innerHTML = html;
            })
            .catch(() => {
                resultsBox.innerHTML = '<p class="no-result">Gagal memuat data, coba lagi.</p>';
            });
    }, 300);
}

// ─── Section Visibility Toggle (persist via localStorage) ─────────────────

const SECTION_TOGGLE_STORAGE_KEY = 'areaManagerDashboard_sectionVisibility';

function getStoredSectionVisibility() {
    try {
        const raw = localStorage.getItem(SECTION_TOGGLE_STORAGE_KEY);
        return raw ? JSON.parse(raw) : {};
    } catch (e) {
        return {};
    }
}

function saveStoredSectionVisibility(state) {
    try {
        localStorage.setItem(SECTION_TOGGLE_STORAGE_KEY, JSON.stringify(state));
    } catch (e) {
        // localStorage unavailable (private mode dll), abaikan secara senyap
    }
}

function applySectionVisibility() {
    const stored = getStoredSectionVisibility();
    const checkboxes = document.querySelectorAll('.section-toggle-checkbox');

    checkboxes.forEach(checkbox => {
        const sectionId = checkbox.dataset.section;
        const isVisible = stored.hasOwnProperty(sectionId) ? stored[sectionId] : true;

        checkbox.checked = isVisible;

        const sectionEl = document.getElementById(sectionId);
        if (sectionEl) {
            sectionEl.style.display = isVisible ? '' : 'none';
        }
    });
}

function initSectionToggles() {
    const checkboxes = document.querySelectorAll('.section-toggle-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const sectionId = this.dataset.section;
            const sectionEl = document.getElementById(sectionId);

            if (sectionEl) {
                sectionEl.style.display = this.checked ? '' : 'none';
            }

            const stored = getStoredSectionVisibility();
            stored[sectionId] = this.checked;
            saveStoredSectionVisibility(stored);
        });
    });

    applySectionVisibility();
}

function toggleSectionPanel() {
    const body = document.getElementById('togglePanelBody');
    const arrow = document.getElementById('togglePanelArrow');

    const isCollapsed = body.style.display === 'none';
    body.style.display = isCollapsed ? 'flex' : 'none';
    arrow.textContent = isCollapsed ? '▾' : '▸';
}

// ─── Initialize on page load ───────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {
    console.log('Area Manager Dashboard loaded');

    initSectionToggles();

    // Add hover & click effects for stat cards
    const cards = document.querySelectorAll('.stat-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-4px)';
            this.style.boxShadow = '0 8px 16px rgba(0, 0, 0, 0.1)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.05)';
        });

        card.addEventListener('click', function () {
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = 'translateY(0)';
            }, 150);
        });
    });
});