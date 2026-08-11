{{-- resources/views/auth/login-fancy.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>تسجيل الدخول | لوحة التحكم</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ====== المتغيرات (Theme Colors) ====== */
        :root {
            --sidebar: #0F172A;
            --active-gold: #8A704C;
            --primary: #8A704C;
            --primary-hover: #A4865C;
            --body-bg: #F1F5F9;
            --white: #FFFFFF;

            --success: #22C55E;
            --warning: #F59E0B;
            --error: #EF4444;
            --info: #3B82F6;

            --text-main: #1E293B;
            --text-muted: #64748B;
            --border-color: #E2E8F0;

            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--body-bg);
            font-family: 'Cairo', system-ui, sans-serif;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        /* ====== الحاوية الرئيسية ====== */
        .layout-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
            background: var(--body-bg);
        }

        /* ====== الجانب المرئي (الداكن والذهبي) ====== */
        .visual-section {
            flex: 1.2;
            background: var(--sidebar);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            color: var(--white);
        }

        /* تأثير الشبكة والنقاط في الخلفية */
        .visual-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(var(--active-gold) 1px, transparent 1px);
            background-size: 40px 40px;
            opacity: 0.1;
        }

        /* الأشكال المتحركة الاحترافية */
        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            animation: pulseShape 10s ease-in-out infinite alternate;
        }

        .shape-1 {
            width: 400px;
            height: 400px;
            background: rgba(138, 112, 76, 0.2);
            /* Gold */
            top: -10%;
            right: -10%;
        }

        .shape-2 {
            width: 500px;
            height: 500px;
            background: rgba(15, 23, 42, 0.8);
            /* Dark */
            bottom: -20%;
            left: -20%;
            animation-delay: -5s;
        }

        .shape-3 {
            width: 300px;
            height: 300px;
            border: 2px solid rgba(138, 112, 76, 0.1);
            border-radius: 30%;
            top: 40%;
            right: 30%;
            filter: none;
            animation: spin 20s linear infinite;
        }

        .visual-content {
            position: relative;
            z-index: 1;
            max-width: 500px;
        }

        .visual-badge {
            display: inline-block;
            padding: 8px 16px;
            background: rgba(138, 112, 76, 0.15);
            color: var(--primary-hover);
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 24px;
            border: 1px solid rgba(138, 112, 76, 0.3);
            backdrop-filter: blur(10px);
        }

        .visual-content h1 {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            color: var(--white);
        }

        .visual-content h1 span {
            color: var(--active-gold);
        }

        .visual-content p {
            font-size: 1.1rem;
            color: #94A3B8;
            line-height: 1.8;
        }

        /* ====== جانب نموذج تسجيل الدخول ====== */
        .form-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            position: relative;
        }

        .form-card {
            width: 100%;
            max-width: 480px;
            background: var(--white);
            padding: 3.5rem;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
            position: relative;
            z-index: 2;
            animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .form-header {
            margin-bottom: 2.5rem;
            text-align: right;
        }

        .form-header h2 {
            font-size: 2rem;
            color: var(--sidebar);
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 1rem;
        }

        /* تصميم الحقول (Floating Labels) */
        .input-group {
            position: relative;
            margin-bottom: 1.8rem;
            opacity: 0;
            transform: translateY(10px);
            animation: slideUpFade 0.5s ease forwards;
        }

        .input-group:nth-child(2) {
            animation-delay: 0.1s;
        }

        .input-group:nth-child(3) {
            animation-delay: 0.2s;
        }

        .input-group:nth-child(4) {
            animation-delay: 0.3s;
        }

        .input-group input {
            width: 100%;
            padding: 18px 16px 10px;
            font-size: 1rem;
            font-family: inherit;
            color: var(--text-main);
            background: #F8FAFC;
            border: 2px solid transparent;
            border-radius: 12px;
            outline: none;
            transition: var(--transition);
        }

        .input-group input:focus,
        .input-group input:not(:placeholder-shown) {
            background: var(--white);
            border-color: var(--active-gold);
            box-shadow: 0 0 0 4px rgba(138, 112, 76, 0.1);
        }

        .input-group label {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1rem;
            pointer-events: none;
            transition: var(--transition);
            background: transparent;
        }

        .input-group input:focus~label,
        .input-group input:not(:placeholder-shown)~label {
            top: 10px;
            font-size: 0.8rem;
            color: var(--active-gold);
            font-weight: 600;
        }

        .toggle-password {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted);
            transition: var(--transition);
        }

        .toggle-password:hover {
            color: var(--active-gold);
        }

        /* خيارات إضافية */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            opacity: 0;
            animation: slideUpFade 0.5s 0.4s ease forwards;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.95rem;
        }

        /* Checkbox مخصص واحترافي */
        .remember-me input {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            background: var(--white);
            cursor: pointer;
            position: relative;
            transition: var(--transition);
        }

        .remember-me input:checked {
            background: var(--active-gold);
            border-color: var(--active-gold);
        }

        .remember-me input:checked::after {
            content: '✔';
            position: absolute;
            color: white;
            font-size: 12px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .forgot-link {
            color: var(--active-gold);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .forgot-link:hover {
            color: var(--sidebar);
        }

        /* زر الإرسال */
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: var(--sidebar);
            color: var(--white);
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            opacity: 0;
            animation: slideUpFade 0.5s 0.5s ease forwards;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: 0.5s;
        }

        .btn-submit:hover {
            background: var(--active-gold);
            box-shadow: 0 10px 20px rgba(138, 112, 76, 0.3);
            transform: translateY(-2px);
        }

        .btn-submit:hover::before {
            left: 100%;
        }

        .btn-submit svg {
            transition: transform 0.3s ease;
        }

        .btn-submit:hover svg {
            transform: translateX(-5px);
            /* السهم يتجه لليسار في العربي */
        }

        .error-msg {
            color: var(--error);
            font-size: 0.85rem;
            margin-top: 6px;
            display: block;
            font-weight: 600;
        }

        /* ====== الأنيميشن ====== */
        @keyframes slideUpFade {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulseShape {
            0% {
                transform: scale(1) translate(0, 0);
            }

            100% {
                transform: scale(1.1) translate(20px, 20px);
            }
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        /* ====== التجاوب (Responsive) ====== */
        @media (max-width: 992px) {
            .layout-container {
                flex-direction: column;
            }

            .visual-section {
                flex: none;
                padding: 3rem 2rem;
                align-items: center;
                text-align: center;
            }

            .visual-content h1 {
                font-size: 2.5rem;
            }

            .shape-3 {
                display: none;
            }

            .form-section {
                padding: 2rem 1rem;
                margin-top: -40px;
                /* لدمج الكارت مع القسم العلوي */
            }

            .form-card {
                padding: 2.5rem;
                box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.1);
            }
        }

        @media (max-width: 480px) {
            .visual-content h1 {
                font-size: 2rem;
            }

            .form-card {
                padding: 2rem 1.5rem;
            }

            .form-header h2 {
                font-size: 1.5rem;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>

<body>

    <div class="layout-container">
        <div class="visual-section">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>

            <div class="visual-content">
                <div class="visual-badge">✨ نظام الإدارة الذكي</div>
                <h1>مرحباً بك في <br><span>البُعد الجديد</span> للأعمال</h1>
                <p>لوحة تحكم مصممة بدقة لتمنحك السيطرة الكاملة، سرعة فائقة في الأداء، وتجربة مستخدم ترتقي بتطلعاتك
                    المهنية.</p>
            </div>
        </div>

        <div class="form-section">
            <div class="form-card">

                <div class="form-header">
                    <h2>تسجيل الدخول 👋</h2>
                    <p>أدخل بيانات الاعتماد الخاصة بك للوصول لحسابك</p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="input-group">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            autofocus autocomplete="username" placeholder=" ">
                        <label for="email">البريد الإلكتروني</label>
                        <x-input-error :messages="$errors->get('email')" class="error-msg" />
                    </div>

                    <div class="input-group">
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            placeholder=" ">
                        <label for="password">كلمة المرور</label>
                        <div class="toggle-password" onclick="togglePassword()">
                            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="error-msg" />
                    </div>

                    <div class="form-options">
                        <label class="remember-me" for="remember_me">
                            <input id="remember_me" type="checkbox" name="remember">
                            <span>تذكر بياناتي</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="forgot-link" href="{{ route('password.request') }}">نسيت كلمة المرور؟</a>
                        @endif
                    </div>

                    <button type="submit" class="btn-submit">
                        الدخول للوحة التحكم
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                    </button>
                </form>

            </div>
        </div>
    </div>

    <script>
        // 1. إظهار/إخفاء كلمة المرور (UX مريح جداً)
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML =
                    `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>`;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML =
                    `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>`;
            }
        }

        // 2. تأثير بارالاكس (Parallax) خفيف جداً لا يستهلك موارد الجهاز
        document.addEventListener('mousemove', (e) => {
            if (window.innerWidth < 992) return; // تفعيل فقط في الديسكتوب

            const x = (e.clientX / window.innerWidth - 0.5) * 20;
            const y = (e.clientY / window.innerHeight - 0.5) * 20;

            requestAnimationFrame(() => {
                document.querySelector('.shape-1').style.transform = `translate(${x}px, ${y}px)`;
                document.querySelector('.shape-2').style.transform = `translate(${-x}px, ${-y}px)`;
            });
        });
    </script>
</body>

</html>
