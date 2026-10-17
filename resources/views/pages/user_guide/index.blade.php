@extends('layouts.app')

@section('title', 'دليل الاستخدام الشامل للميزات الجديدة')
@section('page_title', 'دليل الاستخدام التفاعلي - نظام تاجميديكس')

@section('content')
<style>
    /* أنماط وتنسيقات مخصصة لصفحة دليل الاستخدام */
    .guide-accordion-header {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .guide-accordion-header:hover {
        background-color: rgba(219, 208, 195, 0.25);
    }
    .guide-accordion-header.is-open {
        background-color: rgba(146, 104, 87, 0.06);
        border-bottom-color: rgba(219, 208, 195, 0.8);
    }
    .guide-chevron {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .guide-accordion-header.is-open .guide-chevron {
        transform: rotate(180deg);
        color: #926857;
    }
    .badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.85rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        transition: all 0.2s ease;
    }
    .step-number {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: #926857;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.875rem;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(146, 104, 87, 0.3);
    }
    .flow-connector {
        position: relative;
    }
    .flow-connector::after {
        content: '';
        position: absolute;
        top: 50%;
        left: -12px;
        width: 24px;
        height: 2px;
        background: #DBD0C3;
        z-index: 1;
    }
    @media (max-width: 768px) {
        .flow-connector::after {
            display: none;
        }
    }
    @media print {
        #sidebar, #headerWrapper, .no-print, .filter-pills, .search-container, #desktopSidebarToggle {
            display: none !important;
        }
        .guide-accordion-content {
            display: block !important;
            max-height: none !important;
            opacity: 1 !important;
        }
        body, main {
            background: white !important;
            overflow: visible !important;
            height: auto !important;
        }
    }
</style>

<div class="space-y-8 pb-16 font-sans w-full max-w-7xl mx-auto" id="userGuideApp">

    <!-- Hero Banner Card -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#2A2120] via-[#503E39] to-[#926857] text-white p-6 sm:p-10 shadow-xl border border-[#DBD0C3]/30">
        <div class="absolute -right-16 -top-16 w-72 h-72 bg-[#C19077]/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute left-10 -bottom-16 w-60 h-60 bg-[#EAAD99]/15 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3 max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-[#DBD0C3] text-xs font-semibold backdrop-blur-md border border-white/15">
                    <i class="fa-solid fa-graduation-cap text-[#EAAD99]"></i>
                    <span>دليل الاستخدام والتشغيل الرسمي | Tagmedix Operations Manual</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-white leading-tight">
                    دليل تشغيل الميزات الجديدة في نظام تاجميديكس
                </h1>
                <p class="text-sm sm:text-base text-[#DBD0C3]/90 leading-relaxed font-normal">
                    شرح تفاعلي دقيق ومفصل بنظام (سؤال وجواب) يوضح لك خطوة بخطوة كيفية استخدام فواتير الشراء، إدارة المرتجعات وفحص الجودة، مسير الرواتب الآلي للترزية، سلف الموظفين، وشحن توربو، وكيف تتناغم كل هذه الأجزاء لتشكل دورة عمل متكاملة بدون أي أخطاء.
                </p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-3 no-print">
                <button type="button" onclick="expandAllQuestions()" 
                    class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 border border-white/20 hover:scale-105 active:scale-95 shadow-sm">
                    <i class="fa-solid fa-angles-down text-[#EAAD99]"></i>
                    <span>توسيع جميع الأسئلة</span>
                </button>
                <button type="button" onclick="collapseAllQuestions()" 
                    class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 border border-white/20 hover:scale-105 active:scale-95 shadow-sm">
                    <i class="fa-solid fa-angles-up text-[#EAAD99]"></i>
                    <span>طي الكل</span>
                </button>
                <button type="button" onclick="window.print()" 
                    class="px-4 py-2.5 bg-[#C19077] hover:bg-[#a6745d] text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-lg hover:scale-105 active:scale-95">
                    <i class="fa-solid fa-print"></i>
                    <span>طباعة الدليل</span>
                </button>
            </div>
        </div>

        <!-- Quick Summary Stats Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-8 pt-6 border-t border-white/15">
            <div class="bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/10">
                <p class="text-[11px] text-[#DBD0C3] font-medium">عدد الميزات المشروحة</p>
                <p class="text-xl sm:text-2xl font-black text-white mt-0.5">6 ميزات رئيسية</p>
            </div>
            <div class="bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/10">
                <p class="text-[11px] text-[#DBD0C3] font-medium">إجمالي التساؤلات والردود</p>
                <p class="text-xl sm:text-2xl font-black text-[#EAAD99] mt-0.5" id="totalQuestionsCount">21 سؤالاً مفصلاً</p>
            </div>
            <div class="bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/10">
                <p class="text-[11px] text-[#DBD0C3] font-medium">دورة العمل (Ecosystem)</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-400 mt-0.5">تكامل آلي 100%</p>
            </div>
            <div class="bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/10">
                <p class="text-[11px] text-[#DBD0C3] font-medium">دقة البيانات المالية</p>
                <p class="text-xl sm:text-2xl font-black text-amber-300 mt-0.5">صفر تكرار يدوي</p>
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white p-5 rounded-3xl border border-secondaryCream shadow-sm space-y-4 no-print">
        <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
            <!-- Live Search Bar -->
            <div class="relative w-full md:w-1/2">
                <i class="fa-solid fa-magnifying-glass absolute right-4 top-1/2 -translate-y-1/2 text-mutedBrown"></i>
                <input type="text" id="guideSearchInput" oninput="filterQuestions()" placeholder="ابحث في الأسئلة والشروحات (مثال: فاتورة شراء، سلفة، ترزي، مرتجع، توربو، بالقطعة...)"
                    class="w-full pr-11 pl-10 py-3 rounded-2xl border border-secondaryCream bg-primaryBg/30 focus:bg-white text-sm font-medium text-darkText outline-none focus:ring-2 focus:ring-primaryBrown transition">
                <button type="button" id="clearSearchBtn" onclick="clearSearch()" class="hidden absolute left-3 top-1/2 -translate-y-1/2 text-mutedBrown hover:text-darkText p-1.5 rounded-lg text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Matches Status -->
            <div class="text-xs text-mutedBrown font-bold flex items-center gap-2 self-start md:self-center">
                <i class="fa-solid fa-filter text-primaryBrown"></i>
                <span>يتم عرض: <strong id="visibleCount" class="text-primaryBrown">21</strong> سؤال</span>
            </div>
        </div>

        <!-- Category Filter Pills -->
        <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-secondaryCream/40" id="categoryFilterContainer">
            <button type="button" onclick="selectCategory('all', this)" class="badge-pill bg-primaryBrown text-white shadow-sm active-cat-btn">
                <i class="fa-solid fa-layer-group text-xs"></i>
                <span>الكل (عرض شامل)</span>
            </button>
            <button type="button" onclick="selectCategory('general', this)" class="badge-pill bg-secondaryCream/60 hover:bg-secondaryCream text-darkText">
                <i class="fa-solid fa-diagram-project text-xs text-primaryBrown"></i>
                <span>التكامل العام وتناغم النظام ⭐</span>
            </button>
            <button type="button" onclick="selectCategory('purchases', this)" class="badge-pill bg-secondaryCream/60 hover:bg-secondaryCream text-darkText">
                <i class="fa-solid fa-cart-flatbed text-xs text-primaryBrown"></i>
                <span>فواتير الشراء والمخزون</span>
            </button>
            <button type="button" onclick="selectCategory('returns', this)" class="badge-pill bg-secondaryCream/60 hover:bg-secondaryCream text-darkText">
                <i class="fa-solid fa-rotate-left text-xs text-primaryBrown"></i>
                <span>المرتجعات وفحص الجودة (QC)</span>
            </button>
            <button type="button" onclick="selectCategory('payroll', this)" class="badge-pill bg-secondaryCream/60 hover:bg-secondaryCream text-darkText">
                <i class="fa-solid fa-file-invoice-dollar text-xs text-primaryBrown"></i>
                <span>الرواتب وحساب القطعة</span>
            </button>
            <button type="button" onclick="selectCategory('advances', this)" class="badge-pill bg-secondaryCream/60 hover:bg-secondaryCream text-darkText">
                <i class="fa-solid fa-hand-holding-dollar text-xs text-primaryBrown"></i>
                <span>السلف والمكافآت</span>
            </button>
            <button type="button" onclick="selectCategory('turbo', this)" class="badge-pill bg-secondaryCream/60 hover:bg-secondaryCream text-darkText">
                <i class="fa-solid fa-truck-fast text-xs text-primaryBrown"></i>
                <span>الشحن وشركة Turbo</span>
            </button>
            <button type="button" onclick="selectCategory('production', this)" class="badge-pill bg-secondaryCream/60 hover:bg-secondaryCream text-darkText">
                <i class="fa-solid fa-industry text-xs text-primaryBrown"></i>
                <span>مسار الإنتاج والتصنيفات</span>
            </button>
        </div>
    </div>

    <!-- Empty State when search has no results -->
    <div id="noResultsAlert" class="hidden bg-white p-12 text-center rounded-3xl border border-secondaryCream shadow-sm space-y-3">
        <div class="w-16 h-16 bg-secondaryCream/40 rounded-full flex items-center justify-center mx-auto text-primaryBrown text-2xl">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <h3 class="text-lg font-black text-darkText">لم يتم العثور على تساؤلات مطابقة</h3>
        <p class="text-xs text-mutedBrown max-w-md mx-auto">
            جرّب البحث بكلمات أخرى مثل "فاتورة"، "مرتجع"، "سلفة"، "ترزي"، أو قم بإلغاء الفلتر لعرض جميع الأسئلة.
        </p>
        <button onclick="clearSearch()" class="px-4 py-2 bg-primaryBrown text-white text-xs font-bold rounded-xl hover:bg-mutedBrown transition">
            إعادة تعيين البحث
        </button>
    </div>

    <!-- Questions Container -->
    <div class="space-y-6" id="questionsList">

        <!-- ========================================================
             ⭐ QUESTION 1: THE GRAND GENERAL HARMONY QUESTION
             ======================================================== -->
        <div class="question-item bg-white rounded-3xl border-2 border-[#926857]/40 shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg" 
            data-category="general" 
            data-keywords="عام جينيرال تناغم تكامل النظام دورة العمل التشغيلية فواتير شراء مخزون انتاج رواتب سلف شحن توربو مرتجعات workflow lifecycle">
            
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header is-open w-full p-6 sm:p-7 flex items-center justify-between text-right gap-4 bg-gradient-to-r from-primaryBrown/5 to-roseAccent/5 border-b border-secondaryCream cursor-pointer select-none">
                <div class="flex items-start sm:items-center gap-4">
                    <span class="w-12 h-12 rounded-2xl bg-[#926857] text-white flex items-center justify-center text-xl font-bold shrink-0 shadow-md">
                        <i class="fa-solid fa-diagram-project"></i>
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1.5">
                            <span class="px-2.5 py-0.5 text-[10px] font-black rounded-full bg-[#926857] text-white uppercase tracking-wider">
                                سؤال النظام العام والأهم ⭐
                            </span>
                            <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">
                                تناغم وتكامل المنظومة (Ecosystem Harmony)
                            </span>
                        </div>
                        <h2 class="text-lg sm:text-xl font-black text-darkText leading-snug">
                            كيف تعمل هذه الميزات الجديدة بتناغم وتكامل تام مع باقي أجزاء نظام تاجميديكس؟ وما هي دورة التشغيل الشاملة (End-to-End Workflow)؟
                        </h2>
                    </div>
                </div>
                <div class="guide-chevron text-primaryBrown text-lg px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>

            <div class="guide-accordion-content p-6 sm:p-8 space-y-6 bg-white">
                <div class="prose max-w-none text-darkText text-sm leading-relaxed space-y-4">
                    <p class="font-bold text-base text-primaryBrown">
                        نظام Tagmedix تم بناؤه بهندسة برمجية متماسكة، حيث لا توجد أي ميزة تعمل في جزيرة منعزلة. كل ميزة جديدة تمثل حلقة وصل في سلسلة تشغيلية ومحاسبية مغلقة تبدأ من شراء الخامات وتنتهي بتسليم العميل وإدارة المرتجعات:
                    </p>

                    <!-- Interactive Visual Flow Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 my-6">
                        <!-- Step 1 -->
                        <div class="p-5 rounded-2xl bg-primaryBg/40 border border-secondaryCream space-y-2 relative overflow-hidden">
                            <div class="flex items-center gap-2.5">
                                <span class="step-number">1</span>
                                <h4 class="font-black text-darkText text-sm">المشتريات والموردين (Suppliers & Invoices)</h4>
                            </div>
                            <p class="text-xs text-mutedBrown leading-normal">
                                شراء الأقمشة والخيوط والمستلزمات عبر فواتير الشراء. عند اعتماد الفاتورة (Received)، تزيد أرصدة الخامات تلقائياً في المخزن (<code class="text-xs bg-white px-1.5 py-0.5 rounded border">InventoryStock</code>) وتُسجل حركة وارد رسمية بمتوسط التكلفة.
                            </p>
                            <div class="text-[11px] font-bold text-primaryBrown pt-1 flex items-center gap-1">
                                <i class="fa-solid fa-arrow-down-long text-xs"></i> يغذي رصيد المخزن مباشرةً
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="p-5 rounded-2xl bg-primaryBg/40 border border-secondaryCream space-y-2">
                            <div class="flex items-center gap-2.5">
                                <span class="step-number">2</span>
                                <h4 class="font-black text-darkText text-sm">صرف الخامات وأمر التشغيل (Production Workflow)</h4>
                            </div>
                            <p class="text-xs text-mutedBrown leading-normal">
                                عند إنشاء طلب عميل، يمر بمراحل التصنيع (قص، خياطة، مكواة، تشطيب). يتم استهلاك الخامات من المخزن، ويقوم الترزي بتنفيذ مهام الخياطة الموكلة إليه وتوثيقها خطوة بخطوة في النظام.
                            </p>
                            <div class="text-[11px] font-bold text-primaryBrown pt-1 flex items-center gap-1">
                                <i class="fa-solid fa-arrow-down-long text-xs"></i> يسجل خطوات الإنجاز الفعلية
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="p-5 rounded-2xl bg-primaryBg/40 border border-secondaryCream space-y-2">
                            <div class="flex items-center gap-2.5">
                                <span class="step-number">3</span>
                                <h4 class="font-black text-darkText text-sm">احتساب إنتاجية الترزي بالقطعة (Piece-Rate Engine)</h4>
                            </div>
                            <p class="text-xs text-mutedBrown leading-normal">
                                يرتبط سجل إنجاز الترزي بجدول أسعار القطع (<code class="text-xs bg-white px-1.5 py-0.5 rounded border">TailorPiecePrice</code>). كل قطعة ينهيها الترزي تضاف قيمتها فوراً لحسابه المستحق لهذا الشهر آلياً دون أي دفاتر ورقية.
                            </p>
                            <div class="text-[11px] font-bold text-primaryBrown pt-1 flex items-center gap-1">
                                <i class="fa-solid fa-arrow-down-long text-xs"></i> يحدد مستحقات العمل بدقة
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="p-5 rounded-2xl bg-primaryBg/40 border border-secondaryCream space-y-2">
                            <div class="flex items-center gap-2.5">
                                <span class="step-number">4</span>
                                <h4 class="font-black text-darkText text-sm">السلف والخصومات والمكافآت (Employee Advances)</h4>
                            </div>
                            <p class="text-xs text-mutedBrown leading-normal">
                                طوال الشهر، يمكن تسجيل أي سلفة نقدية، مكافأة، أو خصم للموظف. تظل المعاملات معلقة وتحت الحماية حتى وقت إعداد المرتبات.
                            </p>
                            <div class="text-[11px] font-bold text-primaryBrown pt-1 flex items-center gap-1">
                                <i class="fa-solid fa-arrow-down-long text-xs"></i> يربط الحركات المالية بالموظف
                            </div>
                        </div>

                        <!-- Step 5 -->
                        <div class="p-5 rounded-2xl bg-primaryBg/40 border border-secondaryCream space-y-2">
                            <div class="flex items-center gap-2.5">
                                <span class="step-number">5</span>
                                <h4 class="font-black text-darkText text-sm">توليد مسير الرواتب الآلي (Automated Payroll)</h4>
                            </div>
                            <p class="text-xs text-mutedBrown leading-normal">
                                بنقرة زر واحدة في نهاية الشهر: يجمع النظام (الراتب الأساسي أو إنتاجية القطع + المكافآت) ويخصم آلياً (السلف المسحوبة + الجزاءات)، ويغلق السلف المسواة لمنع التكرار نهائياً.
                            </p>
                            <div class="text-[11px] font-bold text-primaryBrown pt-1 flex items-center gap-1">
                                <i class="fa-solid fa-arrow-down-long text-xs"></i> يحسب الصافي بضغطة زر
                            </div>
                        </div>

                        <!-- Step 6 -->
                        <div class="p-5 rounded-2xl bg-primaryBg/40 border border-secondaryCream space-y-2">
                            <div class="flex items-center gap-2.5">
                                <span class="step-number">6</span>
                                <h4 class="font-black text-darkText text-sm">الشحن مع Turbo وإعادة التخزين بالمرتجع (Shipping & QC)</h4>
                            </div>
                            <p class="text-xs text-mutedBrown leading-normal">
                                بمجرد انتهاء المنتج ينتقل إلى الطلبات التامة ليتم شحنه فردياً أو جماعياً عبر Turbo. وإذا حدث مرتجع، يُفحص بالجودة (QC) لتعود القطع السليمة فوراً لمخزن المنتجات التامة جاهزة للبيع مجدداً!
                            </p>
                            <div class="text-[11px] font-bold text-emerald-600 pt-1 flex items-center gap-1">
                                <i class="fa-solid fa-circle-check text-xs"></i> إغلاق الدورة المخزنية والمالية
                            </div>
                        </div>
                    </div>

                    <!-- Highlight Note -->
                    <div class="p-4 rounded-2xl bg-amber-50 border-r-4 border-amber-500 text-amber-900 text-xs leading-relaxed space-y-1">
                        <p class="font-bold flex items-center gap-1.5 text-sm">
                            <i class="fa-solid fa-shield-halved text-amber-600"></i>
                            القيمة التشغيلية المضافة لإدارة تاجميديكس:
                        </p>
                        <p>
                            بهذا التكامل، أصبحت المنظومة خالية من الأخطاء المحاسبية الشائعة: لن يُصرف راتب ترزي بأقل أو أكثر من قطعه المنفذة، ولن تُنسى سلفة نقدية دون خصمها، ولن تضيع قطعة قماش دخلت المخزن دون فاتورة شراء، ولن تُهدر قطعة مرتجعة صالحة لأن نظام فحص الجودة يعيدها فوراً إلى رصيد المبيعات المتاحة!
                        </p>
                    </div>
                </div>
            </div>
        </div>


        <!-- ========================================================
             SECTION 1: PURCHASE INVOICES & SUPPLIERS
             ======================================================== -->
        
        <!-- Q2: How to create purchase invoice -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="purchases" 
            data-keywords="فاتورة شراء إضافة إنشاء مورد موردين خامات اقمشة مخزون purchase invoice supplier create items">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-cart-plus"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-secondaryCream text-mutedBrown">فواتير الشراء</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline"><i class="fa-solid fa-location-dot text-[10px]"></i> القائمة الجانبية > فواتير الشراء > إنشاء فاتورة جديدة</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            إزاي أقدر أضيف وأسجل فاتورة شراء جديدة للخامات والمستلزمات في النظام؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText">
                    <p class="font-bold text-primaryBrown text-sm">خطوات تسجيل فاتورة شراء جديدة بالتفصيل الدقيق:</p>
                    
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="step-number">1</span>
                            <div>
                                <strong class="text-darkText block font-bold">الدخول لشاشة إنشاء الفاتورة:</strong>
                                من القائمة الجانبية، اضغط على تبويب <strong>"فواتير الشراء"</strong> ثم انقر على زر <strong>"إنشاء فاتورة شراء جديدة"</strong> بأعلى الصفحة.
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="step-number">2</span>
                            <div>
                                <strong class="text-darkText block font-bold">تعبئة بيانات المورد والتواريخ:</strong>
                                <ul class="list-disc list-inside mt-1 space-y-1 text-mutedBrown">
                                    <li><strong>المورد:</strong> اختر المورد المسجل من القائمة المنسدلة (إذا كان مورداً جديداً أضفه أولاً من شاشة الموردين).</li>
                                    <li><strong>رقم فاتورة المورد الورقية:</strong> اكتب الرقم المطبوع على الفاتورة الورقية التي استلمتها مع الشحنة لمطابقتها مستقبلاً.</li>
                                    <li><strong>تاريخ الفاتورة:</strong> حدد تاريخ تحرير الفاتورة الفعلي.</li>
                                    <li><strong>تاريخ الاستحقاق:</strong> (اختياري) موعد سداد الآجل المتفق عليه مع المورد.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="step-number">3</span>
                            <div>
                                <strong class="text-darkText block font-bold">إضافة بنود الأصناف والخامات:</strong>
                                اضغط على زر <strong>"إضافة بند صنف"</strong>، ثم حدد:
                                <ul class="list-disc list-inside mt-1 space-y-1 text-mutedBrown">
                                    <li><strong>الصنف المخزني:</strong> اختر القماش أو المستلزم من دليل المخزون.</li>
                                    <li><strong>اللون / المتغير:</strong> حدد اللون أو المتغير إن وجد (مثل كود اللون أو العرض).</li>
                                    <li><strong>الكمية والوحدة:</strong> أدخل الكمية المشتراة (مثل 50) وحدد الوحدة (متر، كجم، بكرة، قطعة).</li>
                                    <li><strong>سعر الوحدة:</strong> اكتب سعر الشراء للوحدة، وسيحسب النظام إجمالي السطر تلقائياً.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="step-number">4</span>
                            <div>
                                <strong class="text-darkText block font-bold">المصروفات، الخصومات، والمدفوع:</strong>
                                يمكنك إضافة (خصم تجاري، ضريبة القيمة المضافة، أو مصاريف شحن ونقل). وإذا قمت بدفع دفعة كاش مقدماً مع الفاتورة، اكتبها في خانة <strong>"المبلغ المدفوع مقدماً"</strong> وحدد طريقة الدفع.
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="step-number">5</span>
                            <div>
                                <strong class="text-darkText block font-bold">تحديد حالة الفاتورة وحفظها:</strong>
                                اختر إما <strong>"مسودة (Draft)"</strong> للمراجعة دون التأثير على المخزن، أو <strong>"مستلمة (Received)"</strong> لتوريد الأصناف فوراً لرصيد المخزن، ثم انقر <strong>"حفظ واعتماد الفاتورة"</strong>.
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-3">
                        <a href="{{ route('purchase-invoices.create') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-primaryBrown text-white text-xs font-bold hover:bg-mutedBrown transition">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            <span>فتح شاشة إنشاء فاتورة شراء الآن</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Q3: Draft vs Received -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="purchases" 
            data-keywords="مسودة مستلمة فرق حالة فاتورة الشراء draft received inventory stock">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-scale-balanced"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-secondaryCream text-mutedBrown">فواتير الشراء</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline">مقارنة الحالات المخزنية</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            إيه الفرق الجوهري بين حالة الفاتورة "مسودة (Draft)" وحالة "مستلمة (Received)"؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs sm:text-sm">
                    <div class="p-5 rounded-2xl bg-amber-50/60 border border-amber-200 space-y-2">
                        <div class="flex items-center gap-2 text-amber-800 font-black">
                            <i class="fa-solid fa-file-pen text-base"></i>
                            <span>حالة مسودة (Draft)</span>
                        </div>
                        <p class="text-darkText leading-relaxed">
                            تُستخدم عند إدخال الفاتورة قبل وصول البضاعة فعلياً، أو عندما تكون قيد المراجعة مع المحاسب أو المشرف.
                        </p>
                        <ul class="list-disc list-inside space-y-1 text-mutedBrown pt-1">
                            <li><strong>تأثير المخزن:</strong> <span class="text-red-600 font-bold">لا تؤثر على الإطلاق</span> على رصيد المخزن.</li>
                            <li><strong>حركات المخزون:</strong> لا يتم إنشاء أي سجل وارد حتى الاعتماد.</li>
                            <li><strong>إمكانية التعديل:</strong> يمكن مراجعتها وتعديل بنودها بسهولة.</li>
                        </ul>
                    </div>

                    <div class="p-5 rounded-2xl bg-emerald-50/60 border border-emerald-200 space-y-2">
                        <div class="flex items-center gap-2 text-emerald-800 font-black">
                            <i class="fa-solid fa-boxes-packing text-base"></i>
                            <span>حالة مستلمة (Received)</span>
                        </div>
                        <p class="text-darkText leading-relaxed">
                            تُستخدم عندما يتم تفريغ الشحنة في المخزن، وعدّ الأمتار والكميات، والتأكد من مطابقتها للفاتورة.
                        </p>
                        <ul class="list-disc list-inside space-y-1 text-mutedBrown pt-1">
                            <li><strong>تأثير المخزن:</strong> <span class="text-emerald-700 font-bold">تزيد رصيد المخزن فوراً</span> بالكميات المشتراة.</li>
                            <li><strong>حركات المخزون:</strong> يُنشئ النظام حركة توريد وارد (<code class="bg-white px-1 rounded">Receipt</code>) موثقة بالتاريخ والوقت.</li>
                            <li><strong>التكلفة:</strong> تحدث متوسط تكلفة الوحدة للصنف تلقائياً.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Q4: Receive Draft Invoice -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="purchases" 
            data-keywords="اعتماد استلام فاتورة توريد للمخزن وارد حركات المخزون receive stock inventory transaction">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-dolly"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-secondaryCream text-mutedBrown">فواتير الشراء</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline">اعتماد التوريد للمخزن</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            إزاي اعتمد استلام فاتورة شراء كانت "مسودة" وأورد أصنافها للمخزن لاحقاً (Receive)؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                    <div class="flex items-start gap-3">
                        <span class="step-number">1</span>
                        <div>
                            ادخل على قائمة <strong>"فواتير الشراء"</strong>، وابحث عن الفاتورة التي حالتها <strong>"مسودة (Draft)"</strong>، ثم اضغط على زر عرض الفاتورة (أيقونة العين أو رقم الفاتورة).
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="step-number">2</span>
                        <div>
                            في أعلى صفحة تفاصيل الفاتورة، ستجد زراً بارزاً باللون الأخضر باسم <strong>"اعتماد الاستلام وتوريد للمخزن"</strong>. اضغط عليه وأكّد الاستلام.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="step-number">3</span>
                        <div>
                            <strong>ماذا يفعل النظام في الكواليس؟</strong>
                            <ul class="list-disc list-inside mt-1 space-y-1 text-mutedBrown">
                                <li>يحدّث رصيد كل صنف ومتغير في المخزون (<code class="bg-primaryBg px-1 rounded">InventoryStock</code>).</li>
                                <li>يسجل حركة مخزنية رسمية من نوع <code class="bg-primaryBg px-1 rounded">Receipt (وارد)</code> مرتبطة برقم فاتورة الشراء واسم المستخدم الذي قام بالاستلام.</li>
                                <li>يحسب الرصيد بعد الحركة (<code class="bg-primaryBg px-1 rounded">balance_after</code>) وتكلفة الوحدة.</li>
                                <li>تتحول حالة الفاتورة إلى <strong>"مستلمة"</strong> ويتم ختم وقت الاستلام بدقة.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Q5: Add supplier payment -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="purchases" 
            data-keywords="سداد دفعة مورد سداد جزئي كلي شيك كاش تحويل بنكي supplier payment add">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-secondaryCream text-mutedBrown">فواتير الشراء</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline">المدفوعات والمستحقات</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            إزاي أقدر أسجل دفعة مالية جديدة لمورد على فاتورة شراء قائمة (سداد جزئي أو كامل)؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                    <p>إذا كانت الفاتورة عليها متبقي مالي (حالتها: غير مسدد Unpaid أو مسدد جزئياً Partial):</p>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="step-number">1</span>
                            <div>من صفحة تفاصيل الفاتورة، انزل إلى بطاقة <strong>"دفعات المورد وسجل السداد"</strong> واضغط على زر <strong>"تسجيل دفعة جديدة"</strong>.</div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">2</span>
                            <div>أدخل <strong>مبلغ الدفعة</strong> (يمنع النظام تجاوز المبلغ المتبقي على الفاتورة لمنع الأخطاء المحاسبية).</div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">3</span>
                            <div>اختر <strong>طريقة الدفع</strong> (كاش، تحويل بنكي، فودافون كاش، إنستاباي، شيك) واكتب رقم الإشعار أو المرجع البنكي وملاحظات السند.</div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">4</span>
                            <div>انقر حفظ، وسيتم توليد كود دفعة تلقائي (<code class="bg-primaryBg px-1 rounded">SPAY-...</code>)، وتحديث الرصيد المتبقي على الفاتورة فوراً. وإذا وصل المتبقي لصفر، تتحول الفاتورة تلقائياً إلى <strong>"مسددة بالكامل (Paid)"</strong>.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- ========================================================
             SECTION 2: RETURNS & QC INSPECTION
             ======================================================== -->

        <!-- Q6: How to create order return -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="returns" 
            data-keywords="مرتجع طلب تسجيل مرتجع كلي جزئي عميل فحص استرجاع order return create refund">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-rotate-left"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-rose-100 text-rose-800">المرتجعات وفحص الجودة</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline"><i class="fa-solid fa-location-dot text-[10px]"></i> القائمة الجانبية > المرتجعات > تسجيل مرتجع جديد</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            إزاي أقدر أسجل طلب مرتجع لأوردر مسبق (كلي أو جزئي) مع خصم مصاريف الشحن؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                    <p class="font-bold text-primaryBrown">خطوات تسجيل المرتجع بسهولة وسرعة:</p>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="step-number">1</span>
                            <div>
                                <strong>البحث عن الأوردر:</strong>
                                في شاشة إنشاء المرتجع، اكتب رقم الأوردر (مثال: ORD-...) أو رقم هاتف العميل في خانة البحث الفوري، أو اختر من قائمة أحدث الطلبات. سيقوم النظام بجلب كافة بنود وتفاصيل الأوردر تلقائياً.
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">2</span>
                            <div>
                                <strong>نوع المرتجع والسياسة المالية:</strong>
                                حدد هل المرتجع <strong>"كلي (Full)"</strong> لكل بنود الطلب، أم <strong>"جزئي (Partial)"</strong> لقطعة محددة. ثم اكتب سبب الإرجاع الرئيسي (مثال: مقاس غير مناسب، رغبة العميل، تأخر التوصيل).
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">3</span>
                            <div>
                                <strong>خصم مصاريف الشحن:</strong>
                                إذا كانت سياسة المتجر تلزم العميل بتحمل مصاريف الشحن عند الإرجاع، اكتب قيمة الشحن في خانة <strong>"قيمة الشحن المخصومة"</strong> ليتم خصمها آلياً من صافي المبلغ المسترد.
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">4</span>
                            <div>
                                <strong>تحديد القطع والكميات:</strong>
                                في جدول البنود، حدد الكمية المرتجعة لكل قطعة واكتب ملاحظة السطر إن وجدت، ثم انقر <strong>"حفظ طلب المرتجع"</strong>. سيصدر النظام كود مرتجع تلقائي (<code class="bg-primaryBg px-1 rounded">RET-...</code>) وتكون حالته "قيد الانتظار والفحص".
                            </div>
                        </div>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('returns.create') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-primaryBrown text-white text-xs font-bold hover:bg-mutedBrown transition">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            <span>تسجيل طلب مرتجع جديد الآن</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Q7: Quality Check QC Process -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="returns" 
            data-keywords="فحص جودة كواليتي تشيك qc سليم تالف معيب quality check good damaged">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-rose-100 text-rose-800">المرتجعات وفحص الجودة</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline">فحص الجودة QC</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            إزاي أنفذ عملية فحص الجودة (QC) للقطع المرتجعة؟ وما الفرق بين السليم والتالف؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                    <p>بعد استلام الطرد المرتجع من مندوب الشحن، يقوم مسؤول الفحص بالدخول لصفحة تفاصيل المرتجع (<code class="bg-primaryBg px-1 rounded">returns.show</code>):</p>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="step-number">1</span>
                            <div>
                                في جدول فحص البنود، يظهر أمام كل قطعة خياران للفحص:
                                <ul class="list-disc list-inside mt-1 space-y-1">
                                    <li><strong class="text-emerald-700">سليم ومطابق (Good):</strong> القطعة بحالتها الأصلية غير ملبوسة وبدون أي تلف أو أوساخ، وصالحة لإعادة بيعها فوراً.</li>
                                    <li><strong class="text-red-600">تالف / معيب (Damaged):</strong> القطعة بها عيب قماش، خياطة مقطوعة، أو تم استخدامها وتلفها من قبل العميل، وتُنقل للهالك.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">2</span>
                            <div>
                                <strong>خيار إعادة التخزين:</strong> تأكد من تفعيل خانة <em>"إعادة الأصناف السليمة تلقائياً لمخزون المنتجات التامة"</em>.
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">3</span>
                            <div>
                                انقر <strong>"اعتماد نتيجة الفحص"</strong>. يحدّث النظام حالة الفحص الشاملة إلى (سليم Good، تالف Damaged، أو فحص جزئي Partial Good) وتتحول حالة المرتجع إلى "مكتمل Completed".
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Q8: Restocking to Finished Goods -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="returns" 
            data-keywords="إعادة تخزين ريستوك منتجات تامة جاهزة للبيع restock finished goods inventory">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-rose-100 text-rose-800">المرتجعات وفحص الجودة</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline">إعادة التخزين التلقائي Restock</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            إزاي الأصناف المرتجعة السليمة بترجع تلقائياً لمخزون المنتجات التامة (Restock to Finished Goods)؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                    <div class="p-4 rounded-2xl bg-emerald-50 border-r-4 border-emerald-500 text-emerald-900 space-y-1">
                        <p class="font-bold flex items-center gap-1.5 text-sm">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i> ميزة إعادة التخزين الآلية (Restock Engine):
                        </p>
                        <p>
                            تعد هذه الميزة من أقوى ميزات تاجميديكس الحديثة، حيث تمنع هدر القطع وتوفر تكاليف الخامات وأجور الخياطة من خلال إعادة تدوير القطع السليمة فورياً.
                        </p>
                    </div>

                    <p><strong>كيف تعمل الآلية الفنية للـ Restock في الكود؟</strong></p>
                    <ul class="list-disc list-inside space-y-2 text-mutedBrown">
                        <li>بمجرد اختيار "سليم Good" واعتماد الفحص، يبحث النظام في جدول <code class="bg-primaryBg px-1 rounded">InventoryFinishedProduct</code> عن نفس (الموديل، اللون، المقاس، نوع البنطلون، ونوع الكم).</li>
                        <li>يقوم النظام بزيادة رصيد الكمية المتوفرة فوراً بعدد القطع السليمة المرتجعة.</li>
                        <li>يتم وضع كود منتج معتمد يبدأ بـ <code class="bg-primaryBg px-1 rounded">RESTOCK-...</code> وتحديث تكلفة المخزون.</li>
                        <li><strong>النتيجة العملية:</strong> تظهر القطعة فوراً في شاشة المنتجات التامة والمخزن، ويمكن تخصيصها وبيعها لأي عميل جديد دون الحاجة لإدخال أمر قص أو خياطة أو تفصيل جديد!</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Q9: Refund Process -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="returns" 
            data-keywords="استرداد اموال تسوية مالية مرتجع ريفاند refund finance customer">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-rose-100 text-rose-800">المرتجعات وفحص الجودة</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline">التسوية المالية Refund</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            إزاي اثبت استرداد المبلغ المالي للعميل (Refund Process) وإغلاق المرتجع مالياً؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                    <div class="flex items-start gap-3">
                        <span class="step-number">1</span>
                        <div>من شاشة تفاصيل المرتجع، ستجد شارة حمراء <strong>"المبلغ معلق لم يسترد"</strong> وزراً باللون الأخضر <strong>"تأكيد استرداد المبلغ للعميل"</strong>.</div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="step-number">2</span>
                        <div>عند الضغط عليه، تفتح نافذة لتأكيد الإجراء المتخذ (مثال: تحويل إنستاباي، فودافون كاش، تسليم كاش بالمقر، أو كوبون رصيد).</div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="step-number">3</span>
                        <div>عند التأكيد، تتحول حالة الاسترداد إلى <strong>"تم استرداد المبلغ (Refunded)"</strong> باللون الأخضر ويتم إغلاق المرتجع محاسبياً بنجاح.</div>
                    </div>
                </div>
            </div>
        </div>


        <!-- ========================================================
             SECTION 3: EMPLOYEE ADVANCES & BONUSES
             ======================================================== -->

        <!-- Q10: How to record advance/bonus/deduction -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="advances" 
            data-keywords="سلفة مكافأة خصم موظف موظفين ترزية عامل سلفيات advance bonus deduction employee">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-receipt"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-purple-100 text-purple-800">شؤون الموظفين والسلف</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline"><i class="fa-solid fa-location-dot text-[10px]"></i> القائمة الجانبية > الموظفون > تفاصيل الموظف</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            إزاي أسجل سلفة نقدية، مكافأة تشجيعية، أو خصم جزائي لموظف من شاشة الموظفين؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="step-number">1</span>
                            <div>
                                من القائمة الجانبية، ادخل على <strong>"الموظفون"</strong>، ثم انقر على اسم الموظف المطلوب لفتح شاشة تفاصيله (<code class="bg-primaryBg px-1 rounded">employees.show</code>).
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">2</span>
                            <div>
                                في أعلى الصفحة، اضغط على زر <strong>"سلفة / مكافأة / خصم"</strong> لفتح النافذة المنبثقة.
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">3</span>
                            <div>
                                <strong>اختر نوع المعاملة:</strong>
                                <ul class="list-disc list-inside mt-1 space-y-1 text-mutedBrown">
                                    <li><strong class="text-red-600">سلفة (Advance):</strong> مبلغ نقدي سحبه العامل مقدماً ويُخصم من راتبه القادم.</li>
                                    <li><strong class="text-emerald-700">مكافأة (Bonus):</strong> حافز إضافي أو مكافأة تميز تُضاف وتزيد من راتب العامل.</li>
                                    <li><strong class="text-red-700">خصم (Deduction):</strong> جزاء غياب أو تلف يُخصم من مستحقات العامل.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">4</span>
                            <div>
                                أدخل <strong>المبلغ</strong>، <strong>تاريخ السحب</strong>، واكتب <strong>البيان / الملاحظة</strong> ثم احفظ. ستظهر المعاملة فوراً في تبويب "السلف والخصومات" وتحدث إجمالي السلف المعلقة للموظف.
                            </div>
                        </div>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-primaryBrown text-white text-xs font-bold hover:bg-mutedBrown transition">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            <span>الانتقال لشاشة الموظفين</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Q11: Advance Lifecycle & Protection -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="advances" 
            data-keywords="دورة حياة السلفة تسوية حماية الغاء حذف unsettled settled advances">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-purple-100 text-purple-800">شؤون الموظفين والسلف</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline">أمان السجلات والحماية</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            ما هي دورة حياة السلفة؟ وإزاي النظام بيحمي السلف بعد تسويتها في مسير الرواتب؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 space-y-2">
                            <strong class="text-amber-800 font-bold block text-sm">الحالة 1: غير مسواة (Unsettled)</strong>
                            <p class="text-mutedBrown">
                                تكون السلفة بهذه الحالة فور تسجيلها وطوال الشهر. يظهر بجوارها زر حذف أحمر، ويمكن حذفها أو تعديلها في حال الخطأ لأن مسير الراتب لم يعتمد بعد.
                            </p>
                        </div>
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 space-y-2">
                            <strong class="text-emerald-800 font-bold block text-sm">الحالة 2: تم تسويتها (Settled)</strong>
                            <p class="text-mutedBrown">
                                بمجرد الضغط على "توليد مسير الرواتب" للشهر المعني، يقوم النظام بربط السلفة بمسير الراتب، وخصمها من الراتب الصافي، وتتحول حالتها إلى "مسواة". <span class="font-bold text-red-600">يتم إخفاء زر الحذف فوراً ويمنع النظام حذفها</span> لمنع التلاعب المالي أو الخلل في ميزانية الرواتب.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- ========================================================
             SECTION 4: PAYROLL & PIECE-RATE ENGINE
             ======================================================== -->

        <!-- Q12: Generate Payroll -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="payroll" 
            data-keywords="توليد مسير الرواتب الشهر الرواتب حساب تلقائي مرتبات payroll generate bulk">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-calculator"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-blue-100 text-blue-800">مسير الرواتب والإنتاج</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline"><i class="fa-solid fa-location-dot text-[10px]"></i> القائمة الجانبية > الرواتب</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            إزاي أولّد مسير الرواتب الشهري لجميع موظفي الشركة بنقرة زر واحدة (Generate Payroll)؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="step-number">1</span>
                            <div>من القائمة الجانبية، اضغط على <strong>"الرواتب"</strong> (<code class="bg-primaryBg px-1 rounded">payroll.index</code>).</div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">2</span>
                            <div>في أعلى الشاشة، اختر <strong>الشهر والسنة</strong> المطلوب احتساب رواتبهم من القائمة المنسدلة (مثال: أكتوبر 2026).</div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">3</span>
                            <div>انقر على الزر الذهبي البارز <strong>"توليد مسير الشهر آلياً"</strong>.</div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">4</span>
                            <div>
                                <strong>المعالجة الفورية:</strong>
                                في ثوانٍ معدودة، يمسح النظام كافة الموظفين النشطين، ويحسب مستحقات ذوي الرواتب الثابتة، ويحسب إنتاجية القطع للترزية، ويخصم السلف والخصومات، وينشئ جدول المسير بحالة <strong>"مسودة (Draft)"</strong> لتتمكن من مراجعته واعتماده.
                            </div>
                        </div>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('payroll.index') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-primaryBrown text-white text-xs font-bold hover:bg-mutedBrown transition">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            <span>الانتقال لشاشة مسير الرواتب الآن</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Q13: Piece-Rate Tailors Calculation -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="payroll" 
            data-keywords="حساب بالقطعة ترزي ترزية خياطين اسعار القطع خطوات الانتاج piece rate tailor earnings">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-scissors"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-blue-100 text-blue-800">مسير الرواتب والإنتاج</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline">محرك أجور القطعة Piece-Rate</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            إزاي النظام بيحسب أجر عمال الخياطة والترزية تلقائياً بناءً على الإنتاج بالقطعة (Piece-Rate)؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                    <p class="font-bold text-primaryBrown">المعادلة الذكية لاحتساب أجور الترزية بدون أوراق أو أخطاء:</p>
                    <ol class="list-decimal list-inside space-y-2 text-mutedBrown">
                        <li><strong>تحديد نوع الراتب:</strong> يكون الموظف مسجلاً في ملفه بنظام محاسبة <code class="bg-primaryBg px-1 rounded">piece_rate (محاسبة بالإنتاج)</code>.</li>
                        <li><strong>جدول أسعار القطع:</strong> تكون أسعار خياطة كل موديل ومقاس مسجلة مسبقاً في جدول <code class="bg-primaryBg px-1 rounded">TailorPiecePrice</code> (مثال: تفصيل جاكيت 35 ج.م، بنطلون 20 ج.م).</li>
                        <li><strong>تتبع إنجاز خطوات الإنتاج:</strong> خلال الشهر، عندما ينتهي الترزي من خياطة قطع معينة ويوثقها المشرف في النظام، تُسجل في جدول سجلات الإنتاج (<code class="bg-primaryBg px-1 rounded">ProductionStepLog</code>) برقم الأوردر، تاريخ التنفيذ، وعدد القطع.</li>
                        <li><strong>الاحتساب الآلي:</strong> عند توليد مسير الرواتب، يستعلم النظام عن مجموع القطع المنفذة لهذا العامل في ذلك الشهر ويضربها بسعر القطعة المعتمد، فتظهر خانة <em>"إنتاجية القطع"</em> و<em>"عدد القطع المنجزة"</em> في المسير بأعلى دقة رياضية ممكنة.</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Q14: Net Salary Formula -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="payroll" 
            data-keywords="معادلة صافي الراتب خصم السلف مكافآت جزاءات net salary formula deductions">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-equals"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-blue-100 text-blue-800">مسير الرواتب والإنتاج</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline">الحسابات المحاسبية</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            إزاي بتتحسب المعادلة المالية لصافي الراتب وخصم السلف والجزاءات وإضافة المكافآت آلياً؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                    <div class="p-4 rounded-2xl bg-primaryBg/60 border border-secondaryCream text-center space-y-2">
                        <p class="text-xs font-bold text-mutedBrown uppercase tracking-wider">المعادلة المعتمدة في النظام:</p>
                        <p class="text-base sm:text-lg font-black text-primaryBrown font-mono">
                            صافي الراتب = (مستحق العمل + المكافآت) - (السلف المسحوبة + الخصومات)
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                            <strong class="text-emerald-800 font-bold block mb-1">المستحقات الإيجابية (+):</strong>
                            <p>الراتب الأساسي الثابت (للموظفين الشهريين) أو إجمالي أجر إنتاج القطع (للترزية) + المكافآت والحوافز المسجلة بالشهر.</p>
                        </div>
                        <div class="p-3 bg-red-50 rounded-xl border border-red-200">
                            <strong class="text-red-800 font-bold block mb-1">الاستقطاعات (-):</strong>
                            <p>السلف النقدية المسحوبة خلال الشهر + الجزاءات والخصومات المعتمدة، مع ضمان ألا يقل الصافي عن صفر.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Q15: Bulk Approve & Bulk Pay -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="payroll" 
            data-keywords="اعتماد جماعي صرف جماعي bulk approve bulk pay payroll تسليم الرواتب">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-money-check-dollar"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-blue-100 text-blue-800">مسير الرواتب والإنتاج</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline">الاعتماد والصرف الجماعي</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            ما هي خطوات اعتماد وصرف الرواتب فردياً أو جماعياً (Bulk Approve & Bulk Pay)؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="step-number">1</span>
                            <div>
                                <strong>المرحلة الأولى: المسودة (Draft):</strong>
                                بعد توليد المسير، يراجع المحاسب أو المدير الأرقام. يمكن حذف أي مسودة بالخطأ وتعديل السلف ثم إعادة التوليد.
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">2</span>
                            <div>
                                <strong>المرحلة الثانية: الاعتماد (Approved):</strong>
                                يمكنك الضغط على زر <strong>"اعتماد الكل (Bulk Approve)"</strong> في أعلى الصفحة، أو الضغط على زر الاعتماد الفردي أمام كل موظف. تتحول الحالة إلى معتمد وتصبح جاهزة للصرف.
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">3</span>
                            <div>
                                <strong>المرحلة الثالثة: الصرف والتسليم (Paid):</strong>
                                عند تسليم الأموال للعمال، اضغط على <strong>"صرف وتسليم الكل (Bulk Pay)"</strong> وحدد وسيلة الصرف (كاش، تحويل بنكي)، فيتم توثيق تاريخ وساعة الصرف رسميًا.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Q16: Payslip Show -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="payroll" 
            data-keywords="كشف راتب قسيمة راتب طباعة payslip payroll show employee piece details">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-print"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-blue-100 text-blue-800">مسير الرواتب والإنتاج</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline">كشف الراتب التفصيلي Payslip</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            إزاي استعرض وأطبع كشف الراتب التفصيلي (Payslip) لكل موظف موضحاً فيه تفاصيل القطع والسلف؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                    <p>
                        في جدول مسير الرواتب، اضغط على أيقونة <strong>العين (عرض التفاصيل)</strong> أمام اسم الموظف لفتح صفحة قسيمة الراتب (<code class="bg-primaryBg px-1 rounded">payroll.show</code>).
                    </p>
                    <p><strong>ماذا يحتوي كشف الراتب التفصيلي؟</strong></p>
                    <ul class="list-disc list-inside space-y-1 text-mutedBrown">
                        <li>بيانات الموظف ووظيفته ونوع حسابه المالي.</li>
                        <li><strong>جدول إنتاج القطع:</strong> جدول مفصل بكل حركة إنتاج نفذها العامل، رقم الأوردر، تاريخ وساعة التنفيذ، واسم المشرف المعتمد.</li>
                        <li><strong>جدول السلف والخصومات والمكافآت:</strong> كل سلفة مسحوبة مع تاريخها وبيانها وقيمتها المخصومة.</li>
                        <li><strong>خانة التوقيع والاستلام:</strong> زر طباعة مخصص يطبع كشف الراتب بصيغة أنيقة جاهزة لتوقيع العامل عند استلام راتبه.</li>
                    </ul>
                </div>
            </div>
        </div>


        <!-- ========================================================
             SECTION 5: SHIPPING & TURBO INTEGRATION
             ======================================================== -->

        <!-- Q17: Send Orders to Turbo -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="turbo" 
            data-keywords="شحن توربو طلبات تامة بوليصة شحن تتبع turbo shipment bulk ship finished goods">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-truck-ramp-box"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-100 text-amber-800">الشحن وعمليات Turbo</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline"><i class="fa-solid fa-location-dot text-[10px]"></i> القائمة الجانبية > الطلبات التامة</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            إزاي أشحن أوردر منفرد أو شحن جماعي (Bulk Ship) لشركة الشحن توربو مباشرة؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="step-number">1</span>
                            <div>
                                ادخل على <strong>"الطلبات التامة"</strong> (<code class="bg-primaryBg px-1 rounded">finished-goods.index</code>)، حيث تظهر جميع الأوردرات التي اكتمل تصنيعها وجاهزة للتسليم.
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">2</span>
                            <div>
                                <strong>شحن طلب منفرد:</strong> اضغط على زر <strong>"شحن عبر Turbo"</strong> أمام الأوردر المطلوب، فيقوم النظام بإرسال بيانات العميل وعنوانه ومبلغ التحصيل COD إلى سيرفرات Turbo وتوليد رقم التتبع فوراً.
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">3</span>
                            <div>
                                <strong>الشحن الجماعي (Bulk Ship):</strong> حدد مربعات الاختيار بجانب الطلبات المراد شحنها، ثم اضغط على زر <strong>"شحن المحدد جماعياً إلى Turbo"</strong> بأعلى الصفحة لإرسال العشرات من الشحنات بضغطة زر واحدة.
                            </div>
                        </div>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('finished-goods.index') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-primaryBrown text-white text-xs font-bold hover:bg-mutedBrown transition">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            <span>فتح شاشة الطلبات التامة</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Q18: Turbo Live Sync -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="turbo" 
            data-keywords="مزامنة شحنات توربو تحديث الحالات تحصيلات مندوب live sync turbo shipments">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-100 text-amber-800">الشحن وعمليات Turbo</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline"><i class="fa-solid fa-location-dot text-[10px]"></i> القائمة الجانبية > أوردرات تم شحنها</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            إزاي أحدث حالات الشحنات والتحصيلات المالية لحظياً من خوادم Turbo (Live Sync)؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="step-number">1</span>
                            <div>
                                ادخل على تبويب <strong>"أوردرات تم شحنها"</strong> (<code class="bg-primaryBg px-1 rounded">shipments.index</code>).
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">2</span>
                            <div>
                                اضغط على الزر الأصفر البارز <strong>"مزامنة الشحنات من Turbo"</strong> في أعلى الشاشة.
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">3</span>
                            <div>
                                يتصل النظام مباشرة عبر API مع شركة Turbo، ويجلب أحدث التطورات لجميع الشحنات (تم التسليم Delivered، مع المندوب Out for Delivery، مؤجل، أو مرتجع)، مع تحديث المبالغ المحصلة فعلياً لضمان مطابقة الكاش اليومي.
                            </div>
                        </div>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('shipments.index') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-primaryBrown text-white text-xs font-bold hover:bg-mutedBrown transition">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            <span>فتح مركز إدارة الشحنات</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Q19: Turbo Return Amounts -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="turbo" 
            data-keywords="مبالغ مرتجعات توربو تكلفة شحن المرتجع مناطق محافظات turbo return amounts settings">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-gear"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-100 text-amber-800">الشحن وعمليات Turbo</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline"><i class="fa-solid fa-location-dot text-[10px]"></i> القائمة الجانبية > الإعدادات > إعدادات مبالغ مرتجعات Turbo</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            كيف يتعامل النظام مع مبالغ مرتجعات توربو وتكاليف الشحن المعلقة؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                    <p>
                        تختلف تكلفة شحن المرتجعات بين محافظة وأخرى؛ ولذلك يتيح نظام تاجميديكس في شاشة الإعدادات قسماً مخصصاً باسم <strong>"مبالغ مرتجعات توربو (Turbo Return Amounts)"</strong> لتحديد تكلفة الإرجاع لكل محافظة ومنطقة، بحيث يتم احتساب الخصم المالي بدقة متناهية عند تسوية الشحنات المرتجعة مع شركة الشحن.
                    </p>
                </div>
            </div>
        </div>


        <!-- ========================================================
             SECTION 6: PRODUCTION WORKFLOW & CLASSIFICATIONS
             ======================================================== -->

        <!-- Q20: Production Workflow -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="production" 
            data-keywords="مسار عمل الطلبات مراحل الانتاج قص خياطة مكواة تشطيب جاهز للشحن production workflow kanban">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-industry"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-teal-100 text-teal-800">مسار عمل الإنتاج</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline"><i class="fa-solid fa-location-dot text-[10px]"></i> القائمة الجانبية > مسار عمل الطلبات</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            إزاي اتابع مراحل تصنيع الأوردرات (قص، خياطة، مكواة، تشطيب، جاهز للشحن) وإسنادها للمشرفين؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="step-number">1</span>
                            <div>
                                ادخل على <strong>"مسار عمل الطلبات"</strong> (<code class="bg-primaryBg px-1 rounded">production.workflow.index</code>). تعرض الشاشة لوحة كانبان بصرية لجميع الطلبات في خطوط الإنتاج.
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">2</span>
                            <div>
                                <strong>المراحل المتتابعة:</strong> جديد (New) -> قص (Cutting) -> خياطة (Sewing) -> مكواة (Ironing) -> تشطيب (Finishing) -> جاهز للشحن (Ready to ship).
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="step-number">3</span>
                            <div>
                                يمكن إسناد الأوردر لمشرف محدد، وتتبع تواريخ التسليم المستهدفة، وتسجيل إنجاز الترزية في كل مرحلة لتغذية محرك الرواتب تلقائياً.
                            </div>
                        </div>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('production.workflow.index') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-primaryBrown text-white text-xs font-bold hover:bg-mutedBrown transition">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            <span>فتح مسار عمل الطلبات</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Q21: Order Classifications -->
        <div class="question-item bg-white rounded-3xl border border-secondaryCream shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md" 
            data-category="production" 
            data-keywords="تصنيفات الطلبات ألوان مقاسات موديلات اكمام بناطيل order classifications styles sizes colors">
            <button type="button" onclick="toggleAccordion(this)" 
                class="guide-accordion-header w-full p-6 flex items-center justify-between text-right gap-4 cursor-pointer select-none">
                <div class="flex items-center gap-4">
                    <span class="w-10 h-10 rounded-2xl bg-secondaryCream/50 text-primaryBrown flex items-center justify-center text-lg font-bold shrink-0">
                        <i class="fa-solid fa-tags"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-teal-100 text-teal-800">مسار عمل الإنتاج</span>
                            <span class="text-xs text-mutedBrown hidden sm:inline"><i class="fa-solid fa-location-dot text-[10px]"></i> القائمة الجانبية > تصنيفات الطلبات</span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-darkText">
                            إزاي أهيئ تصنيفات الطلبات والمعايير (الألوان، المقاسات، الموديلات، الأكمام، البناطيل)؟
                        </h3>
                    </div>
                </div>
                <div class="guide-chevron text-mutedBrown text-base px-2 shrink-0">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </button>
            <div class="guide-accordion-content hidden p-6 sm:p-8 space-y-4 bg-white border-t border-secondaryCream/60">
                <div class="space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                    <p>
                        من القائمة الجانبية، ادخل على <strong>"تصنيفات الطلبات"</strong> (<code class="bg-primaryBg px-1 rounded">order_classifications.index</code>). توفر الشاشة تبويبات متكاملة لإدارة:
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-mutedBrown">
                        <li><strong>الألوان (Colors):</strong> إضافة الألوان وأكواد الـ Hex الخاصة بها.</li>
                        <li><strong>المقاسات (Sizes):</strong> المقاسات المعيارية (S, M, L, XL, 2XL...).</li>
                        <li><strong>الموديلات (Styles):</strong> تصميمات وتشكيلات السكرب والبالطو الطبي.</li>
                        <li><strong>أنواع البناطيل والأكمام (Pant & Sleeve Types):</strong> (كم طويل، نص كم، بنطلون عادي، جوجر...).</li>
                    </ul>
                    <p class="text-primaryBrown font-bold">
                        تظهر هذه التصنيفات فوراً في شاشات إنشاء الأوردرات وفواتير الشراء والمنتجات التامة، مما يوحد لغة العمل عبر المؤسسة.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('order_classifications.index') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-primaryBrown text-white text-xs font-bold hover:bg-mutedBrown transition">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            <span>فتح شاشة تصنيفات الطلبات</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Quick Feedback & Support Footer Card -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-secondaryCream shadow-sm flex flex-col md:flex-row items-center justify-between gap-6 no-print">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-primaryBrown/10 text-primaryBrown flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-headset"></i>
            </div>
            <div>
                <h4 class="text-base font-black text-darkText">هل تحتاج لمساعدة إضافية أو شرح لميزة معينة؟</h4>
                <p class="text-xs text-mutedBrown mt-0.5">فريق الدعم الفني وتطوير نظام تاجميديكس متاح دائماً لدعم فريق العمل وتوفير التدريب اللازم.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="px-4 py-2.5 bg-secondaryCream/60 hover:bg-secondaryCream text-darkText rounded-xl text-xs font-bold transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-up"></i>
                <span>العودة لأعلى الصفحة</span>
            </button>
        </div>
    </div>

</div>

<!-- JavaScript Logic for Live Search, Accordion, and Filters -->
<script>
    let activeCategory = 'all';

    function toggleAccordion(headerBtn) {
        const content = headerBtn.nextElementSibling;
        const isOpen = headerBtn.classList.contains('is-open');

        if (isOpen) {
            headerBtn.classList.remove('is-open');
            content.classList.add('hidden');
        } else {
            headerBtn.classList.add('is-open');
            content.classList.remove('hidden');
        }
    }

    function expandAllQuestions() {
        const items = document.querySelectorAll('.question-item');
        items.forEach(item => {
            if (!item.classList.contains('hidden')) {
                const header = item.querySelector('.guide-accordion-header');
                const content = item.querySelector('.guide-accordion-content');
                if (header && content) {
                    header.classList.add('is-open');
                    content.classList.remove('hidden');
                }
            }
        });
    }

    function collapseAllQuestions() {
        const items = document.querySelectorAll('.question-item');
        items.forEach(item => {
            const header = item.querySelector('.guide-accordion-header');
            const content = item.querySelector('.guide-accordion-content');
            if (header && content) {
                header.classList.remove('is-open');
                content.classList.add('hidden');
            }
        });
    }

    function selectCategory(cat, btn) {
        activeCategory = cat;

        // Update Pill Styles
        document.querySelectorAll('#categoryFilterContainer .badge-pill').forEach(b => {
            b.classList.remove('bg-primaryBrown', 'text-white', 'shadow-sm', 'active-cat-btn');
            b.classList.add('bg-secondaryCream/60', 'text-darkText');
        });
        btn.classList.add('bg-primaryBrown', 'text-white', 'shadow-sm', 'active-cat-btn');
        btn.classList.remove('bg-secondaryCream/60', 'text-darkText');

        filterQuestions();
    }

    function filterQuestions() {
        const query = document.getElementById('guideSearchInput').value.toLowerCase().trim();
        const clearBtn = document.getElementById('clearSearchBtn');
        const items = document.querySelectorAll('.question-item');
        let visibleCount = 0;

        if (query.length > 0) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }

        items.forEach(item => {
            const itemCat = item.getAttribute('data-category');
            const itemKeywords = (item.getAttribute('data-keywords') || '').toLowerCase();
            const textContent = item.textContent.toLowerCase();

            const matchesCategory = (activeCategory === 'all' || itemCat === activeCategory);
            const matchesQuery = (query === '' || itemKeywords.includes(query) || textContent.includes(query));

            if (matchesCategory && matchesQuery) {
                item.classList.remove('hidden');
                visibleCount++;
                // If there's an active search query, auto-expand the matched question
                if (query.length > 0) {
                    const header = item.querySelector('.guide-accordion-header');
                    const content = item.querySelector('.guide-accordion-content');
                    if (header && content) {
                        header.classList.add('is-open');
                        content.classList.remove('hidden');
                    }
                }
            } else {
                item.classList.add('hidden');
            }
        });

        document.getElementById('visibleCount').textContent = visibleCount;
        const noResults = document.getElementById('noResultsAlert');
        if (visibleCount === 0) {
            noResults.classList.remove('hidden');
        } else {
            noResults.classList.add('hidden');
        }
    }

    function clearSearch() {
        const input = document.getElementById('guideSearchInput');
        input.value = '';
        selectCategory('all', document.querySelector('#categoryFilterContainer .badge-pill'));
        filterQuestions();
        input.focus();
    }

    document.addEventListener('DOMContentLoaded', () => {
        const total = document.querySelectorAll('.question-item').length;
        const totalElem = document.getElementById('totalQuestionsCount');
        const visibleElem = document.getElementById('visibleCount');
        if (totalElem) totalElem.textContent = total + ' سؤالاً مفصلاً';
        if (visibleElem) visibleElem.textContent = total;
    });
</script>
@endsection
