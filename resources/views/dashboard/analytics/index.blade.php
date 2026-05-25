@extends('layouts.app')

@section('content')
    <style>
        :root {
            --text-main: #ffffff;
            --text-muted: #9ca3af;
            --glass-border: rgba(255, 255, 255, 0.1);
            --primary: #00f2fe;
            --accent: #ff6b6b;
            --card-bg: rgba(255, 255, 255, 0.03);
            --success: #2ed573;
        }

        .reports-container {
            max-width: 1000px;
            margin: 0 auto;
            animation: fadeIn 0.6s ease forwards;
            direction: rtl;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-title {
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(to left, var(--primary), #a6ffcb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2rem;
            text-align: right;
        }

        /* ====== مربعات الإحصائيات العلوي ====== */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card-report {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.01));
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 24px;
            border: 1px solid var(--glass-border);
            text-align: center;
            transition: 0.3s;
        }

        .stat-card-report:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .stat-card-report h4 {
            color: var(--text-muted);
            font-size: 1rem;
            margin-bottom: 15px;
        }

        .stat-card-report h2 {
            font-size: 2.5rem;
            font-weight: 900;
            margin: 10px 0;
        }

        /* ====== الفلتر ====== */
        .filter-card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-group label {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .filter-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            padding: 10px;
            color: #fff;
            outline: none;
        }

        .btn-search {
            background: var(--primary);
            color: #000;
            border: none;
            border-radius: 10px;
            padding: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-search:hover {
            opacity: 0.9;
        }

        /* ====== الجدول ====== */
        .table-container {
            background: var(--card-bg);
            border-radius: 24px;
            border: 1px solid var(--glass-border);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .table-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--glass-border);
            background: rgba(255, 255, 255, 0.02);
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
        }

        .report-table th {
            padding: 1.2rem;
            color: var(--text-muted);
            font-weight: 500;
            background: rgba(0, 0, 0, 0.1);
        }

        .report-table td {
            padding: 1.2rem;
            border-bottom: 1px solid var(--glass-border);
            color: var(--text-main);
        }

        .report-table tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        /* شريط النسبة المئوية */
        .progress-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .progress-bar-bg {
            flex-grow: 1;
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(to right, var(--primary), #00d2ff);
            border-radius: 10px;
            transition: width 1s ease-in-out;
        }

        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 1.8rem;
            }
        }
    </style>

    <div class="reports-container">

        <h1 class="page-title">التقارير التحليلية</h1>

        <div class="filter-card">
            <form action="{{ route('analytics.index') }}" method="GET" class="filter-grid">
                <div class="filter-group">
                    <label>من تاريخ</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="filter-input">
                </div>
                <div class="filter-group">
                    <label>إلى تاريخ</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="filter-input">
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn-search">تحديث البيانات 📊</button>
                </div>
                <div class="filter-group">
                    <a href="{{ route('analytics.index') }}" class="filter-input"
                        style="text-align:center; text-decoration:none;">إعادة ضبط</a>
                </div>
            </form>
        </div>

        <div class="summary-grid">
            <div class="stat-card-report">
                <h4>إجمالي ما تم صرفه</h4>
                <h2 style="color: var(--accent);">{{ number_format($totalSpent, 2) }} <small
                        style="font-size: 1rem;">ج.م</small></h2>
                <p style="color: var(--text-muted); font-size: 0.8rem;">بناءً على الفترة المحددة</p>
            </div>

            <div class="stat-card-report">
                <h4>أعلى بند صرف</h4>
                <h3 style="color: var(--primary); margin-top: 10px;">{{ $topType->name ?? 'لا يوجد' }}</h3>
                <h2 style="font-size: 1.8rem;">{{ number_format($topType->expenses_sum_amount ?? 0, 2) }} <small
                        style="font-size: 0.9rem;">ج.م</small></h2>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h3 style="color: #fff; margin:0;">توزيع المصاريف لكل بند</h3>
            </div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>النوع</th>
                        <th>عدد العمليات</th>
                        <th>إجمالي الصرف</th>
                        <th>النسبة من الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($typesReport as $report)
                        @php
                            $percentage = $totalSpent > 0 ? ($report->expenses_sum_amount / $totalSpent) * 100 : 0;
                        @endphp
                        <tr>
                            <td style="font-weight: bold;">{{ $report->name }}</td>
                            <td><span
                                    style="background: rgba(255,255,255,0.1); padding: 4px 12px; border-radius: 8px;">{{ $report->expenses_count }}</span>
                            </td>
                            <td style="color: var(--primary); font-weight: bold;">
                                {{ number_format($report->expenses_sum_amount, 2) }} ج.م</td>
                            <td>
                                <div class="progress-wrapper">
                                    <span style="font-size: 0.8rem; min-width: 35px;">{{ round($percentage, 1) }}%</span>
                                    <div class="progress-bar-bg">
                                        <div class="progress-bar-fill" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 3rem; color: var(--text-muted);">لا توجد
                                بيانات لعرضها في هذه الفترة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
