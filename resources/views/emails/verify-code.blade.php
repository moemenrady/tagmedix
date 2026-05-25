@extends('layouts.app')

@section('content')
<style>
    /* حاوية الصفحة المركزية */
    .verification-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
        padding: 20px;
    }

    /* كارت الكود بنفس ستايل لوحة التحكم */
    .verification-card {
        position: relative;
        background: linear-gradient(145deg, rgba(20, 25, 35, 0.8), rgba(10, 12, 18, 0.6));
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 24px;
        padding: 4rem 2rem;
        width: 100%;
        max-width: 550px;
        text-align: center;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        animation: fadeInUp 0.8s ease forwards;
    }

    /* الترحيب */
    .verification-card h2 {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 1rem;
    }

    .verification-card h2 span {
        color: var(--primary);
    }

    /* كود التحقق */
    .code-display {
        background: rgba(255, 255, 255, 0.05);
        border: 1px dashed var(--primary);
        border-radius: 16px;
        padding: 1.5rem;
        margin: 2rem 0;
        position: relative;
        overflow: hidden;
    }

    .code-display h1 {
        font-size: 3.5rem;
        font-weight: 900;
        letter-spacing: 10px;
        margin: 0;
        background: linear-gradient(135deg, #fff, var(--primary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-family: 'Courier New', Courier, monospace; /* لضمان وضوح الأرقام */
    }

    /* النصوص المساعدة */
    .info-text {
        color: var(--text-muted);
        font-size: 1rem;
        line-height: 1.6;
    }

    .timer-text {
        color: #ff4b2b; /* لون تنبيهي للوقت */
        font-weight: 600;
        margin-top: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    /* جملة الدعم */
    .support-note {
        margin-top: 2.5rem;
        padding-top: 2rem;
        border-top: 1px solid var(--glass-border);
    }

    .btn-support {
        display: inline-block;
        color: var(--primary);
        text-decoration: none;
        font-weight: bold;
        padding: 10px 20px;
        border: 1px solid var(--primary);
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .btn-support:hover {
        background: var(--primary);
        color: #000;
        box-shadow: 0 0 20px rgba(0, 242, 254, 0.3);
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="verification-container">
    <div class="verification-card">
        <div style="font-size: 4rem; margin-bottom: 1.5rem;">🔐</div>

        <h2>مرحباً، <span>{{ $user->name }}</span></h2>
        
        <p class="info-text">لقد تم إنشاء حسابك بنجاح. كود التحقق الخاص بك هو:</p>

        <div class="code-display">
            <h1>{{ $code }}</h1>
        </div>

        <p class="timer-text">
            <span>⏱️</span> الكود صالح لمدة 10 دقائق فقط.
        </p>

        <div class="support-note">
            <p class="info-text" style="margin-bottom: 15px;">
                يجب تفعيل الحساب من قبل الإدارة للتمكن من الدخول.
            </p>
            <a href="#" class="btn-support">
                تواصل مع دعم النظام لتفعيل الحساب
            </a>
        </div>
    </div>
</div>
@endsection