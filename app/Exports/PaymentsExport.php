<?php

namespace App\Exports;

use App\Services\PaymentService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PaymentsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithEvents,
    WithCustomStartCell
{
    protected $from;
    protected $to;

    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function collection()
    {
        $service = app(PaymentService::class);
        return $service->financialCenter($this->from, $this->to)['payments'];
    }

    public function startCell(): string
    {
        return 'A2'; // ترك الصف الأول لعنوان التقرير
    }

    public function headings(): array
    {
        return [
            'التاريخ',
            'نوع العملية',
            'المبلغ (ج.م)',
            'رقم الطلب',
            'اسم العميل',
            'المسؤول',
            'ملاحظات',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->payment_date->format('Y-m-d'),
            $payment->isOrderAdvance() ? 'مقدم طلب' : 'توريد شركة شحن',
            $payment->amount,
            $payment->order?->order_number ?? '-',
            $payment->order?->customer?->name ?? '-',
            $payment->creator?->name ?? 'النظام',
            $payment->note ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $sheet->setRightToLeft(true); // تفعيل اتجاه اليمين لليسار

        // تنسيق صف العناوين (رقم 2) بالأخضر الغامق المميز
        $sheet->getStyle('A2:G2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '205A43']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(35);

        // تنسيق صفوف البيانات من الصف 3 حتى النهاية
        if ($highestRow >= 3) {
            $sheet->getStyle("A3:G{$highestRow}")->applyFromArray([
                'font' => ['size' => 11],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true, // التفاف النص عشان الملاحظات الطويلة
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']],
                ],
            ]);

            // حلقة تكرارية لتمييز الألوان وتوسيع الارتفاع ليكون مناسب للموبايل
            for ($row = 3; $row <= $highestRow; $row++) {
                $sheet->getRowDimension($row)->setRowHeight(40);

                $typeText = trim($sheet->getCell("B{$row}")->getValue());

                // تلوين خلية "نوع العملية" بناءً على النوع للتفرقة البصرية السريعة
                if ($typeText === 'مقدم طلب') {
                    $sheet->getStyle("B{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'DBEAFE']], // أزرق فاتح
                        'font' => ['color' => ['rgb' => '1E3A8A'], 'bold' => true] // أزرق غامق
                    ]);
                } elseif ($typeText === 'توريد شركة شحن') {
                    $sheet->getStyle("B{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'C0E7FF']], // نيلي فاتح
                        'font' => ['color' => ['rgb' => '312E81'], 'bold' => true] // نيلي غامق
                    ]);
                }

                // تمييز عمود "المبلغ" ليكون بخط عريض دائمًا
                $sheet->getStyle("C{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '111827']]
                ]);
            }
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // تصميم عنوان الصفحة المدمج في الأعلى
                $sheet->mergeCells('D1:G1');
                $sheet->setCellValue('D1', 'تقرير مركز الماليات (من ' . $this->from . ' إلى ' . $this->to . ')');
                $sheet->getStyle('D1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '205A43']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // تاريخ التصدير في الأعلى يميناً
                $sheet->mergeCells('A1:B1');
                $sheet->setCellValue('A1', 'تاريخ التصدير: ' . now()->format('Y/m/d H:i'));
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '6B7280']], // رمادي
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->getRowDimension(1)->setRowHeight(45);
                $sheet->freezePane('A3'); // تثبيت العناوين الأساسية عند التمرير لأسفل (Scroll)
                $sheet->getSheetView()->setZoomScale(100);
            },
        ];
    }

    public function columnWidths(): array
    {
        // 7 أعمدة مقاساتهم مضبوطة ومدروسة للموبايل والديسكتوب
        return [
            'A' => 15, // التاريخ
            'B' => 18, // النوع
            'C' => 16, // المبلغ
            'D' => 15, // رقم الطلب
            'E' => 25, // العميل
            'F' => 18, // المسؤول
            'G' => 35, // الملاحظات
        ];
    }
}
