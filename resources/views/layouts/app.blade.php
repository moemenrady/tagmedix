<!DOCTYPE html>
<html id="htmlRoot" lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagmedix - @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        cairo: ['Cairo', 'sans-serif']
                    },
                    colors: {
                        primaryBg: '#EAE9DF',
                        secondaryCream: '#DBD0C3',
                        softBeige: '#CABCB0',
                        primaryBrown: '#926857',
                        darkText: '#2A2120',
                        mutedBrown: '#503E39',
                        roseAccent: '#C19077',
                        softPink: '#EAAD99',
                        white: '#FFFFFF'
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.4s ease-out forwards',
                        'slide-up': 'slideUp 0.5s ease-out forwards',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': {
                                opacity: '0'
                            },
                            '100%': {
                                opacity: '1'
                            }
                        },
                        slideUp: {
                            '0%': {
                                opacity: '0',
                                transform: 'translateY(15px)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateY(0)'
                            }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body.ar {
            font-family: 'Cairo', sans-serif;
        }

        body.en {
            font-family: 'Inter', sans-serif;
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #CABCB0;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #926857;
        }

        .page-transition {
            animation: fadeIn 0.4s ease-out;
        }

        .active-tab {
            background-color: #926857 !important;
            color: #FFFFFF !important;
            box-shadow: 0 4px 12px rgba(146, 104, 87, 0.25);
            transform: translateY(-1px);
        }

        .dropdown-enter {
            opacity: 0;
            transform: scale(0.95) translateY(10px);
            pointer-events: none;
        }

        .dropdown-enter-active {
            opacity: 1;
            transform: scale(1) translateY(0);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: auto;
        }

        .logo-container {
            width: 100%;
            height: 86px;
            background: #FFFFFF;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            border: 1px solid #DBD0C3;
            box-shadow: 0 4px 15px rgba(202, 188, 176, 0.3);
            position: relative;
            overflow: hidden;
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.4s ease;
        }

        .logo-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(234, 173, 153, 0.15), transparent);
            transform: skewX(-20deg);
            transition: left 0.7s ease;
        }

        .logo-container:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 25px rgba(193, 144, 119, 0.25);
            border-color: #C19077;
        }

        .logo-container:hover::before {
            left: 150%;
        }

        .logo-image {
            width: 100%;
            height: 68px;
            object-fit: contain;
            transition: transform 0.4s ease;
        }

        .nav-link {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-link:hover:not(.active-tab) {
            background-color: #DBD0C3;
            color: #2A2120;
            transform: translateX(-4px);
        }

        html[dir="ltr"] .nav-link:hover:not(.active-tab) {
            transform: translateX(4px);
        }

        .mobile-close-btn {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #926857;
            transition: all 0.2s ease;
        }

        .mobile-close-btn:hover {
            background: #EAAD99;
            color: #FFFFFF;
        }

        @media (min-width: 768px) {
            .logo-container {
                height: 92px;
                padding: 10px 20px;
                border-radius: 20px;
            }

            .logo-image {
                height: 74px;
            }

            .mobile-close-btn {
                display: none;
            }
        }

        @media (max-width: 767px) {
            .logo-container {
                height: 78px;
                border-radius: 16px;
                padding: 8px 14px;
            }

            .logo-image {
                height: 62px;
            }
        }

        /* =========================================
           Magnetic Floating Button Styling
        ========================================= */
        .floating-add-order-btn {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 9999;
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            /* Android Material You Style */
            background: #926857;
            color: #fff;
            border: 2px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 8px 25px rgba(146, 104, 87, 0.35), 0 2px 10px rgba(0, 0, 0, 0.1);
            cursor: grab;
            user-select: none;
            -webkit-user-select: none;
            touch-action: none;
            /* Magnetic Snap Transition */
            transition: left 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.15),
                top 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.15),
                background-color 0.3s ease,
                transform 0.3s ease,
                border-radius 0.3s ease;
            animation: floatingAddOrderIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .floating-add-order-btn i {
            font-size: 20px;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .floating-add-order-btn span {
            display: none;
        }

        .floating-add-order-btn:hover {
            background: #503E39;
            border-radius: 20px;
            box-shadow: 0 12px 30px rgba(146, 104, 87, 0.45), 0 4px 15px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .floating-add-order-btn:hover i {
            transform: rotate(90deg) scale(1.1);
        }

        .floating-add-order-btn:active {
            cursor: grabbing;
            transform: scale(0.92);
        }

        .floating-add-order-btn.is-dragging {
            cursor: grabbing;
            transition: none !important;
            /* Disable transition during drag for smoothness */
            transform: scale(1.05);
            border-radius: 50%;
            /* Becomes circle when dragged */
            box-shadow: 0 20px 40px rgba(146, 104, 87, 0.5), 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        @keyframes floatingAddOrderIn {
            from {
                opacity: 0;
                transform: scale(0.5) translateY(20px) rotate(-45deg);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0) rotate(0);
            }
        }

        @media (max-width: 767px) {
            .floating-add-order-btn {
                width: 52px;
                height: 52px;
                right: 16px;
                bottom: calc(20px + env(safe-area-inset-bottom));
                border-radius: 14px;
            }
        }

        /* =========================================
   Pull-to-Refresh Styling
========================================= */
        /* إيقاف سحب المتصفح الافتراضي لمنع التداخل مع الأنيميشن الخاص بنا */
        body {
            overscroll-behavior-y: none;
        }

        .ptr-indicator {
            position: fixed;
            top: 0;
            left: 50%;
            z-index: 9999;
            width: 48px;
            height: 48px;
            background: #FFFFFF;
            border: 2px solid #DBD0C3;
            /* secondaryCream */
            border-radius: 50%;
            box-shadow: 0 8px 25px rgba(146, 104, 87, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            /* يبدأ مختفياً أعلى الشاشة */
            transform: translate(-50%, -150%);
            pointer-events: none;
            will-change: transform;
        }

        .ptr-indicator i {
            color: #926857;
            /* primaryBrown */
            font-size: 20px;
            will-change: transform;
        }

        /* حالة التحديث مع أنيميشن النبض والدوران */
        .ptr-indicator.is-refreshing {
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), border-color 0.3s;
            transform: translate(-50%, 90px) !important;
            border-color: #C19077;
            /* roseAccent */
        }

        .ptr-indicator.is-refreshing i {
            animation: ptr-spin 0.7s linear infinite;
            color: #C19077;
        }

        /* حالة الإلغاء وعودة المؤشر لأعلى بمرونة */
        .ptr-resetting {
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes ptr-spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="bg-primaryBg text-darkText font-sans flex h-screen overflow-hidden">
    <div id="ptrIndicator" class="ptr-indicator">
        <i class="fa-solid fa-rotate-right" id="ptrIcon"></i>
    </div>
    <!-- Mobile Overlay -->
    <div id="mobileOverlay"
        class="fixed inset-0 bg-darkText/50 backdrop-blur-sm z-40 hidden md:hidden transition-opacity opacity-0 duration-300">
    </div>

    <!-- Sidebar -->
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white flex flex-col transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out h-full border-l border-secondaryCream shadow-2xl md:shadow-[4px_0_24px_rgba(202,188,176,0.15)] shrink-0">

        <button id="desktopSidebarToggle"
            class="hidden md:flex absolute top-1/2 -end-4 -translate-y-1/2 z-50 bg-white border border-secondaryCream rounded-full w-8 h-8 items-center justify-center text-primaryBrown shadow-md hover:bg-secondaryCream hover:scale-110 transition-all duration-300">
            <span id="sidebarIconWrapper" class="transition-transform duration-300 flex">
                <i class="fa-solid fa-chevron-left rtl:rotate-180"></i>
            </span>
        </button>

        <div class="p-5 flex items-center justify-between">
            <div class="w-full">
                <div class="logo-container">
                    <img src="{{ asset('t.webp') }}" alt="TAGMEDIX" class="logo-image">
                </div>
            </div>
            <button id="closeSidebarBtn" class="mobile-close-btn mr-2">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-4 space-y-1.5 text-sm font-medium pb-4">
            <div class="space-y-1">

                {{-- الطلبات التامة --}}
                <a href="{{ route('finished-goods.index') }}"
                    class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-mutedBrown
        {{ request()->routeIs('finished-goods.*') ? 'active-tab' : '' }}">

                    <i class="fa-solid fa-box-open w-5 transition-transform group-hover:scale-110"></i>

                    <span data-ar="الطلبات التامة" data-en="Finished Goods">
                        الطلبات التامة
                    </span>
                </a>

                {{-- Sub Tab: أوردرات تم شحنها --}}
                <a href="{{ route('shipments.index') }}"
                    class="nav-link flex items-center gap-3 px-4 py-2.5 mt-1
        ml-4 rtl:ml-0 rtl:mr-4 rounded-lg text-sm
        border-r-2 rtl:border-r-2 rtl:border-l-0
        {{ request()->routeIs('shipments.*')
            ? 'bg-secondaryCream text-primaryBrown font-bold border-primaryBrown'
            : 'text-mutedBrown/80 border-transparent hover:bg-secondaryCream/60' }}">

                    <i class="fa-solid fa-truck-fast w-4"></i>

                    <span data-ar="أوردرات تم شحنها" data-en="Shipped Orders">
                        أوردرات تم شحنها
                    </span>
                </a>

            </div>
            @if (auth()->user() && auth()->user()->role == 'admin')
                <!-- باقي الروابط كما هي في الكود الأصلي -->
                <a href="{{ route('inventory.dashboard') }}"
                    class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-mutedBrown {{ request()->routeIs('inventory.*') ? 'active-tab' : '' }}">
                    <i class="fa-solid fa-boxes-stacked w-5"></i>
                    <span data-ar="المخزون" data-en="Inventory">المخزون</span>
                </a>

                <a href="{{ route('inventory-transactions.index') }}"
                    class="nav-link flex items-center gap-3 px-4 py-2 mt-1 ml-4 rtl:ml-0 rtl:mr-4 rounded-lg text-sm {{ request()->routeIs('inventory.transactions.*') ? 'text-primaryBrown font-bold' : 'text-mutedBrown/80' }}">
                    <i class="fa-solid fa-arrow-right-arrow-left w-4"></i>
                    <span data-ar="حركات المخزون" data-en="Inventory Transactions">حركات المخزون</span>
                </a>
                <a href="{{ route('customers.index') }}"
                    class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-mutedBrown {{ request()->routeIs('customers.*') ? 'active-tab' : '' }}">

                    <i class="fa-solid fa-users w-5"></i>

                    <span data-ar="العملاء" data-en="Customers">
                        العملاء
                    </span>
                </a>
            @endif
            <a href="{{ route('orders.index') }}"
                class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-mutedBrown {{ request()->routeIs('orders.*') ? 'active-tab' : '' }}">
                <i class="fa-solid fa-clipboard-list w-5"></i>
                <span data-ar="الطلبات" data-en="Orders">الطلبات</span>
            </a>

            <a href="{{ route('turbo.index') }}"
                class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-mutedBrown {{ request()->routeIs('turbo.*') ? 'active-tab' : '' }}">
                <i class="fa-solid fa-truck-fast w-5 text-roseAccent"></i>
                <span data-ar="مركز عمليات Turbo" data-en="Turbo Ops Center">مركز عمليات Turbo</span>
            </a>
            <a href="{{ route('production.workflow.index') }}"
                class="nav-link flex items-center justify-between px-4 py-3 rounded-xl text-mutedBrown {{ request()->routeIs('production.*') ? 'active-tab' : '' }}">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-industry w-5"></i>
                    <span data-ar="مسار عمل الطلبات" data-en="Orders Work Flow">مسار عمل الطلبات</span>
                </div>
                <i class="fa-solid fa-chevron-left text-[10px] rtl:rotate-180"></i>
            </a>

            @if (auth()->user() && auth()->user()->role == 'admin')
                <div class="h-px bg-secondaryCream my-3 w-4/5 mx-auto"></div>
                <a href="{{ route('dashboard') }}"
                    class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-mutedBrown {{ request()->routeIs('dashboard') ? 'active-tab' : '' }}">
                    <i class="fa-solid fa-chart-line w-5"></i>
                    <span data-ar="لوحة التحكم" data-en="Dashboard">لوحة التحكم</span>
                </a>
                <a href="{{ route('payments.index') }}"
                    class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-mutedBrown {{ request()->routeIs('payments.*') ? 'active-tab' : '' }}">
                    <i class="fa-regular fa-credit-card w-5"></i>
                    <span data-ar="الدفعات" data-en="Payments">الدفعات</span>
                </a>
                <a href="{{ route('suppliers.index') }}"
                    class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-mutedBrown {{ request()->routeIs('suppliers.*') ? 'active-tab' : '' }}">
                    <i class="fa-solid fa-truck-field w-5"></i>
                    <span data-ar="الموردون" data-en="Suppliers">الموردون</span>
                </a>
                <a href="{{ route('users.index') }}"
                    class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-mutedBrown {{ request()->routeIs('users.*') ? 'active-tab' : '' }}">
                    <i class="fa-regular fa-user w-5"></i>
                    <span data-ar="المستخدمون" data-en="Users">المستخدمون</span>
                </a>
                <a href="{{ route('employees.index') }}"
                    class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-mutedBrown {{ request()->routeIs('employees.*') ? 'active-tab' : '' }}">
                    <i class="fa-solid fa-users w-5"></i>
                    <span data-ar="الموظفون" data-en="Employees">الموظفون</span>
                </a>
                <a href="{{ route('payroll.index') }}"
                    class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-mutedBrown {{ request()->routeIs('payroll.*') ? 'active-tab' : '' }}">
                    <i class="fa-solid fa-file-invoice-dollar w-5"></i>
                    <span data-ar="الرواتب" data-en="Payroll">الرواتب</span>
                </a>
                <a href="{{ route('reports.index') }}"
                    class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-mutedBrown {{ request()->routeIs('reports.*') ? 'active-tab' : '' }}">
                    <i class="fa-solid fa-chart-bar w-5"></i>
                    <span data-ar="التقارير" data-en="Reports">التقارير</span>
                </a>
            @endif

            <div class="h-px bg-secondaryCream my-3 w-4/5 mx-auto"></div>
            <a href="{{ route('returns.index') }}"
                class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-mutedBrown {{ request()->routeIs('returns.*') ? 'active-tab' : '' }}">
                <i class="fa-solid fa-rotate-left w-5"></i>
                <span data-ar="المرتجعات" data-en="Returns">المرتجعات</span>
            </a>
            <a href="{{ route('order_classifications.index') }}"
                class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-mutedBrown {{ request()->routeIs('order_classifications.*') ? 'active-tab' : '' }}">
                <i class="fa-solid fa-tags w-5"></i>
                <span data-ar="تصنيفات الطلبات" data-en="Order Classifications">تصنيفات الطلبات</span>
            </a>
            <a href="{{ route('settings.index') }}"
                class="nav-link flex items-center gap-3 px-4 py-3 rounded-xl text-mutedBrown {{ request()->routeIs('settings.*') ? 'active-tab' : '' }}">
                <i class="fa-solid fa-gear w-5"></i>
                <span data-ar="الإعدادات" data-en="Settings">الإعدادات</span>
            </a>
        </nav>

        @php $user = auth()->user(); @endphp
        <div class="p-4 mt-auto border-t border-secondaryCream relative bg-white">
            <div id="profileDropdownBtn"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-secondaryCream cursor-pointer transition-all duration-300 select-none">
                <div
                    class="w-10 h-10 rounded-full bg-softBeige flex items-center justify-center text-primaryBrown font-bold text-sm shrink-0 border-2 border-white shadow-sm">
                    @if ($user && $user->name)
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @else
                        <i class="fa-solid fa-user"></i>
                    @endif
                </div>
                @php
                    $roles = ['admin' => ['ar' => 'مدير النظام', 'en' => 'Administrator']];
                    $role = $roles[$user->role ?? 'admin'] ?? ['ar' => 'مدير', 'en' => 'Manager'];
                @endphp
                <div class="flex-1 overflow-hidden">
                    <p class="text-darkText text-sm font-bold truncate">{{ $user->name ?? 'اسم المستخدم' }}</p>
                    <p class="text-[11px] text-primaryBrown truncate font-medium" data-ar="{{ $role['ar'] }}"
                        data-en="{{ $role['en'] }}">{{ $role['ar'] }}</p>
                </div>
                <i class="fa-solid fa-chevron-up text-mutedBrown text-xs transition-transform duration-300"
                    id="profileChevron"></i>
            </div>

            <div id="profileDropdown"
                class="absolute bottom-[85px] left-4 right-4 bg-white border border-secondaryCream rounded-xl shadow-[0_10px_40px_rgba(146,104,87,0.15)] overflow-hidden hidden dropdown-enter z-50">
                <div class="p-2 space-y-1">
                    <div class="px-3 py-3 border-b border-secondaryCream bg-primaryBg/50">
                        <p class="text-darkText text-sm font-bold">{{ $user->name ?? 'اسم المستخدم' }}</p>
                        <p class="text-xs text-mutedBrown mt-0.5">{{ $user->email ?? 'user@tagmedix.com' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="pt-1">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-3 px-3 py-2.5 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors text-left rtl:text-right font-medium">
                            <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center"></i>
                            <span data-ar="تسجيل الخروج" data-en="Logout">تسجيل الخروج</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden w-full bg-primaryBg relative z-0">
        <div
            class="absolute top-0 right-0 w-[500px] h-[500px] bg-white opacity-40 rounded-full blur-[100px] -z-10 pointer-events-none">
        </div>
        <div
            class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-softPink opacity-20 rounded-full blur-[120px] -z-10 pointer-events-none">
        </div>

        @unless(View::hasSection('hide_header'))
        <div id="headerWrapper"
            class="sticky top-0 z-30 transition-transform duration-500 ease-in-out w-full shrink-0">
            <header
                class="bg-white/80 backdrop-blur-lg border-b border-secondaryCream p-4 flex justify-between items-center shadow-sm relative">
                <div class="flex items-center gap-4">
                    <button id="openSidebarBtn"
                        class="md:hidden text-primaryBrown hover:text-darkText focus:outline-none p-2 rounded-lg hover:bg-secondaryCream transition-colors">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-darkText" data-ar="لوحة التحكم" data-en="Dashboard">لوحة
                            التحكم</h1>
                        <p class="text-sm text-mutedBrown hidden sm:block mt-0.5"
                            data-ar="مرحباً بك في نظام إدارة تاجميديكس" data-en="Welcome to Tagmedix System">مرحباً بك
                            في نظام إدارة تاجميديكس</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 md:gap-4">
                    <button
                        class="flex items-center gap-2 bg-white border border-secondaryCream shadow-sm rounded-xl px-3 py-1.5 md:px-4 md:py-2 text-xs md:text-sm font-semibold text-darkText hover:bg-secondaryCream transition-all transform hover:-translate-y-0.5">
                        <span class="hidden sm:inline" data-ar="اليوم" data-en="Today">اليوم</span>
                        <i class="fa-regular fa-calendar sm:mx-1 text-primaryBrown"></i>
                    </button>
                    <button id="languageBtn"
                        class="bg-white border border-secondaryCream shadow-sm rounded-xl px-3 py-1.5 md:px-4 md:py-2 text-xs md:text-sm font-semibold text-darkText transition-all hover:bg-secondaryCream hover:-translate-y-0.5">
                        🇪🇬 العربية
                    </button>
                    <button
                        class="relative p-2 md:p-2.5 bg-white border border-secondaryCream shadow-sm rounded-xl text-primaryBrown hover:text-darkText hover:bg-secondaryCream transition-all transform hover:-translate-y-0.5">
                        <i class="fa-regular fa-bell text-lg md:text-xl"></i>
                        <span
                            class="absolute top-2 right-2 w-2 h-2 md:w-2.5 md:h-2.5 bg-roseAccent rounded-full border-2 border-white animate-pulse"></span>
                    </button>
                </div>
            </header>

            <button id="headerToggleBtn"
                class="absolute -bottom-8 left-1/2 -translate-x-1/2 z-40 bg-white border border-secondaryCream rounded-full w-8 h-8 flex items-center justify-center text-primaryBrown shadow-md hover:bg-secondaryCream hover:scale-110 transition-all duration-300">
                <i class="fa-solid fa-chevron-up transition-transform duration-300" id="headerArrowIcon"></i>
            </button>
        </div>
        @endunless

        <div id="mainScrollArea" class="flex-1 overflow-y-auto p-4 md:p-6 space-y-6 page-transition relative z-10 flex flex-col">
            <div class="flex-1">
                @yield('content')
            </div>

            <div class="mt-8 pt-4 border-t border-secondaryCream/40 text-center w-full shrink-0">
                <p class="text-xs text-mutedBrown font-medium flex items-center justify-center gap-1.5">
                    <span data-ar="تم تطوير النظام بواسطة" data-en="System developed by">تم تطوير النظام بواسطة</span>
                    <span class="font-bold text-primaryBrown hover:text-roseAccent transition-colors cursor-default">
                        <i class="fa-solid fa-code text-[10px] mr-0.5"></i> Moemen Team
                    </span>
                </p>
            </div>
        </div>
    </main>

    <!-- Magnetic Floating Button -->
    <a href="{{ route('orders.create') }}" id="floatingAddOrderBtn" class="floating-add-order-btn"
        title="إضافة طلب جديد" aria-label="إضافة طلب جديد">
        <i class="fa-solid fa-plus"></i>
        <span data-ar="إضافة طلب جديد" data-en="Add New Order">إضافة طلب جديد</span>
    </a>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar Layout Logic
            const desktopSidebarToggle = document.getElementById('desktopSidebarToggle');
            const sidebarIconWrapper = document.getElementById('sidebarIconWrapper');
            const sidebar = document.getElementById('sidebar');

            if (desktopSidebarToggle) {
                desktopSidebarToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('md:-ms-64');
                    sidebarIconWrapper.classList.toggle('rotate-180');
                });
            }

            // Header Toggle Logic
            const headerToggleBtn = document.getElementById('headerToggleBtn');
            const headerWrapper = document.getElementById('headerWrapper');
            const headerArrowIcon = document.getElementById('headerArrowIcon');

            if (headerToggleBtn) {
                headerToggleBtn.addEventListener('click', () => {
                    headerWrapper.classList.toggle('-translate-y-full');
                    headerArrowIcon.classList.toggle('rotate-180');
                });
            }

            // Mobile Sidebar Logic
            const openBtn = document.getElementById('openSidebarBtn');
            const closeBtn = document.getElementById('closeSidebarBtn');
            const overlay = document.getElementById('mobileOverlay');

            function toggleSidebar() {
                const isOpen = !sidebar.classList.contains('-translate-x-full');
                if (isOpen) {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.remove('opacity-100');
                    overlay.classList.add('opacity-0');
                    setTimeout(() => overlay.classList.add('hidden'), 300);
                } else {
                    overlay.classList.remove('hidden');
                    setTimeout(() => {
                        sidebar.classList.remove('-translate-x-full');
                        overlay.classList.remove('opacity-0');
                        overlay.classList.add('opacity-100');
                    }, 10);
                }
            }

            openBtn.addEventListener('click', toggleSidebar);
            closeBtn.addEventListener('click', toggleSidebar);
            overlay.addEventListener('click', toggleSidebar);

            // Profile Dropdown
            const profileBtn = document.getElementById('profileDropdownBtn');
            const profileDropdown = document.getElementById('profileDropdown');
            const profileChevron = document.getElementById('profileChevron');

            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (profileDropdown.classList.contains('hidden')) {
                    profileDropdown.classList.remove('hidden');
                    setTimeout(() => {
                        profileDropdown.classList.remove('dropdown-enter');
                        profileDropdown.classList.add('dropdown-enter-active');
                        profileChevron.classList.add('rotate-180');
                    }, 10);
                } else {
                    profileDropdown.classList.add('dropdown-enter');
                    profileDropdown.classList.remove('dropdown-enter-active');
                    profileChevron.classList.remove('rotate-180');
                    setTimeout(() => profileDropdown.classList.add('hidden'), 250);
                }
            });

            document.addEventListener('click', function(e) {
                if (!profileDropdown.contains(e.target) && !profileBtn.contains(e.target) && !
                    profileDropdown.classList.contains('hidden')) {
                    profileDropdown.classList.add('dropdown-enter');
                    profileDropdown.classList.remove('dropdown-enter-active');
                    profileChevron.classList.remove('rotate-180');
                    setTimeout(() => profileDropdown.classList.add('hidden'), 250);
                }
            });

            // =========================================
            // Magnetic Floating Button Logic
            // =========================================
            const floatBtn = document.getElementById('floatingAddOrderBtn');
            if (floatBtn) {
                let isDragging = false;
                let moved = false;
                let startX = 0,
                    startY = 0,
                    startLeft = 0,
                    startTop = 0;
                const DRAG_THRESHOLD = 5;

                function getPoint(e) {
                    return e.touches ? {
                        x: e.touches[0].clientX,
                        y: e.touches[0].clientY
                    } : {
                        x: e.clientX,
                        y: e.clientY
                    };
                }

                function startDrag(e) {
                    const point = getPoint(e);
                    const rect = floatBtn.getBoundingClientRect();
                    startX = point.x;
                    startY = point.y;
                    startLeft = rect.left;
                    startTop = rect.top;
                    moved = false;
                    isDragging = true;

                    // تحويل الإحداثيات من Right/Bottom إلى Left/Top لسهولة الحساب أثناء السحب
                    floatBtn.style.left = `${startLeft}px`;
                    floatBtn.style.top = `${startTop}px`;
                    floatBtn.style.right = 'auto';
                    floatBtn.style.bottom = 'auto';

                    floatBtn.classList.add('is-dragging');
                }

                function moveDrag(e) {
                    if (!isDragging) return;
                    const point = getPoint(e);
                    const deltaX = point.x - startX;
                    const deltaY = point.y - startY;

                    if (Math.abs(deltaX) > DRAG_THRESHOLD || Math.abs(deltaY) > DRAG_THRESHOLD) {
                        moved = true;
                    }

                    floatBtn.style.left = `${startLeft + deltaX}px`;
                    floatBtn.style.top = `${startTop + deltaY}px`;

                    if (e.cancelable) e.preventDefault();
                }

                function endDrag() {
                    if (!isDragging) return;
                    isDragging = false;
                    floatBtn.classList.remove('is-dragging');

                    if (moved) {
                        snapToEdge();
                    }
                }

                // دالة الالتصاق المغناطيسي للحواف
                function snapToEdge() {
                    const rect = floatBtn.getBoundingClientRect();
                    const windowWidth = window.innerWidth;
                    const windowHeight = window.innerHeight;
                    const padding = windowWidth < 768 ? 16 : 24; // مسافة الأمان للحواف

                    let finalLeft, finalTop;
                    const buttonCenterX = rect.left + (rect.width / 2);

                    // الالتصاق يميناً أو يساراً بناءً على منتصف الشاشة
                    if (buttonCenterX < windowWidth / 2) {
                        finalLeft = padding; // الالتصاق باليسار
                    } else {
                        finalLeft = windowWidth - rect.width - padding; // الالتصاق باليمين
                    }

                    // الحفاظ على الزر داخل إطار الشاشة عمودياً
                    finalTop = rect.top;
                    if (finalTop < padding) finalTop = padding;
                    if (finalTop > windowHeight - rect.height - padding) finalTop = windowHeight - rect.height -
                        padding;

                    // تطبيق الإحداثيات النهائية ليتم تحريكها بنعومة (بسبب transition في CSS)
                    floatBtn.style.left = `${finalLeft}px`;
                    floatBtn.style.top = `${finalTop}px`;

                    localStorage.setItem('floatingBtnPos', JSON.stringify({
                        left: finalLeft,
                        top: finalTop
                    }));
                }

                // Events
                floatBtn.addEventListener('mousedown', startDrag);
                document.addEventListener('mousemove', moveDrag);
                document.addEventListener('mouseup', endDrag);

                floatBtn.addEventListener('touchstart', startDrag, {
                    passive: false
                });
                document.addEventListener('touchmove', moveDrag, {
                    passive: false
                });
                document.addEventListener('touchend', endDrag);

                floatBtn.addEventListener('click', function(e) {
                    if (moved) {
                        e.preventDefault();
                        setTimeout(() => moved = false, 50);
                    }
                });

                // استعادة الموقع عند التحميل
                try {
                    const saved = JSON.parse(localStorage.getItem('floatingBtnPos'));
                    if (saved && typeof saved.left === 'number') {
                        floatBtn.style.left = `${saved.left}px`;
                        floatBtn.style.top = `${saved.top}px`;
                        floatBtn.style.right = 'auto';
                        floatBtn.style.bottom = 'auto';
                        // إعادة الحساب للتأكد من عدم خروجه من الشاشة بعد تغيير حجمها
                        setTimeout(snapToEdge, 100);
                    }
                } catch (e) {}

                // إعادة الحساب عند تغيير حجم الشاشة (دوران الموبايل أو تصغير المتصفح)
                window.addEventListener('resize', () => {
                    setTimeout(snapToEdge, 200);
                });
            }
        });

        // Language Script
        const html = document.getElementById("htmlRoot");
        const langBtn = document.getElementById("languageBtn");

        function applyLanguage(lang) {
            localStorage.setItem("lang", lang);
            html.lang = lang;
            html.dir = lang === "ar" ? "rtl" : "ltr";
            document.body.classList.remove("ar", "en");
            document.body.classList.add(lang);
            document.querySelectorAll("[data-ar]").forEach(el => {
                el.innerHTML = lang === "ar" ? el.dataset.ar : el.dataset.en;
            });
            langBtn.innerHTML = lang === "ar" ? "🇪🇬 العربية" : "🇺🇸 English";
        }

        const savedLang = localStorage.getItem("lang") || "ar";
        applyLanguage(savedLang);

        langBtn.onclick = () => {
            const current = localStorage.getItem("lang");
            applyLanguage(current === "ar" ? "en" : "ar");
        }
    </script>
    {{-- انيماشن الريفرش --}}
    <script>
        // =========================================
        // Pull-to-Refresh Logic (Mobile & Desktop)
        // =========================================
        const ptrIndicator = document.getElementById('ptrIndicator');
        const ptrIcon = document.getElementById('ptrIcon');
        const scrollArea = document.getElementById('mainScrollArea');

        if (ptrIndicator && scrollArea) {
            let startY = 0;
            let currentY = 0;
            let isDragging = false;
            let isRefreshing = false;
            const triggerThreshold = 85; // المسافة اللازمة لتفعيل التحديث (بيكسل)
            const resistance = 0.45; // مقاومة السحب لإعطاء إحساس بالوزن والواقعية

            // توحيد قراءة إحداثيات اللمس أو الماوس
            function getEventY(e) {
                return e.touches ? e.touches[0].clientY : e.clientY;
            }

            function handlePtrStart(e) {
                // نمنع بدء السحب إذا كنا نقوم بالتحديث حالياً، أو إذا لم نكن في أعلى الصفحة تماماً
                if (isRefreshing || scrollArea.scrollTop > 5) return;

                startY = getEventY(e);
                isDragging = true;
                ptrIndicator.classList.remove('ptr-resetting');
            }

            function handlePtrMove(e) {
                if (!isDragging) return;

                // إذا تحرك المستخدم للأسفل بالشريط أثناء السحب، نلغي العملية
                if (scrollArea.scrollTop > 5) {
                    isDragging = false;
                    handlePtrEnd();
                    return;
                }

                currentY = getEventY(e);
                const distance = currentY - startY;

                // إذا كان السحب للأسفل
                if (distance > 0) {
                    const pullDistance = Math.min(distance * resistance, triggerThreshold + 30);

                    // تحريك المؤشر وتدوير الأيقونة بشكل متناسب مع قوة السحب
                    ptrIndicator.style.transform = `translate(-50%, calc(-100% + ${pullDistance}px))`;
                    ptrIcon.style.transform = `rotate(${pullDistance * 2.5}deg)`;

                    // منع التحديد الافتراضي للماوس أثناء السحب
                    if (e.cancelable && !e.touches) {
                        e.preventDefault();
                    }
                }
            }

            function handlePtrEnd() {
                if (!isDragging) return;
                isDragging = false;

                const distance = currentY - startY;
                const pullDistance = distance * resistance;

                if (pullDistance >= triggerThreshold) {
                    // تفعيل التحديث
                    isRefreshing = true;
                    ptrIndicator.classList.add('is-refreshing');
                    ptrIndicator.style.transform = ''; // يتم التحكم فيه بواسطة الـ CSS الآن
                    ptrIcon.style.transform = '';

                    // محاكاة الأنيماشن لثواني قبل إعادة تحميل الصفحة بسلاسة
                    setTimeout(() => {
                        window.location.reload();
                    }, 600);
                } else {
                    // التراجع (إلغاء التحديث) لإنه لم يصل للحد المطلوب
                    ptrIndicator.classList.add('ptr-resetting');
                    ptrIndicator.style.transform = 'translate(-50%, -150%)';
                }

                startY = 0;
                currentY = 0;
            }

            // دعم أجهزة اللمس (الموبايل/التابلت)
            scrollArea.addEventListener('touchstart', handlePtrStart, {
                passive: true
            });
            scrollArea.addEventListener('touchmove', handlePtrMove, {
                passive: false
            });
            scrollArea.addEventListener('touchend', handlePtrEnd);

            // دعم الماوس (الديسكتوب)
            scrollArea.addEventListener('mousedown', handlePtrStart);
            document.addEventListener('mousemove', handlePtrMove);
            document.addEventListener('mouseup', handlePtrEnd);
        }
    </script>
    @stack('scripts')
</body>

</html>
