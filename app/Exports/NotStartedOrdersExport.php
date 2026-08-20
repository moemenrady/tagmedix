<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class NotStartedOrdersExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithColumnWidths,
    WithEvents,
    WithCustomStartCell
{
    public function collection()
    {
        $orders = Order::with([
            'customer',
            'items.style',
            'items.color',
            'items.size',
            'items.type',
            'items.pantType'
        ])
            ->where('status', 'new')
            ->orderBy('id')
            ->get();

        $rows = [];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {

                $sleeve = match ($item->sleeve) {
                    'short' => 'نصف كم',
                    'long'  => 'طويل',
                    default => $item->sleeve ?? '-',
                };

                $rows[] = [
                    'order_number'  => $order->order_number,
                    'customer_name' => $order->customer?->name ?? '-',
                    'design'        => $item->style?->name ?? '-',
                    'product_type'  => $item->type?->name ?? '-',
                    'pant_type'     => $item->pantType?->name ?? '-',
                    'fit_style'     => $item->size?->name ?? '-',
                    'color'         => $item->color?->name ?? '-',
                    'sleeve'        => $sleeve,
                    'extra_size'    => $item->quantity ? $item->quantity . ' قطعة' : '-',
                    'notes'         => $item->notes ?? '-',
                ];
            }
        }

        return collect($rows);
    }

    public function startCell(): string
    {
        return 'A2'; // ترك الصف الأول للعنوان المخصص
    }

    public function headings(): array
    {
        return [
            'رقم الأوردر',
            'اسم العميلة',
            'اسم التصميم',
            'نوع المنتج',
            'نوع البنطلون',
            'الستايل / الشكل',
            'اللون',
            'نوع الكم',
            'القياس الإضافي',
            'ملاحظات خاصة',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $sheet->setRightToLeft(true);

        // تنسيق صف العناوين (رقم 2) بالأخضر الغامق مثل الصورة
        $sheet->getStyle('A2:J2')->applyFromArray([
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

        // تنسيق صفوف البيانات
        if ($highestRow >= 3) {
            $sheet->getStyle("A3:J{$highestRow}")->applyFromArray([
                'font' => ['size' => 11],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']],
                ],
            ]);

            // تلوين خلايا عمود "اللون" برمجياً لإبهارك!
            for ($row = 3; $row <= $highestRow; $row++) {
                $sheet->getRowDimension($row)->setRowHeight(40); // ارتفاع ممتاز للموبايل
                $colorText = trim($sheet->getCell("G{$row}")->getValue());

                $colorHex = match ($colorText) {
                    'كشمير'   => 'E4C1C1',
                    'زيتي'    => '6B8E23',
                    'أسود'    => '000000',
                    'بيبي بلو' => 'ADD8E6',
                    'موف'     => '9370DB',
                    'كحلي'    => '000080',
                    'بترولي'  => '005F69',
                    default   => null,
                };

                if ($colorHex) {
                    $fontColor = in_array($colorText, ['أسود', 'كحلي', 'بترولي']) ? 'FFFFFF' : '000000';
                    $sheet->getStyle("G{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $colorHex]],
                        'font' => ['color' => ['rgb' => $fontColor], 'bold' => true]
                    ]);
                }
            }
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // تصميم الصف الأول (العنوان والتاريخ)
                $sheet->mergeCells('D1:G1');
                $sheet->setCellValue('D1', 'أوردرات لم يتم البدء');
                $sheet->getStyle('D1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->mergeCells('A1:B1');
                $sheet->setCellValue('A1', 'تاريخ التصدير: ' . now()->format('Y/m/d'));
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->getRowDimension(1)->setRowHeight(45);
                $sheet->freezePane('A3'); // تثبيت العناوين عند التمرير
                $sheet->getSheetView()->setZoomScale(100);
            },
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 20,
            'C' => 16,
            'D' => 22,
            'E' => 22,
            'F' => 18,
            'G' => 15,
            'H' => 12,
            'I' => 15,
            'J' => 35,
        ];
    }
}
