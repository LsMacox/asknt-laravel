<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;

class CompletedRoutesExport implements WithHeadings, WithCustomStartCell, WithStyles, WithEvents
{
    public function headings(): array
    {
        $headingFirst = collect()
            ->pad(20, '')
            ->add('Точка загрузки')
            ->pad(27, '')
            ->add('Точка выгрузки')
            ->pad(33, '')
            ->add('Техническая информация')
            ->toArray();

        $pointHead = ['Дата', 'Время', 'Дата', 'Время', 'Дата', 'Время', 'мин', 'мин', ''];
        $technicalHead = ['мин', 'км', 'кг', '', '°C'];
        $tempHead = ['°C', '°C', '°C', 'в норме'];
        $tempPercentHead = ['%', '%', '%'];
        $countHead = ['шт', 'шт'];

        $headingLast = collect()
            ->pad(20, '')
            ->merge($pointHead)->add('')
            ->merge($pointHead)->add('')
            ->merge($technicalHead)->add('')
            ->merge($tempHead)->add('')
            ->merge($tempPercentHead)->add('')
            ->merge($countHead)->toArray();

        return [
            $headingFirst,
            [
                'Склад загрузки',
                '№ транспортировки',
                '№ поставки',
                'Название маршрута SAP (до 50 км, до 25 км. и т.д.)',
                'Индикатор транспорта собств. или наемный',
                'ID ТС',
                'ID прицепа',
                'Гос. номер ТС',
                'Гос. номер прицепа',
                'Грузоподьемность авто',
                'Водитель',
                'Наименование ТК',
                'Плановый порядок посещения точек',
                'Фактический порядок посещения точек',
                'Код SAP точки',
                '* Количество ТТ',
                'Наименование точки',
                'Вывеска',
                'Адрес точки',
                '',
                'Плановое прибытие на загрузку',
                'Фактическое прибытие на загрузку',
                'Факт убытия с точки загрузки',
                'Время нахождения в точке загрузки (время от прибытия в точку до убытия из точки)',
                'Отклонение от планового времени прибытия',
                'Индикатор отклонения прибытия на загрузку',
                '',
                'Плановое прибытие на выгрузку (окно)',
                'Фактическое прибытие на выгрузку (в геозону)',
                'Фактическое убытие с точки выгрузки (из геозоны)',
                'Время нахождения в точке выгрузки (время от прибытия в точку до убытия из точки)',
                'Отклонение от планового времени прибытия на выгрузку',
                'Индикатор отклонения прибытия на выгрузку % - верхне-во индикатор - на тт',
                '',
                'Длительность рейса, (факт. время от начала маршрута до завершения маршрута) (выезд из геозон загрузки и выгризки)',
                'Пробег до объекта ФАКТ',
                'Весовые характр. заказа',
                'Тип груза (если есть данные в SAP)',
                'Нормативная t для данного груза',
                '',
                't в кузове в момент загрузки (момент выезда из геозоны)',
                't в кузове авто в точке выгрузки (в момент въезда в геозону)',
                'Средняя t по маршруту ( с момента выезда из геозоны до момента въезда в геозону)',
                '',
                'Соответствует нормативной t',
                '% с отклонением на +2,1 +4,0 градуса',
                '% с отклонением на 4,1 градуса',
                '',
                'Кол-во t датчиков',
                'Наличие датчика двери',
            ],
            $headingLast
        ];

    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {

                $event->sheet->getDelegate()->getRowDimension('3')->setRowHeight(113);
//                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(50);
            },
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $baseFill = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'FFFF00'
                ]
            ]
        ];

        return [
            'U2'    => $baseFill,
            'AB2'    => $baseFill,
            'AH2'    => $baseFill,
            3    => array_merge($baseFill, [
                'font' => [
                    'name' => 'Arial',
                    'bold' => true,
                    'size' => 11,
                ],
                'alignment' => [
                    'vertical' => 'center'
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                          'color' => [
                              'rgb' => '000000'
                         ]
                    ]
                ]
            ]),
        ];
    }

    public function startCell(): string
    {
        return 'A2';
    }
}
