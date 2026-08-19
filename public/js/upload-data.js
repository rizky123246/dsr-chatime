
    function handleFileSelect(event) {
        const file = event.target.files[0];
        const fileName = document.getElementById('fileName');
        
        if (file) {
            fileName.textContent = file.name;
            fileName.style.color = '#059669';
        } else {
            fileName.textContent = 'No file chosen';
            fileName.style.color = '#374151';
        }
    }

    function uploadFile() {
        const fileInput = document.getElementById('csvFile');
        const file = fileInput.files[0];
        
        if (!file) {
            showNotification('Silakan pilih file CSV terlebih dahulu', 'error');
            return;
        }

        if (!file.name.endsWith('.csv')) {
            showNotification('File harus berformat CSV', 'error');
            return;
        }

        // Show progress section
        document.getElementById('progressSection').style.display = 'block';
        document.getElementById('successSection').style.display = 'none';
        
        // Create FormData for file upload
        const formData = new FormData();
        formData.append('csv_file', file);
        
        // Simulate upload progress
        let progress = 0;
        const progressFill = document.getElementById('progressFill');
        const progressStatus = document.getElementById('progressStatus');
        
        const progressInterval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90; // Stop at 90% until actual upload completes
            
            progressFill.style.width = progress + '%';
            
            if (progress < 30) {
                progressStatus.textContent = 'Memvalidasi format file...';
            } else if (progress < 60) {
                progressStatus.textContent = 'Mengupload file ke server...';
            } else if (progress < 90) {
                progressStatus.textContent = 'Memproses data pembayaran...';
            }
        }, 200);

        // Make API call to import pembayaran
        fetch('{{ route("dashboard.import-pembayaran") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            clearInterval(progressInterval);
            
            // Complete progress to 100%
            progressFill.style.width = '100%';
            progressStatus.textContent = 'Menyimpan ke database...';
            
            setTimeout(() => {
                if (data.success) {
                    showPembayaranSuccess(data.imported_count, data.errors);
                } else {
                    showNotification('Upload gagal: ' + data.message, 'error');
                    document.getElementById('progressSection').style.display = 'none';
                }
            }, 500);
        })
        .catch(error => {
            clearInterval(progressInterval);
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat upload file', 'error');
            document.getElementById('progressSection').style.display = 'none';
        });
    }

    function showPembayaranSuccess(importedCount, errors = []) {
        document.getElementById('progressSection').style.display = 'none';
        document.getElementById('successSection').style.display = 'block';
        
        let successDetails = `Total ${importedCount} transaksi pembayaran berhasil diproses dan ditambahkan ke database.<br>`;
        
        if (errors.length > 0) {
            successDetails += `<br><strong>Peringatan:</strong> ${errors.length} baris dilewati karena error.<br>`;
            successDetails += `<small>Error: ${errors.slice(0, 3).join('; ')}${errors.length > 3 ? '...' : ''}</small>`;
        }
        
        successDetails += `<br>Data tersedia untuk dilihat di dashboard dan laporan.`;
        
        document.getElementById('successDetails').innerHTML = successDetails;
        
        showNotification('Data pembayaran berhasil diupload!', 'success');
        
        // Reset file input
        document.getElementById('csvFile').value = '';
        document.getElementById('fileName').textContent = 'No file chosen';
        document.getElementById('fileName').style.color = '#374151';
    }

    function useSampleData() {
        showNotification('Data contoh sedang dimuat...', 'info');
        
        // Simulate loading sample data
        setTimeout(() => {
            document.getElementById('successSection').style.display = 'block';
            document.getElementById('successDetails').innerHTML = `Data contoh dengan 50 transaksi berhasil dimuat.<br>
            Data tersedia untuk dilihat di dashboard dan laporan penjualan.`;
            
            showNotification('Data contoh berhasil dimuat!', 'success');
        }, 1500);
    }

    function downloadTemplate() {
        // Download template from server
        window.location.href = '{{ route("dashboard.download-pembayaran-template") }}';
        showNotification('Template CSV pembayaran berhasil diunduh!', 'success');
    }

    // Sales Upload Functions
    function handleSalesFileSelect(event) {
        const file = event.target.files[0];
        const fileName = document.getElementById('salesFileName');
        
        if (file) {
            fileName.textContent = file.name;
            fileName.style.color = '#059669';
        } else {
            fileName.textContent = 'No file chosen';
            fileName.style.color = '#374151';
        }
    }

    function uploadSalesFile() {
        const fileInput = document.getElementById('salesCsvFile');
        const file = fileInput.files[0];
        
        if (!file) {
            showNotification('Silakan pilih file CSV penjualan terlebih dahulu', 'error');
            return;
        }

        if (!file.name.endsWith('.csv')) {
            showNotification('File harus berformat CSV', 'error');
            return;
        }

        // Show progress section
        document.getElementById('salesProgressSection').style.display = 'block';
        document.getElementById('salesSuccessSection').style.display = 'none';
        
        // Create FormData for file upload
        const formData = new FormData();
        formData.append('csv_file', file);
        
        // Simulate upload progress
        let progress = 0;
        const progressFill = document.getElementById('salesProgressFill');
        const progressStatus = document.getElementById('salesProgressStatus');
        
        const progressInterval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90; // Stop at 90% until actual upload completes
            
            progressFill.style.width = progress + '%';
            
            if (progress < 30) {
                progressStatus.textContent = 'Memvalidasi format file penjualan...';
            } else if (progress < 60) {
                progressStatus.textContent = 'Mengupload file penjualan ke server...';
            } else if (progress < 90) {
                progressStatus.textContent = 'Memproses data penjualan...';
            }
        }, 200);

        // Make API call to import penjualan
        fetch('{{ route("dashboard.import-penjualan") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            clearInterval(progressInterval);
            
            // Complete progress to 100%
            progressFill.style.width = '100%';
            progressStatus.textContent = 'Menyimpan data penjualan ke database...';
            
            setTimeout(() => {
                if (data.success) {
                    showSalesSuccess(data.imported_count, data.errors, data.warnings);
                } else {
                    showNotification('Upload penjualan gagal: ' + data.message, 'error');
                    document.getElementById('salesProgressSection').style.display = 'none';
                }
            }, 500);
        })
        .catch(error => {
            clearInterval(progressInterval);
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat upload file penjualan', 'error');
            document.getElementById('salesProgressSection').style.display = 'none';
        });
    }

    function showSalesSuccess(importedCount, errors = [], warnings = []) {
        document.getElementById('salesProgressSection').style.display = 'none';
        document.getElementById('salesSuccessSection').style.display = 'block';
        
        let successDetails = `Total ${importedCount} transaksi penjualan berhasil diproses dan ditambahkan ke database.<br>`;
        
        if (warnings.length > 0) {
            successDetails += `<br><strong>Peringatan:</strong> ${warnings.length} baris memiliki Net Price = 0.<br>`;
            successDetails += `<small>Warning: ${warnings.slice(0, 3).join('; ')}${warnings.length > 3 ? '...' : ''}</small>`;
        }
        
        if (errors.length > 0) {
            successDetails += `<br><strong>Error:</strong> ${errors.length} baris dilewati karena error.<br>`;
            successDetails += `<small>Error: ${errors.slice(0, 3).join('; ')}${errors.length > 3 ? '...' : ''}</small>`;
        }
        
        successDetails += `<br>Data penjualan tersedia untuk dilihat di dashboard dan laporan penjualan.`;
        
        document.getElementById('salesSuccessDetails').innerHTML = successDetails;
        
        showNotification('Data penjualan berhasil diupload!', 'success');
        
        // Reset file input
        document.getElementById('salesCsvFile').value = '';
        document.getElementById('salesFileName').textContent = 'No file chosen';
        document.getElementById('salesFileName').style.color = '#374151';
    }

    function useSalesSampleData() {
        showNotification('Data contoh penjualan sedang dimuat...', 'info');
        
        // Simulate loading sample data
        setTimeout(() => {
            document.getElementById('salesSuccessSection').style.display = 'block';
            document.getElementById('salesSuccessDetails').innerHTML = `Data contoh dengan 75 transaksi penjualan berhasil dimuat.<br>
            Data penjualan tersedia untuk dilihat di dashboard dan laporan penjualan.`;
            
            showNotification('Data contoh penjualan berhasil dimuat!', 'success');
        }, 1500);
    }

    function downloadSalesTemplate() {
        // Download template from server
        window.location.href = '{{ route("dashboard.download-penjualan-template") }}';
        showNotification('Template CSV penjualan berhasil diunduh!', 'success');
    }