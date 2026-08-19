<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Daily Sales Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .animated-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #f5576c 75%, #4facfe 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        
        .shape {
            position: absolute;
            opacity: 0.1;
        }
        
        .shape-1 {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
            border-radius: 50%;
            top: 10%;
            left: 10%;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape-2 {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #a8e6cf, #dcedc1);
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            top: 70%;
            right: 10%;
            animation: float 8s ease-in-out infinite reverse;
        }
        
        .shape-3 {
            width: 100px;
            height: 100px;
            background: linear-gradient(45deg, #ffd3b6, #ffaaa5);
            border-radius: 50%;
            bottom: 10%;
            left: 50%;
            animation: float 10s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group input:focus + label,
        .input-group input:not(:placeholder-shown) + label {
            transform: translateY(-25px) scale(0.85);
            color: #667eea;
        }
        
        .floating-label {
            position: absolute;
            left: 12px;
            top: 14px;
            transition: all 0.3s ease;
            pointer-events: none;
            color: #9ca3af;
            font-size: 14px;
        }
        
        .btn-primary-modern {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary-modern:hover::before {
            left: 100%;
        }
        
        .demo-card {
            transition: all 0.3s ease;
        }
        
        .demo-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .role-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .store-manager { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; }
        .area-manager { background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: white; }
        .kasir { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }
    </style>
</head>
<body class="animated-bg min-h-screen flex justify-center items-start pt-32">
    <!-- Floating Shapes Background -->
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="relative z-10 w-full max-w-2xl px-4">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl mb-4 shadow-lg">
                <i class="fas fa-chart-line text-3xl bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent"></i>
            </div>
            <h1 class="text-4xl font-bold text-white mb-2">Daily Sales Report</h1>
            <p class="text-white/80">Login to access your dashboard</p>
        </div>

        <!-- Login Card -->
        <div class="glass-effect rounded-2xl shadow-2xl p-12">
            <form action="/login" method="POST">
                @csrf
                <div class="space-y-6">
                    <!-- Email Field -->
                    <div class="input-group">
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            class="w-full px-5 py-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all peer"
                            placeholder=" "
                            value="{{ old('email') }}"
                        >
                        <label for="email" class="floating-label bg-white px-2">
                            <i class="fas fa-envelope mr-2 text-purple-500"></i>Email Address
                        </label>
                    </div>
            
                    <!-- Password Field -->
                    <div class="input-group">
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all peer"
                                placeholder=" "
                            >
                            <label for="password" class="floating-label bg-white px-2">
                                <i class="fas fa-lock mr-2 text-purple-500"></i>Password
                            </label>
                            <button 
                                type="button" 
                                id="togglePassword"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-purple-600 transition-colors"
                            >
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
            
                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                id="remember" 
                                name="remember"
                                class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                            >
                            <span class="ml-2 text-sm text-gray-700">Remember me</span>
                        </label>
                    </div>
            
                    <!-- Login Button -->
                    <button 
                        type="submit" 
                        class="btn-primary-modern w-full py-3 px-4 text-white font-semibold rounded-lg relative"
                    >
                        <span class="relative z-10">
                            <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                        </span>
                    </button>
            
                   
                    @error('email')
                        <p class="text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
            
                    @error('password')
                        <p class="text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>
            </form>

            <!-- Success Message -->
            @if (session('success'))
                <div class="mt-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif
        </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Fill demo account
        function fillDemo(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';
            
            // Add animation effect
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            
            emailInput.classList.add('ring-2', 'ring-purple-500');
            passwordInput.classList.add('ring-2', 'ring-purple-500');
            
            setTimeout(() => {
                emailInput.classList.remove('ring-2', 'ring-purple-500');
                passwordInput.classList.remove('ring-2', 'ring-purple-500');
            }, 1000);
        }

        // Add loading state to form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalContent = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Signing in...';
            submitBtn.disabled = true;
            
            // Reset if submission fails (after 5 seconds as fallback)
            setTimeout(() => {
                submitBtn.innerHTML = originalContent;
                submitBtn.disabled = false;
            }, 5000);
        });
    </script>
</body>
</html>
