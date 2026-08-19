<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Chatime DSR System') - Daily Sales Report</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Navbar Styles */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #ffffff;
            padding: 15px 30px;
            border-bottom: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Bagian Kiri (Logo dan Judul) */
        .brand-section {
            display: flex;
            align-items: center;
        }

        .logo-circle {
            width: 45px;
            height: 45px;
            background-color: #ff6b00; /* Warna Oranye Chatime */
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 15px;
            box-shadow: 0 2px 4px rgba(255, 107, 0, 0.3);
        }

        /* Ikon Cangkir Putih Sederhana */
        .logo-circle svg {
            fill: white;
            width: 24px;
            height: 24px;
        }

        .brand-text h1 {
            margin: 0;
            font-size: 20px;
            color: #000;
            font-weight: 700;
        }

        .brand-text p {
            margin: 0;
            font-size: 13px;
            color: #7f8c8d;
        }

        /* Bagian Kanan (Info User dan Tombol) */
        .user-section {
            display: flex;
            align-items: center;
        }

        .user-info {
            text-align: right;
            margin-right: 20px;
        }

        .user-info .name {
            display: block;
            font-weight: bold;
            font-size: 16px;
            color: #000;
        }

        .user-info .location {
            display: block;
            font-size: 13px;
            color: #7f8c8d;
        }

        /* Tombol Logout */
        .logout-btn {
            display: flex;
            align-items: center;
            padding: 8px 16px;
            border: 1px solid #dcdde1;
            border-radius: 8px;
            background-color: white;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            color: #000;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .logout-btn:hover {
            background-color: #f5f5f5;
            border-color: #ff6b00;
            color: #ff6b00;
        }

        .logout-btn svg {
            margin-right: 8px;
            width: 18px;
            height: 18px;
        }

        /* Role Badge Styles */
        .role-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 8px;
        }

        .role-store-manager {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .role-area-manager {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }

        .role-kasir {
            background-color: #fff3e0;
            color: #f57c00;
        }

        /* Footer Styles */
        .report-footer {
            background-color: transparent;
            padding: 40px 20px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            margin-top: auto;
        }

        .copyright-text {
            color: #6b7280;
            font-size: 14px;
            margin: 0 0 8px 0;
            font-weight: 400;
        }

        .system-text {
            color: #374151;
            font-size: 16px;
            font-weight: 500;
            margin: 0;
            letter-spacing: 0.2px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-container {
                padding: 10px 15px;
                flex-direction: column;
                align-items: flex-start;
            }

            .user-section {
                margin-top: 10px;
                width: 100%;
                justify-content: space-between;
            }

            .brand-text h1 {
                font-size: 18px;
            }

            .brand-text p {
                font-size: 12px;
            }

            .user-info {
                margin-right: 15px;
            }

            .user-info .name {
                font-size: 14px;
            }

            .user-info .location {
                font-size: 12px;
            }

            .copyright-text { 
                font-size: 12px; 
            }
            
            .system-text { 
                font-size: 14px; 
            }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Header/Navbar -->
    <header class="header-container">
        <div class="brand-section">
            <div class="logo-circle">
                <svg viewBox="0 0 24 24">
                    <path d="M2 21h18v-2H2M20 8h-2V5h2m0-2H4v10a4 4 0 0 0 4 4h6a4 4 0 0 0 4-4v-3h2a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/>
                </svg>
            </div>
            <div class="brand-text">
                <h1>Chatime DSR System</h1>
                <p>Daily Sales Report Management</p>
            </div>
        </div>

        <div class="user-section">
            <div class="user-info">
                <span class="name">
                    {{ session('user.name', 'User') }}
                    @if(session('user.role'))
                        <span class="role-badge role-{{ session('user.role') }}">
                            {{ session('user.role_display_name') }}
                        </span>
                    @endif
                </span>
                <span class="location">
                    {{ session('user.role_display_name', 'User') }} - 
                    @if(in_array(session('user.role'), ['store_manager', 'kasir']))
                {{-- FIX: pakai store user sendiri --}}
                {{ session('user.store_name') ?? session('user.site_code') ?? 'Store Tidak Diketahui' }}

            @elseif(session('user.role') == 'area_manager')
                {{-- Area Manager bebas --}}
                {{ session('selected_store_name') 
                    ?? request('store') 
                    ?? 'Semua Store' }}

            @else
                Semua Store
            @endif

                </span>
            </div>

            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="logout-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container py-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="report-footer">
        <p class="copyright-text">
            © {{ date('Y') }} PT. Foods and Beverages Indonesia (Chatime)
        </p>
        <h3 class="system-text">
            Sistem Informasi Daily Sales Report
        </h3>
    </footer>

    <!-- Scripts -->
    <script>
        // Get user data from session
        const userData = @json(session('user', []));
        
        // Global functions
        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        }

        function showNotification(message, type = 'success') {
            // Simple notification system
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 20px;
                border-radius: 4px;
                color: white;
                font-weight: 500;
                z-index: 9999;
                ${type === 'success' ? 'background-color: #22c55e;' : 'background-color: #ef4444;'}
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
    @stack('scripts')
</body>
</html>
