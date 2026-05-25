@extends('layouts.app')

@section('page_title', 'Home - ' . (Auth::user()->role == 'production' ? 'Production Department' : 'Management'))

@section('styles')
    <style>
        /* Element Entry Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .welcome-header {
            margin-bottom: 4rem;
            animation: fadeInUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        }

        .welcome-title {
            font-size: 3rem;
            font-weight: 900;
            letter-spacing: -1px;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #fff, #a1a1aa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-badge {
            display: inline-block;
            background: linear-gradient(45deg, var(--cyan-glow), var(--purple-glow));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
        }

        /* Modern Display Grid */
        .bento-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        /* Distinctive Glassmorphism Cards Design */
        .glass-card {
            background: var(--surface);
            border: 1px solid var(--border-glass);
            border-radius: 24px;
            padding: 2rem;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            min-height: 220px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            opacity: 0;
            animation: fadeInUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
            transition: transform 0.4s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow 0.4s ease, border-color 0.4s ease;
        }

        /* Staggered animation delay for card reveal */
        .glass-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .glass-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .glass-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .glass-card:nth-child(4) {
            animation-delay: 0.4s;
        }

        .glass-card:nth-child(5) {
            animation-delay: 0.5s;
        }

        .glass-card:nth-child(6) {
            animation-delay: 0.6s;
        }

        .glass-card:nth-child(7) {
            animation-delay: 0.7s;
        }

        .glass-card:nth-child(8) {
            animation-delay: 0.8s;
        }

        /* Impressive Hover Effects */
        .glass-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.05), transparent);
            transform: skewX(-25deg);
            transition: 0.5s;
        }

        .glass-card:hover {
            transform: translateY(-8px) scale(1.02);
            background: var(--surface-hover);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            z-index: 2;
        }

        .glass-card:hover::before {
            left: 150%;
            transition: 0.7s ease;
        }

        /* Elegant Icons */
        .icon-wrapper {
            width: 65px;
            height: 65px;
            border-radius: 18px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1.5rem;
            position: relative;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.4s ease;
        }

        .glass-card:hover .icon-wrapper {
            transform: scale(1.1) rotate(5deg);
        }

        .icon-wrapper i,
        .icon-wrapper svg {
            font-size: 1.8rem;
            width: 32px;
            height: 32px;
            transition: all 0.4s;
        }

        /* Text Details */
        .card-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.5rem;
            transition: color 0.3s;
        }

        .card-desc {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin: 0;
            flex-grow: 1;
        }

        /* Custom Theme Colors */
        .theme-cyan .icon-wrapper {
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.2);
        }

        .theme-cyan .icon-wrapper svg {
            color: var(--cyan-glow);
        }

        .glass-card.theme-cyan:hover {
            border-color: var(--cyan-glow);
            box-shadow: 0 10px 30px rgba(6, 182, 212, 0.15);
        }

        .theme-purple .icon-wrapper {
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.2);
        }

        .theme-purple .icon-wrapper svg {
            color: var(--purple-glow);
        }

        .glass-card.theme-purple:hover {
            border-color: var(--purple-glow);
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.15);
        }

        .theme-pink .icon-wrapper {
            box-shadow: 0 0 20px rgba(236, 72, 153, 0.2);
        }

        .theme-pink .icon-wrapper svg {
            color: var(--pink-glow);
        }

        .glass-card.theme-pink:hover {
            border-color: var(--pink-glow);
            box-shadow: 0 10px 30px rgba(236, 72, 153, 0.15);
        }

        .theme-amber .icon-wrapper {
            background: rgba(245, 158, 11, 0.1);
            border-color: rgba(245, 158, 11, 0.3);
        }

        .theme-amber .icon-wrapper svg {
            color: var(--amber-glow);
        }

        .glass-card.theme-amber:hover {
            border-color: var(--amber-glow);
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.15);
        }
    </style>

    {{-- Typing Effect Style --}}
    <style>
        .typing-text {
            font-weight: 600;
            min-height: 40px;
            display: flex;
            align-items: center;
            gap: 4px;

            background: linear-gradient(90deg,
                    #94a3b8,
                    #e2e8f0,
                    var(--cyan-glow),
                    var(--purple-glow),
                    #cbd5e1);

            background-size: 300% 300%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;

            animation:
                gradientMove 6s ease infinite,
                fadeInUp 1s ease forwards;

            text-shadow: 0 0 20px rgba(255, 255, 255, 0.08);
        }

        /* Cursor */
        .typing-cursor {
            color: var(--cyan-glow);
            -webkit-text-fill-color: var(--cyan-glow);
            animation: blink 0.8s infinite;
            font-weight: bold;
        }

        /* Gradient Motion */
        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Cursor Blinking */
        @keyframes blink {

            0%,
            50% {
                opacity: 1;
            }

            51%,
            100% {
                opacity: 0;
            }
        }
    </style>
@endsection

@section('content')
    <header class="welcome-header">
        <h1 class="welcome-title">Welcome,
            <span class="hero-badge">
                @if (Auth::user()->role == 'production')
                    @if (Auth::user()->is_cutting)
                        (Cutting Hero)
                    @endif
                    @if (Auth::user()->is_sewing)
                        (Sewing Hero)
                    @endif
                    @if (Auth::user()->is_finishing)
                        (Finishing Hero)
                    @endif
                @endif
                {{ Auth::user()->name }}
            </span>
        </h1>
        <p class="typing-text fs-5">
            <span id="typing"></span>
            <span class="typing-cursor">|</span>
        </p>
    </header>

    <div class="bento-grid" id="cardsContainer">
      

        {{-- Currently Present - For Admin and Supervisor --}}
        @if (Auth::user()->role == 'admin' || Auth::user()->role == 'supervisor')
            <a href="#" class="glass-card theme-amber">
                <div class="icon-wrapper">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5V18a3 3 0 00-5-2.236"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20H4v-2a3 3 0 015-2.236"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 3.13a4 4 0 010 7.75"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 3.13a4 4 0 000 7.75"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 100-8 4 4 0 000 8z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 20v-1a6 6 0 00-12 0v1"></path>
                    </svg>
                </div>

                <h3 class="card-title" style="color: var(--amber-glow);">
                    Currently Present
                </h3>

                <p class="card-desc">
                    Live display of all employees currently present within each department
                    (Cutting - Sewing - Finishing - Store - Management)
                    with activity status and the latest system updates in real-time.
                </p>
            </a>
        @endif

        {{-- Shared Admin and Supervisor Permissions --}}
        @if (Auth::user()->role == 'admin' || Auth::user()->role == 'supervisor')
            <a href="#" class="glass-card theme-cyan">
                <div class="icon-wrapper">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z">
                        </path>
                    </svg>
                </div>
                <h3 class="card-title">Orders</h3>
                <p class="card-desc">Track all orders, view their details, and filter them with extreme precision and high
                    speed.</p>
            </a>

            <a href="#" class="glass-card theme-purple">
                <div class="icon-wrapper">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                </div>
                <h3 class="card-title">Work Flow</h3>
                <p class="card-desc">Track order statuses and smart-assign tasks to the production team.</p>
            </a>

            <a href="#" class="glass-card theme-cyan">
                <div class="icon-wrapper">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
                <h3 class="card-title">Employees</h3>
                <p class="card-desc">Manage supervisors, cutting, sewing, and finishing teams with advanced filtering.</p>
            </a>

            <a href="#" class="glass-card theme-purple">
                <div class="icon-wrapper">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <h3 class="card-title">Inventory</h3>
                <p class="card-desc">Full control over raw materials and finished products with absolute accuracy.</p>
            </a>

            <a href="#" class="glass-card theme-pink">
                <div class="icon-wrapper">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                </div>
                <h3 class="card-title">Suppliers</h3>
                <p class="card-desc">Manage supplier profiles, reports, and financial accounts transparently.</p>
            </a>

            <a href="#" class="glass-card theme-cyan">
                <div class="icon-wrapper">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                </div>
                <h3 class="card-title">Returns & Exchanges</h3>
                <p class="card-desc">Analyze returns and their direct, real-time impact on inventory.</p>
            </a>

            <a href="#" class="glass-card theme-purple">
                <div class="icon-wrapper">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0">
                        </path>
                    </svg>
                </div>
                <h3 class="card-title">Shipping</h3>
                <p class="card-desc">Streamline shipping orders and manage delivery routes to customers securely.</p>
            </a>
        @endif

        {{-- Admin Only Cards --}}
        @if (Auth::user()->role == 'admin')
            <a href="#" class="glass-card theme-pink">
                <div class="icon-wrapper">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM17 13v-2V7l-5-5-5 5v4v2a5 5 0 005 5h0a5 5 0 005-5z">
                        </path>
                    </svg>
                </div>
                <h3 class="card-title">Finance</h3>
                <p class="card-desc">Professional management of all finances, payments, and dues with a single click.</p>
            </a>
            <a href="#" class="glass-card theme-amber"
                style="grid-column: 1 / -1; max-width: 600px; margin: 0 auto;">
                <div class="icon-wrapper">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                </div>
                <h3 class="card-title" style="color: var(--amber-glow);">Supervisors' Action Log</h3>
                <p class="card-desc">System Radar: Accurate monitoring of all supervisor actions within the system to
                    ensure the highest quality standards.</p>
            </a>
        @endif

        {{-- Production Employee Tabs (Cutting, Sewing, Finishing) --}}
        @if (Auth::user()->role == 'production')
            <a href="#" class="glass-card theme-cyan">
                <div class="icon-wrapper">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="card-title">Tasks</h3>
                <p class="card-desc">A list of your current assigned tasks—execute them and move on to the next
                    achievement.</p>
            </a>

            <a href="#" class="glass-card theme-pink">
                <div class="icon-wrapper">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-7.714 2.143L11 21l-2.286-6.857L1 12l7.714-2.143L11 3z">
                        </path>
                    </svg>
                </div>
                <h3 class="card-title">Achievements Board</h3>
                <p class="card-desc">Your complete record of excellence with all tasks successfully accomplished.</p>
            </a>

            <a href="#" class="glass-card theme-purple">
                <div class="icon-wrapper">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h3 class="card-title">My Profile</h3>
                <p class="card-desc">Track your bonuses, balance, achievements, and monthly account status.</p>
            </a>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // Smooth 3D tilt effect on mouse movement for glass cards using Vanilla JS
        document.querySelectorAll('.glass-card').forEach(card => {
            card.addEventListener('mousemove', e => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                const rotateX = ((y - centerY) / centerY) * -5;
                const rotateY = ((x - centerX) / centerX) * 5;

                card.style.transform =
                    `translateY(-8px) perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.02)`;
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = `translateY(0) perspective(1000px) rotateX(0) rotateY(0) scale(1)`;
            });
        });
    </script>

    {{-- Typing Effect Script --}}
    <script>
        // Multiple Typing Texts in English
        const texts = [
            "Here is a quick look at your tasks and permissions today ✨",
            "Everything is ready to work and head towards new achievements 🚀",
            "The smart dashboard gives you a professional management experience ⚡",
            "Track orders, production, and accounts with total ease 🎯",
            "Start your day with faster management and stronger organization than ever 💎",
            "All work details are now right at your fingertips 🔥"
        ];

        const typingElement = document.getElementById("typing");

        let textIndex = 0;
        let charIndex = 0;
        let isDeleting = false;

        function typeEffect() {
            const currentText = texts[textIndex];

            if (!isDeleting) {
                typingElement.innerHTML = currentText.substring(0, charIndex + 1);
                charIndex++;

                if (charIndex === currentText.length) {
                    isDeleting = true;
                    setTimeout(typeEffect, 2200); // Wait duration after typing completely
                    return;
                }
            } else {
                typingElement.innerHTML = currentText.substring(0, charIndex - 1);
                charIndex--;

                if (charIndex === 0) {
                    isDeleting = false;
                    textIndex = (textIndex + 1) % texts.length;
                }
            }

            setTimeout(typeEffect, isDeleting ? 30 : 55);
        }

        window.addEventListener("load", () => {
            setTimeout(typeEffect, 700);
        });
    </script>
@endpush
