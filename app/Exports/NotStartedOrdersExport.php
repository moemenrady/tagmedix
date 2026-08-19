<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class NotStartedOrdersExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithColumnWidths
{
    /**
     * جلب نفس الأوردرات الموجودة في عمود:
     * "لم يتم البدء"
     */
    public function collection()
    {
        $orders = Order::with([
            'customer',
            'items.style',
            'items.color',
            'items.size',
            'items.type',
            'items.pantType',
        ])
            ->where('status', 'new')
            ->orderBy('id')
            ->get();

        return $orders->map(function ($order) {

            $itemsDetails = [];

            foreach ($order->items as $index => $item) {

                $details = [];

                /*
                |--------------------------------------------------------------------------
                | الاستايل
                |--------------------------------------------------------------------------
                */
                if ($item->style?->name) {
                    $details[] = 'الاستايل: ' . $item->style->name;
                }

                /*
                |--------------------------------------------------------------------------
                | اللون
                |--------------------------------------------------------------------------
                */
                if ($item->color?->name) {
                    $details[] = 'اللون: ' . $item->color->name;
                }

                /*
                |--------------------------------------------------------------------------
                | المقاس
                |--------------------------------------------------------------------------
                */
                if ($item->size?->name) {
                    $details[] = 'المقاس: ' . $item->size->name;
                }

                /*
                |--------------------------------------------------------------------------
                | النوع
                |--------------------------------------------------------------------------
                */
                if ($item->type?->name) {
                    $details[] = 'النوع: ' . $item->type->name;
                }

                /*
                |--------------------------------------------------------------------------
                | Pant Type
                |--------------------------------------------------------------------------
                */
                if ($item->pantType?->name) {
                    $details[] = 'البانت تايب: ' . $item->pantType->name;
                }

                /*
                |--------------------------------------------------------------------------
                | الكم
                |--------------------------------------------------------------------------
                */
                if ($item->sleeve) {

                    $sleeve = match ($item->sleeve) {
                        'short' => 'قصير',
                        'long'  => 'طويل',
                        default => $item->sleeve,
                    };

                    $details[] = 'الكم: ' . $sleeve;
                }

                /*
                |--------------------------------------------------------------------------
                | الملاحظات
                |--------------------------------------------------------------------------
                */
                if (filled($item->notes)) {
                    $details[] = 'ملاحظات: ' . $item->notes;
                }

                /*
                |--------------------------------------------------------------------------
                | كل OrderItem له كمية مستقلة
                |--------------------------------------------------------------------------
                */
                $quantity = (int) ($item->quantity ?? 0);

                /*
                |--------------------------------------------------------------------------
                | شكل بند التصنيع
                |--------------------------------------------------------------------------
                */
                $itemText =
                    ($index + 1)
                    . ') '
                    . 'الكمية: '
                    . $quantity
                    . ' قطعة'
                    . "\n"
                    . implode(' | ', $details);

                $itemsDetails[] = $itemText;
            }

            /*
            |--------------------------------------------------------------------------
            | لو الأوردر مفيهوش Items
            |--------------------------------------------------------------------------
            */
            if (empty($itemsDetails)) {
                $itemsDetails[] = 'لا توجد تفاصيل تصنيع مسجلة لهذا الأوردر';
            }

            /*
            |--------------------------------------------------------------------------
            | إجمالي القطع
            |--------------------------------------------------------------------------
            */
            $totalPieces = $order->items->sum(function ($item) {
                return (int) ($item->quantity ?? 0);
            });

            return [
                'order_number'  => $order->order_number,
                'customer_name' => $order->customer?->name ?? '',
                'phone'         => $order->customer?->phone_1 ?? '',
                'total_pieces'  => $totalPieces,
                'items'         => implode("\n\n", $itemsDetails),
            ];
        });
    }

    /**
     * عناوين الأعمدة
     */
    public function headings(): array
    {
        return [
            'رقم الأوردر',
            'اسم العميل',
            'رقم الهاتف',
            'إجمالي القطع',
            'تفاصيل التصنيع',
        ];
    }

    /**
     * تنسيق الشيت
     */
    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        /*
        |--------------------------------------------------------------------------
        | إعدادات عامة
        |--------------------------------------------------------------------------
        */

        $sheet->setRightToLeft(true);

        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle('A1:E1')->applyFromArray([

            'font' => [
                'bold' => true,
                'size' => 13,
                'color' => [
                    'rgb' => 'FFFFFF',
                ],
            ],

            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => [
                    'rgb' => '6B563B',
                ],
            ],

            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],

            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => [
                        'rgb' => 'FFFFFF',
                    ],
                ],
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | ارتفاع Header
        |--------------------------------------------------------------------------
        */

        $sheet->getRowDimension(1)->setRowHeight(32);

        /*
        |--------------------------------------------------------------------------
        | بيانات الأوردرات
        |--------------------------------------------------------------------------
        */

        if ($highestRow >= 2) {

            $sheet->getStyle("A2:E{$highestRow}")->applyFromArray([

                'font' => [
                    'size' => 11,
                    'color' => [
                        'rgb' => '1F2937',
                    ],
                ],

                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],

                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => [
                            'rgb' => 'D1D5DB',
                        ],
                    ],
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | رقم الأوردر
            |--------------------------------------------------------------------------
            */

            $sheet->getStyle("A2:A{$highestRow}")->applyFromArray([

                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => [
                        'rgb' => '6B563B',
                    ],
                ],

                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_TOP,
                ],

                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => [
                        'rgb' => 'F8F4EE',
                    ],
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | اسم العميل
            |--------------------------------------------------------------------------
            */

            $sheet->getStyle("B2:B{$highestRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            /*
            |--------------------------------------------------------------------------
            | الهاتف
            |--------------------------------------------------------------------------
            */

            $sheet->getStyle("C2:C{$highestRow}")->applyFromArray([

                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_TOP,
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | إجمالي القطع
            |--------------------------------------------------------------------------
            */

            $sheet->getStyle("D2:D{$highestRow}")->applyFromArray([

                'font' => [
                    'bold' => true,
                    'size' => 13,
                    'color' => [
                        'rgb' => '166534',
                    ],
                ],

                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => [
                        'rgb' => 'DCFCE7',
                    ],
                ],

                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_TOP,
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | تفاصيل التصنيع
            |--------------------------------------------------------------------------
            */

            $sheet->getStyle("E2:E{$highestRow}")->applyFromArray([

                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],

                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => [
                        'rgb' => 'FFFBEB',
                    ],
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | حدود أقوى لتفاصيل التصنيع
            |--------------------------------------------------------------------------
            */

            $sheet->getStyle("E2:E{$highestRow}")
                ->getBorders()
                ->getLeft()
                ->setBorderStyle(Border::BORDER_MEDIUM);

            /*
            |--------------------------------------------------------------------------
            | ارتفاع الصفوف حسب طول التفاصيل
            |--------------------------------------------------------------------------
            */

            for ($row = 2; $row <= $highestRow; $row++) {

                $details = $sheet->getCell("E{$row}")->getValue();

                $lineCount = substr_count((string) $details, "\n") + 1;

                /*
                | كل بند فيه غالباً سطرين أو أكثر.
                | نزيد الارتفاع حسب عدد السطور.
                */

                $height = max(
                    65,
                    min(
                        250,
                        $lineCount * 22
                    )
                );

                $sheet->getRowDimension($row)->setRowHeight($height);
            }

            /*
            |--------------------------------------------------------------------------
            | Alternate Row
            |--------------------------------------------------------------------------
            */

            for ($row = 2; $row <= $highestRow; $row++) {

                if ($row % 2 === 0) {

                    $sheet->getStyle("B{$row}:C{$row}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID);

                    $sheet->getStyle("B{$row}:C{$row}")
                        ->getFill()
                        ->getStartColor()
                        ->setRGB('F9FAFB');
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Freeze Header
        |--------------------------------------------------------------------------
        */

        $sheet->freezePane('A2');

        /*
        |--------------------------------------------------------------------------
        | Auto Filter
        |--------------------------------------------------------------------------
        */

        if ($highestRow >= 1) {
            $sheet->setAutoFilter("A1:E{$highestRow}");
        }

        /*
        |--------------------------------------------------------------------------
        | Zoom
        |--------------------------------------------------------------------------
        */

        $sheet->getSheetView()->setZoomScale(90);

        /*
        |--------------------------------------------------------------------------
        | Page Setup
        |--------------------------------------------------------------------------
        */

        $sheet->getPageSetup()
            ->setOrientation(
                \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
            );

        $sheet->getPageSetup()
            ->setPaperSize(
                \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
            );

        return [];
    }

    /**
     * عرض الأعمدة
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 30,
            'C' => 20,
            'D' => 16,
            'E' => 90,
        ];
    }
}
