<!DOCTYPE html>
@if(app()->getLocale() =='ar')
<html direction="rtl" dir="rtl" style="direction: rtl" lang="ar">
@else
<html lang="en">
@endif
<head>
    <base href="../../"/>
    <title>@if(app()->getLocale() =='ar') تسجيل الدخول @else Sign In @endif — Admin</title>
    <meta charset="utf-8"/>
    <meta name="description" content="Secure admin authentication"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <meta property="og:locale" content="{{ app()->getLocale() == 'ar' ? 'ar_SA' : 'en_US' }}"/>
    
    <!-- Fonts: Inter for Latin, Plus Jakarta Sans for Arabic -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;450;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Linear/Vercel-inspired color palette - True minimalism */
            --bg-primary: #ffffff;
            --bg-secondary: #fafafa;
            --text-primary: #111111;
            --text-secondary: #666666;
            --text-tertiary: #888888;
            --border-default: #e5e5e5;
            --border-hover: #d4d4d4;
            --accent: #000000;
            --accent-hover: #333333;
            --error: #dc2626;
            --error-bg: #fef2f2;
            --success: #16a34a;
            --radius: 8px;
            --radius-sm: 6px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg-primary: #000000;
                --bg-secondary: #0a0a0a;
                --text-primary: #ffffff;
                --text-secondary: #a1a1aa;
                --text-tertiary: #71717a;
                --border-default: #27272a;
                --border-hover: #3f3f46;
                --accent: #ffffff;
                --accent-hover: #e4e4e7;
                --error: #ef4444;
                --error-bg: rgba(239, 68, 68, 0.1);
            }
        }

        [dir="rtl"] {
            --font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        [dir="ltr"] {
            --font-family: 'Inter', 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            height: 100%;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            font-family: var(--font-family);
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.5;
            font-size: 15px;
            letter-spacing: -0.01em;
        }

        /* Subtle grid pattern background - Linear style */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                linear-gradient(to right, var(--border-default) 1px, transparent 1px),
                linear-gradient(to bottom, var(--border-default) 1px, transparent 1px);
            background-size: 40px 40px;
            opacity: 0.3;
            pointer-events: none;
            z-index: -1;
        }

        @media (prefers-color-scheme: dark) {
            body::before {
                opacity: 0.1;
            }
        }

        /* Header */
        .auth-header {
            padding: 24px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 16px;
            letter-spacing: -0.02em;
        }

        .brand-icon {
            width: 32px;
            height: 32px;
            background: var(--accent);
            color: var(--bg-primary);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .lang-switch {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-default);
            background: transparent;
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            font-family: inherit;
        }

        .lang-switch:hover {
            border-color: var(--border-hover);
            color: var(--text-primary);
            background: var(--bg-secondary);
        }

        /* Main Container */
        .auth-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        /* Card - Ultra minimal */
        .auth-card {
            width: 100%;
            max-width: 400px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-card-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .auth-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }

        .auth-subtitle {
            font-size: 15px;
            color: var(--text-secondary);
            font-weight: 400;
        }

        /* Form Elements - Perfect spacing */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 6px;
            letter-spacing: 0.01em;
        }

        .input-wrapper {
            position: relative;
        }

        .form-control {
            width: 100%;
            height: 44px;
            padding: 0 14px;
            background: var(--bg-primary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius);
            color: var(--text-primary);
            font-size: 15px;
            font-family: inherit;
            font-weight: 400;
            transition: var(--transition);
            outline: none;
        }

        [dir="rtl"] .form-control {
            text-align: right;
        }

        .form-control:hover {
            border-color: var(--border-hover);
        }

        .form-control:focus {
            border-color: var(--text-primary);
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
        }

        @media (prefers-color-scheme: dark) {
            .form-control:focus {
                box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
            }
        }

        .form-control::placeholder {
            color: var(--text-tertiary);
            opacity: 1;
        }

        .form-control:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Password Field */
        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 4px;
            color: var(--text-tertiary);
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: var(--transition);
            font-family: inherit;
        }

        [dir="rtl"] .password-toggle {
            right: auto;
            left: 12px;
        }

        .password-toggle:hover {
            color: var(--text-primary);
        }

        /* Options Row */
        .auth-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            font-size: 13px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
        }

        .checkbox-wrapper input {
            width: 16px;
            height: 16px;
            accent-color: var(--accent);
            cursor: pointer;
            margin: 0;
        }

        .checkbox-label {
            color: var(--text-secondary);
            font-weight: 450;
        }

        .link {
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
        }

        .link:hover {
            opacity: 0.7;
        }

        /* Button - Primary */
        .btn-primary {
            width: 100%;
            height: 44px;
            background: var(--accent);
            color: var(--bg-primary);
            border: none;
            border-radius: var(--radius);
            font-size: 15px;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            letter-spacing: -0.01em;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            display: none;
        }

        .btn-primary.loading .spinner {
            display: block;
        }

        .btn-primary.loading .btn-text {
            opacity: 0.9;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Divider - Vercel style */
        .divider {
            display: flex;
            align-items: center;
            margin: 24px 0;
            color: var(--text-tertiary);
            font-size: 13px;
            font-weight: 500;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-default);
        }

        .divider span {
            padding: 0 16px;
        }

        /* Social Login - Minimal */
        .btn-social {
            width: 100%;
            height: 44px;
            background: var(--bg-primary);
            border: 1px solid var(--border-default);
            border-radius: var(--radius);
            color: var(--text-primary);
            font-size: 14px;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-social:hover {
            background: var(--bg-secondary);
            border-color: var(--border-hover);
        }

        .btn-social svg {
            width: 18px;
            height: 18px;
        }

        /* Error Alert - Inline style */
        .alert {
            padding: 12px 16px;
            background: var(--error-bg);
            border: 1px solid rgba(220, 38, 38, 0.2);
            border-radius: var(--radius);
            color: var(--error);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-icon {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        /* Footer */
        .auth-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--border-default);
            font-size: 14px;
            color: var(--text-secondary);
        }

        .auth-footer .link {
            font-weight: 600;
        }

        /* Footer links */
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 24px;
            padding: 24px;
            font-size: 13px;
            color: var(--text-tertiary);
        }

        .footer-links a {
            color: var(--text-tertiary);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--text-primary);
        }

        /* Utilities */
        .text-center {
            text-align: center;
        }

        .mt-4 {
            margin-top: 16px;
        }

        /* Mobile Optimization */
        @media (max-width: 480px) {
            .auth-header {
                padding: 16px 20px;
            }

            .auth-main {
                padding: 16px;
                align-items: flex-start;
                padding-top: 40px;
            }

            .auth-title {
                font-size: 22px;
            }

            .footer-links {
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }
        }

        /* Smooth focus transitions */
        *:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }

        /* Selection color */
        ::selection {
            background: rgba(0, 0, 0, 0.1);
        }

        @media (prefers-color-scheme: dark) {
            ::selection {
                background: rgba(255, 255, 255, 0.2);
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="auth-header">
    
        
        @if(app()->getLocale() == 'ar')
            <a href="{{ LaravelLocalization::getLocalizedURL('en', null, [], true) }}" class="lang-switch">
                <span>English</span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </a>
        @else
            <a href="{{ LaravelLocalization::getLocalizedURL('ar', null, [], true) }}" class="lang-switch">
                <span>العربية</span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </a>
        @endif
    </header>

    <!-- Main Content -->
    <main class="auth-main">
        <div class="auth-card">
            
            <!-- Card Header -->
            <div class="auth-card-header">
                <h1 class="auth-title">
                    @if(app()->getLocale() =='ar') تسجيل الدخول @else Sign in to your account @endif
                </h1>
                <p class="auth-subtitle">
                    @if(app()->getLocale() =='ar') 
                        أدخل بياناتك للمتابعة إلى لوحة التحكم
                    @else 
                        Enter your details to continue to the dashboard
                    @endif
                </p>
            </div>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="alert">
                    <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('admin.login') }}" id="loginForm">
                @csrf

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="email">
                        @if(app()->getLocale() =='ar') البريد الإلكتروني @else Email @endif
                    </label>
                    <input 
                        type="email" 
                        id="email"
                        name="email" 
                        value="{{ old('email') }}" 
                        class="form-control" 
                        placeholder="name@company.com"
                        required
                        autocomplete="email"
                        autofocus
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="form-label" for="password">
                        @if(app()->getLocale() =='ar') كلمة المرور @else Password @endif
                    </label>
                    <div class="password-wrapper">
                        <input 
                            type="password" 
                            id="password"
                            name="password" 
                            class="form-control" 
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="password-toggle" id="togglePassword">
                            @if(app()->getLocale() =='ar') إظهار @else Show @endif
                        </button>
                    </div>
                </div>

                <!-- Options -->
                <div class="auth-options">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span class="checkbox-label">
                            @if(app()->getLocale() =='ar') تذكرني @else Remember me @endif
                        </span>
                    </label>

                    @if (Route::has('admin.password.request'))
                        <a href="{{ route('admin.password.request') }}" class="link">
                            @if(app()->getLocale() =='ar') نسيت كلمة المرور؟ @else Forgot password? @endif
                        </a>
                    @endif
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-primary" id="submitBtn">
                    <span class="spinner"></span>
                    <span class="btn-text">
                        @if(app()->getLocale() =='ar') تسجيل الدخول @else Continue @endif
                    </span>
                </button>

            </form>

            <!-- Register Link -->
            @if (Route::has('admin.register'))
                <div class="auth-footer">
                    @if(app()->getLocale() =='ar') 
                        ليس لديك حساب؟
                    @else 
                        Don't have an account?
                    @endif
                    <a href="{{ route('admin.register') }}" class="link" style="margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 4px;">
                        @if(app()->getLocale() =='ar') إنشاء حساب @else Sign up @endif
                    </a>
                </div>
            @endif

        </div>
    </main>

   


    <script>
        // Password Toggle
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        toggleBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Update text based on locale
            const isArabic = document.dir === 'rtl';
            this.textContent = type === 'password' 
                ? (isArabic ? 'إظهار' : 'Show')
                : (isArabic ? 'إخفاء' : 'Hide');
        });

        // Form Loading State
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        
        form.addEventListener('submit', function() {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });

        // Input focus effects - subtle border color change
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.zIndex = '1';
            });
        });
    </script>
</body>
</html>