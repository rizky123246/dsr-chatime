
    // Fungsi helper untuk format angka
    function formatNumber(num) {
        return Math.round(num).toLocaleString('id-ID');
    }

    // Fungsi toggle filter
    let currentFilter = 'all';
    
    function toggleFilter() {
        const filterBtn = event.target.closest('.filter-btn');
        const statuses = ['all', 'verified', 'void'];
        const currentIndex = statuses.indexOf(currentFilter);
        currentFilter = statuses[(currentIndex + 1) % statuses.length];
        
        const filterText = currentFilter === 'all' ? 'Semua Status' : 
                          currentFilter === 'verified' ? 'Verified Only' : 'Has Void Only';
        
        filterBtn.innerHTML = `<span>▽</span> ${filterText} <span>▼</span>`;
        
        filterTable(currentFilter);
    }

    function filterTable(status) {
        const rows = document.querySelectorAll('#reportsTableBody tr');
        
        rows.forEach(row => {
            if (status === 'all') {
                row.style.display = '';
            } else {
                const badge = row.querySelector('.badge');
                const rowStatus = badge ? badge.textContent.toLowerCase() : '';
                
                if (status === 'verified' && rowStatus === 'verified') {
                    row.style.display = '';
                } else if (status === 'void' && rowStatus === 'has void') {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    // View report functionality
    function viewReport(date) {
        showNotification(`Melihat detail laporan untuk tanggal ${date}`, 'info');
        
        // Simulate opening report details
        setTimeout(() => {
            showNotification('Detail laporan akan segera tersedia', 'info');
        }, 1000);
    }

    // Add hover effect for table rows
    document.addEventListener("DOMContentLoaded", function () {

        const rows = document.querySelectorAll("#reportsTableBody tr");
    
        // 🔥 CLICK ROW
        rows.forEach(row => {
            row.addEventListener("click", function () {
                const date = this.getAttribute("data-date");
    
                if (date) {
                    window.location.href = `/laporan/${date}`;
                }
            });
    
            // 🔥 HOVER
            row.addEventListener('mouseenter', function () {
                this.style.backgroundColor = '#f9fafb';
            });
    
            row.addEventListener('mouseleave', function () {
                this.style.backgroundColor = '';
            });
        });
    
        // 🔥 TOGGLE BUTTON
        const toggleBtns = document.querySelectorAll('.toggle-btn');
    
        toggleBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                toggleBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    
    });

    function toggleDateFilter() {
        const bar = document.getElementById('dateFilterBar');
        const icon = document.getElementById('filterToggleIcon');
    
        const isHidden = bar.style.display === 'none';
    
        bar.style.display = isHidden ? 'flex' : 'none';
        icon.textContent = isHidden ? '🔼' : '🔽';
    }
    
    // Auto-tampilkan panel kalau filter sedang aktif (habis submit form)
    document.addEventListener('DOMContentLoaded', function () {
        const params = new URLSearchParams(window.location.search);
        const hasActiveFilter = params.has('tanggal') || params.has('bulan') || params.has('tahun');
    
        if (hasActiveFilter) {
            const bar = document.getElementById('dateFilterBar');
            const icon = document.getElementById('filterToggleIcon');
            bar.style.display = 'flex';
            icon.textContent = '🔼';
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const params = new URLSearchParams(window.location.search);
        const hasActiveFilter = params.has('tanggal') || params.has('bulan') || params.has('tahun') || params.has('status');
    
        if (hasActiveFilter) {
            const bar = document.getElementById('dateFilterBar');
            const icon = document.getElementById('filterToggleIcon');
            bar.style.display = 'flex';
            icon.textContent = '🔼';
        }
    });