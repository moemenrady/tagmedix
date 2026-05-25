@extends('layouts.app')

@section('content')
<style>
    /* تنسيقات حاوية الفورم */
    .auth-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
        padding: 20px;
    }

    .auth-card {
        position: relative;
        background: linear-gradient(145deg, rgba(20, 25, 35, 0.8), rgba(10, 12, 18, 0.6));
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 24px;
        padding: 3rem;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        animation: fadeInUp 0.8s ease forwards;
    }

    .auth-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .auth-header h2 {
        font-size: 2rem;
        font-weight: 800;
        background: linear-gradient(135deg, #fff, var(--primary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.5rem;
    }

    /* تنسيق الحقول */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        color: var(--text-main);
        margin-bottom: 0.5rem;
        font-weight: 500;
        font-size: 0.9rem;
        padding-right: 5px;
    }

    .form-control-custom {
        width: 100%;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        padding: 12px 15px;
        color: white;
        transition: all 0.3s ease;
        outline: none;
    }

    .form-control-custom:focus {
        border-color: var(--primary);
        background: rgba(255, 255, 255, 0.1);
        box-shadow: 0 0 15px rgba(0, 242, 254, 0.2);
    }

    /* تنسيق القائمة المنسدلة (Role) */
    select.form-control-custom {
        appearance: none;
        cursor: pointer;
    }

    select.form-control-custom option {
        background: #141923;
        color: white;
    }

    /* أزرار الإرسال */
    .btn-submit {
        width: 100%;
        background: linear-gradient(135deg, var(--primary), #00d2ff);
        border: none;
        border-radius: 12px;
        padding: 14px;
        color: #000;
        font-weight: 700;
        font-size: 1.1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 1rem;
    }

    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 242, 254, 0.4);
        filter: brightness(1.1);
    }

    .auth-footer {
        text-align: center;
        margin-top: 2rem;
        font-size: 0.9rem;
        color: var(--text-muted);
    }

    .auth-footer a {
        color: var(--primary);
        text-decoration: none;
        font-weight: bold;
    }

    /* رسائل الخطأ */
    .error-text {
        color: #ff4b2b;
        font-size: 0.8rem;
        margin-top: 5px;
        display: block;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h2>إنشاء حساب جديد</h2>
            <p style="color: var(--text-muted)">انضم إلى نظام الإدارة الخاص بنا</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label>الاسم الكامل</label>
                <input type="text" name="name" class="form-control-custom @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus placeholder="أدخل اسمك">
                @error('name')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>البريد الإلكتروني</label>
                <input type="email" name="email" class="form-control-custom @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="example@domain.com">
                @error('email')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>نوع الحساب (الصلاحية)</label>
                <select name="role" class="form-control-custom @error('role') is-invalid @enderror">
                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>مستخدم (User)</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>مدير (Admin)</option>
                </select>
                @error('role')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>كلمة المرور</label>
                <input type="password" name="password" class="form-control-custom @error('password') is-invalid @enderror" required placeholder="••••••••">
                @error('password')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>تأكيد كلمة المرور</label>
                <input type="password" name="password_confirmation" class="form-control-custom" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn-submit">
                تسجيل الحساب
            </button>
        </form>

        <div class="auth-footer">
            لديك حساب بالفعل؟ <a href="{{ route('login') }}">تسجيل الدخول</a>
        </div>
    </div>
</div>
@endsection