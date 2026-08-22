<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>تسجيل الدخول | Tagmedix</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ====== الهوية البصرية الجديدة (Theme Colors) ====== */
        :root {
            --bg-primary: #EAE9DF;    /* الكريمي الأساسي للخلفيات */
            --bg-secondary: #DBD0C3;  /* درجات البطاقات/المناطق الفاتحة */
            --soft-beige: #CABCB0;    /* الظلال والـ accents الهادئة */
            --primary-brown: #926857; /* أهم لون للـ headings والأزرار */
            --text-dark: #2A2120;     /* النصوص والأيقونات */
            --text-muted: #503E39;    /* النصوص الثانوية */
            --rose-accent: #C19077;   /* لمسات وردية/بيج */
            --soft-pink: #EAAD99;     /* Accent إضافي */
            --white: #FFFFFF;         /* المساحات البيضاء */
            
            --error: #EF4444;
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-primary);
            font-family: 'Cairo', system-ui, sans-serif;
            color: var(--text-dark);
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
            background: var(--bg-primary);
        }

        /* ====== الجانب المرئي (الألوان الهادئة الفاخرة) ====== */
        .visual-section {
            flex: 1.2;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            color: var(--text-dark);
        }

        /* تأثير زخرفي ناعم في الخلفية */
        .visual-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(var(--soft-beige) 1px, transparent 1px);
            background-size: 30px 30px;
            opacity: 0.3;
        }

        /* الأشكال العضوية المتحركة (Organic Blobs) */
        .shape {
            position: absolute;
            filter: blur(60px);
            z-index: 0;
            animation: morphBlob 15s ease-in-out infinite alternate;
        }

        .shape-1 {
            width: 450px;
            height: 450px;
            background: rgba(193, 144, 119, 0.4); /* Rose Accent */
            top: -5%;
            right: -10%;
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
        }

        .shape-2 {
            width: 500px;
            height: 500px;
            background: rgba(234, 173, 153, 0.4); /* Soft Pink */
            bottom: -10%;
            left: -15%;
            animation-delay: -5s;
            border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
        }

        .shape-3 {
            width: 350px;
            height: 350px;
            background: rgba(202, 188, 176, 0.5); /* Soft Beige */
            top: 40%;
            left: 20%;
            animation-delay: -10s;
            border-radius: 50% 50% 30% 70% / 50% 70% 30% 50%;
        }

        .visual-content {
            position: relative;
            z-index: 1;
            max-width: 520px;
        }

        .visual-badge {
            display: inline-block;
            padding: 8px 20px;
            background: rgba(255, 255, 255, 0.6);
            color: var(--primary-brown);
            border-radius: 30px;
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 24px;
            border: 1px solid var(--white);
            box-shadow: 0 4px 15px rgba(202, 188, 176, 0.3);
            backdrop-filter: blur(10px);
        }

        .visual-content h1 {
            font-size: 3.8rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            color: var(--text-dark);
        }

        .visual-content h1 span {
            color: var(--primary-brown);
            position: relative;
            display: inline-block;
        }
        
        /* خط زينة تحت الكلمة المميزة */
        .visual-content h1 span::after {
            content: '';
            position: absolute;
            bottom: 5px;
            right: 0;
            width: 100%;
            height: 8px;
            background: var(--soft-pink);
            z-index: -1;
            border-radius: 4px;
            opacity: 0.6;
        }

        .visual-content p {
            font-size: 1.15rem;
            color: var(--text-muted);
            line-height: 1.8;
            font-weight: 600;
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
            background: var(--bg-primary);
        }

        .form-card {
            width: 100%;
            max-width: 480px;
            background: var(--white);
            padding: 3.5rem;
            border-radius: 32px;
            box-shadow: 0 20px 50px rgba(42, 33, 32, 0.08); /* ظل متناسق مع الألوان */
            position: relative;
            z-index: 2;
            animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* تصميم اللوجو الجديد */
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 2rem;
            justify-content: flex-start;
        }

        .brand-logo svg {
            width: 42px;
            height: 42px;
            fill: none;
        }

        .brand-logo .logo-text {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary-brown);
            letter-spacing: -0.5px;
        }

        .form-header {
            margin-bottom: 2.5rem;
        }

        .form-header h2 {
            font-size: 1.8rem;
            color: var(--text-dark);
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 1rem;
            font-weight: 600;
        }

        /* تصميم الحقول (Floating Labels) */
        .input-group {
            position: relative;
            margin-bottom: 1.8rem;
            opacity: 0;
            transform: translateY(10px);
            animation: slideUpFade 0.5s ease forwards;
        }

        .input-group:nth-child(2) { animation-delay: 0.1s; }
        .input-group:nth-child(3) { animation-delay: 0.2s; }
        .input-group:nth-child(4) { animation-delay: 0.3s; }

        .input-group input {
            width: 100%;
            padding: 18px 16px 10px 45px; /* 45px لترك مساحة لعين إظهار الباسورد في اليسار */
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            color: var(--text-dark);
            background: var(--bg-primary);
            border: 2px solid var(--soft-beige);
            border-radius: 16px;
            outline: none;
            transition: var(--transition);
        }

        .input-group input:focus,
        .input-group input:not(:placeholder-shown) {
            background: var(--white);
            border-color: var(--primary-brown);
            box-shadow: 0 0 0 4px rgba(146, 104, 87, 0.15); /* Primary Brown Shadow */
        }

        .input-group label {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1rem;
            font-weight: 600;
            pointer-events: none;
            transition: var(--transition);
            background: transparent;
            padding: 0 4px;
        }

        .input-group input:focus~label,
        .input-group input:not(:placeholder-shown)~label {
            top: 0;
            right: 14px;
            font-size: 0.85rem;
            color: var(--primary-brown);
            font-weight: 700;
            background: var(--white);
        }

        .toggle-password {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--soft-beige);
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .toggle-password:hover {
            color: var(--primary-brown);
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
            font-weight: 700;
            font-size: 0.95rem;
        }

        /* Checkbox مخصص واحترافي */
        .remember-me input {
            appearance: none;
            width: 22px;
            height: 22px;
            border: 2px solid var(--soft-beige);
            border-radius: 8px;
            background: var(--bg-primary);
            cursor: pointer;
            position: relative;
            transition: var(--transition);
        }

        .remember-me input:checked {
            background: var(--primary-brown);
            border-color: var(--primary-brown);
        }

        .remember-me input:checked::after {
            content: '✔';
            position: absolute;
            color: var(--white);
            font-size: 13px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .forgot-link {
            color: var(--rose-accent);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .forgot-link:hover {
            color: var(--primary-brown);
        }

        /* زر الإرسال */
        .btn-submit {
            width: 100%;
            padding: 18px;
            background: var(--primary-brown);
            color: var(--white);
            border: none;
            border-radius: 16px;
            font-size: 1.15rem;
            font-weight: 800;
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
            box-shadow: 0 10px 20px rgba(146, 104, 87, 0.25);
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.6s;
        }

        .btn-submit:hover {
            background: var(--text-dark); /* يتغير للون الداكن عند المرور لإعطاء فخامة */
            box-shadow: 0 10px 25px rgba(42, 33, 32, 0.3);
            transform: translateY(-3px);
        }

        .btn-submit:hover::before {
            left: 100%;
        }

        .btn-submit svg {
            transition: transform 0.3s ease;
        }

        .btn-submit:hover svg {
            transform: translateX(-6px);
        }

        .error-msg {
            color: var(--error);
            font-size: 0.85rem;
            margin-top: 8px;
            display: block;
            font-weight: 700;
            padding-right: 10px;
        }

        /* ====== الأنيميشن ====== */
        @keyframes slideUpFade {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes morphBlob {
            0% { transform: scale(1) translate(0, 0) rotate(0deg); }
            33% { transform: scale(1.05) translate(20px, -30px) rotate(5deg); }
            66% { transform: scale(0.95) translate(-20px, 20px) rotate(-5deg); }
            100% { transform: scale(1.1) translate(10px, -10px) rotate(2deg); }
        }

        /* ====== التجاوب (Responsive) ====== */
        @media (max-width: 992px) {
            .layout-container {
                flex-direction: column;
            }

            .visual-section {
                flex: none;
                padding: 4rem 2rem 6rem;
                align-items: center;
                text-align: center;
                border-bottom-left-radius: 40px;
                border-bottom-right-radius: 40px;
            }

            .visual-content h1 {
                font-size: 2.8rem;
            }

            .shape-3 { display: none; }

            .form-section {
                padding: 0 1.5rem 3rem;
                margin-top: -60px; /* دمج الكارت مع الجزء العلوي بشكل احترافي */
                background: transparent;
            }

            .form-card {
                padding: 2.5rem;
                box-shadow: 0 15px 40px rgba(42, 33, 32, 0.1);
            }
        }

        @media (max-width: 480px) {
            .visual-content h1 {
                font-size: 2.2rem;
            }

            .visual-badge {
                font-size: 0.85rem;
            }

            .form-card {
                padding: 2rem 1.5rem;
                border-radius: 24px;
            }

            .brand-logo .logo-text {
                font-size: 1.5rem;
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
        <!-- الجانب المرئي الفاخر -->
        <div class="visual-section">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>

            <div class="visual-content">
                <div class="visual-badge">نظام إدارة Tagmedix</div>
                <h1>مرحباً بك في <br><span>البُعد الجديد</span> للأعمال</h1>
                <p>لوحة تحكم مصممة بدقة لتمنحك السيطرة الكاملة، سرعة فائقة في الأداء، وتجربة مستخدم ترتقي بتطلعاتك المهنية.</p>
            </div>
        </div>

        <!-- جانب تسجيل الدخول -->
        <div class="form-section">
            <div class="form-card">
                
              <div class="brand-logo">
    <img src="{{ asset('t.webp') }}" alt="Tagmedix Logo" class="brand-logo-image">
    <span class="logo-text"></span>
</div>

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
                            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="22" height="22"
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
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
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
        // 1. إظهار/إخفاء كلمة المرور بسلاسة
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

        // 2. تأثير بارالاكس (Parallax) خفيف وعضوي للأشكال
        document.addEventListener('mousemove', (e) => {
            if (window.innerWidth < 992) return; 

            const x = (e.clientX / window.innerWidth - 0.5) * 30;
            const y = (e.clientY / window.innerHeight - 0.5) * 30;

            requestAnimationFrame(() => {
                document.querySelector('.shape-1').style.transform = `translate(${x}px, ${y}px)`;
                document.querySelector('.shape-2').style.transform = `translate(${-x}px, ${-y}px)`;
            });
        });
    </script>
</body>

</html>