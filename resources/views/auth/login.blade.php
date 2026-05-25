{{-- resources/views/auth/login-fancy.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>تسجيل الدخول | البُعد الجديد</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ====== المتغيرات والألوان الجديدة ====== */
        :root {
            --primary: #00f2fe;
            --secondary: #4facfe;
            --accent: #b465da;
            --bg-dark: #090a0f;
            --card-bg: rgba(16, 20, 30, 0.5);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-main: #f0f4f8;
            --text-muted: #8b9bb4;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            margin: 0;
        }

        body {
            background-color: var(--bg-dark);
            font-family: 'Tajawal', Inter, system-ui, sans-serif;
            color: var(--text-main);
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        /* خلفية الألوان المتحركة (Mesh Gradient) */
        .bg-mesh {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -1;
            overflow: hidden;
            background: var(--bg-dark);
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.5;
            animation: floatOrb 15s ease-in-out infinite alternate;
        }

        .orb-1 {
            width: 400px;
            height: 400px;
            background: var(--secondary);
            top: -10%;
            right: -5%;
        }

        .orb-2 {
            width: 500px;
            height: 500px;
            background: var(--accent);
            bottom: -20%;
            left: -10%;
            animation-delay: -5s;
        }

        .orb-3 {
            width: 300px;
            height: 300px;
            background: var(--primary);
            top: 40%;
            left: 40%;
            opacity: 0.3;
            animation-duration: 20s;
        }

        @keyframes floatOrb {
            0% {
                transform: translate(0, 0) scale(1);
            }

            100% {
                transform: translate(50px, 50px) scale(1.2);
            }
        }

        /* الحاوية الرئيسية والكارت */
        .login-wrapper {
            width: 100%;
            max-width: 1000px;
            padding: 2rem;
            z-index: 1;
        }

        .glass-card {
            display: flex;
            flex-direction: row;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        /* الجانب البصري (Hero) */
        .visual-side {
            flex: 1;
            padding: 3rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.03), transparent);
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            border-left: 1px solid var(--glass-border);
        }

        .visual-side h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(to right, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
        }

        .visual-side p {
            color: var(--text-muted);
            font-size: 1.1rem;
            line-height: 1.6;
            max-width: 90%;
        }

        .floating-element {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 30px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 242, 254, 0.3);
            animation: spinFloat 8s linear infinite;
        }

        @keyframes spinFloat {
            0% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(180deg);
            }

            100% {
                transform: translateY(0) rotate(360deg);
            }
        }

        /* جانب الفورم */
        .form-side {
            flex: 1;
            padding: 3.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 2.5rem;
        }

        .form-header h2 {
            font-size: 1.8rem;
            margin: 0 0 0.5rem 0;
            color: var(--text-main);
        }

        .form-header p {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* الحقول */
        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .input-group input[type="email"],
        .input-group input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: var(--text-main);
            font-size: 1rem;
            transition: all 0.3s ease;
            outline: none;
        }

        .input-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(0, 242, 254, 0.1);
            background: rgba(0, 0, 0, 0.4);
        }

        /* خيارات إضافية (تذكرني + نسيت كلمة المرور) */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            accent-color: var(--primary);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .forgot-link {
            color: var(--primary);
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .forgot-link:hover {
            opacity: 0.8;
        }

        /* زر التسجيل */
        .btn-submit {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            color: #fff;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(0, 242, 254, 0.25);
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(0, 242, 254, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* التجاوب مع الشاشات (Responsive) */
        @media (max-width: 850px) {
            .glass-card {
                flex-direction: column;
            }

            .visual-side {
                border-left: none;
                border-bottom: 1px solid var(--glass-border);
                padding: 2.5rem;
                text-align: center;
                align-items: center;
            }

            .visual-side h1 {
                font-size: 2rem;
            }

            .floating-element {
                width: 80px;
                height: 80px;
                margin-bottom: 1.5rem;
            }

            .form-side {
                padding: 2.5rem;
            }
        }

        @media (max-width: 480px) {
            .login-wrapper {
                padding: 1rem;
            }

            .form-side,
            .visual-side {
                padding: 1.5rem;
            }

            .form-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>

    <div class="bg-mesh">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="login-wrapper">
        <div class="glass-card">

            <div class="visual-side">
                <div class="floating-element"></div>
                <h1>آفاق جديدة <br>لإدارة أعمالك</h1>
                <p>نقدم لك تجربة مستخدم سلسة، سريعة، وآمنة. سجل دخولك الآن للوصول إلى لوحة التحكم الخاصة بك بكل سهولة.
                </p>
            </div>

            <div class="form-side">
                <div class="form-header">
                    <h2>مرحباً بعودتك 👋</h2>
                    <p>يرجى إدخال بيانات الاعتماد الخاصة بك للمتابعة</p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="input-group">
                        <label for="email">البريد الإلكتروني</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            autofocus autocomplete="username" placeholder="name@example.com">
                        <x-input-error :messages="$errors->get('email')"
                            style="color: #ff6b6b; font-size: 0.85rem; margin-top: 5px; display:block;" />
                    </div>

                    <div class="input-group">
                        <label for="password">كلمة المرور</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            placeholder="••••••••">
                        <x-input-error :messages="$errors->get('password')"
                            style="color: #ff6b6b; font-size: 0.85rem; margin-top: 5px; display:block;" />
                    </div>

                    <div class="form-options">
                        <label class="remember-me" for="remember_me">
                            <input id="remember_me" type="checkbox" name="remember">
                            <span>تذكرني</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="forgot-link" href="{{ route('password.request') }}">نسيت كلمة المرور؟</a>
                        @endif
                    </div>

                    <button type="submit" class="btn-submit">
                        تسجيل الدخول
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </button>
                </form>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('mousemove', (e) => {
            if (window.matchMedia('(max-width: 850px)').matches) return; // تعطيل في الموبايل
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;

            document.querySelector('.orb-1').style.transform = `translate(${x * 30}px, ${y * 30}px)`;
            document.querySelector('.orb-2').style.transform = `translate(${x * -40}px, ${y * -40}px)`;
        });
    </script>
</body>

</html>
