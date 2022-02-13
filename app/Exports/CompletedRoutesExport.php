<?php

namespace App\Exports;


use App\Models\ShipmentList\Shipment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;

class CompletedRoutesExport implements WithHeadings, WithMapping, WithCustomStartCell, WithStyles, WithEvents, FromCollection
{
    protected $shipments;

    public function __construct($shipments)
    {
        $this->shipments = $shipments;
    }

    public function collection()
    {
        $shipments = $this->shipments->with(['loadingZone', 'retailOutlets'])->get();
        $res = collect();
        $shipments->each(function ($shipment) use ($res) {
            $shipment->loadingZone->shipment = $shipment;
            $res->add($shipment->loadingZone);
            $shipment->retailOutlets->each(function ($retailOutlet) use ($shipment, $res) {
                $retailOutlet->shipment = $shipment;
                $res->add($retailOutlet);
            });
        });
        return $res;
    }

    public function map($data): array
    {
        return [
            [
                $data->name,
                $data->shipment->id,
            ]
        ];
    }


    public function headings(): array
    {
        $headingFirst = collect()
            ->pad(20, '')
            ->add('Точка загрузки')
            ->pad(30, '')
            ->add('Точка выгрузки')
            ->pad(40, '')
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
                '',
                'Фактическое прибытие на загрузку',
                '',
                'Факт убытия с точки загрузки',
                '',
                'Время нахождения в точке загрузки (время от прибытия в точку до убытия из точки)',
                'Отклонение от планового времени прибытия',
                'Индикатор отклонения прибытия на загрузку',
                '',
                'Плановое прибытие на выгрузку (окно)',
                '',
                'Фактическое прибытие на выгрузку (в геозону)',
                '',
                'Фактическое убытие с точки выгрузки (из геозоны)',
                '',
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
                $sheet = $event->sheet->getDelegate();
                // Cell sizes
                $sheet->getRowDimension('1')->setRowHeight(53);
                $sheet->getRowDimension('2')->setRowHeight(23);
                $sheet->getRowDimension('3')->setRowHeight(113);
//                $sheet->getRowDimension('5')->setRowHeight(42);
//                $sheet->getRowDimension('6')->setRowHeight(60);
//                $sheet->getRowDimension('7')->setRowHeight(29);
//                $sheet->getRowDimension('8')->setRowHeight(43);
//                $sheet->getRowDimension('9')->setRowHeight(43);
//                $sheet->getRowDimension('10')->setRowHeight(58);
                # Columns sizes
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension('T')->setWidth(1.63);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(13.50);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(11);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(13.50);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(12.50);
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(12.50);
                $event->sheet->getDelegate()->getColumnDimension('O')->setWidth(16.15);
                $event->sheet->getDelegate()->getColumnDimension('Q')->setWidth(14);
                $event->sheet->getDelegate()->getColumnDimension('P')->setWidth(17.75);
                $event->sheet->getDelegate()->getColumnDimension('L')->setWidth(13.25);
                $event->sheet->getDelegate()->getColumnDimension('K')->setWidth(13.75);
                $event->sheet->getDelegate()->getColumnDimension('M')->setWidth(12.17);
                $event->sheet->getDelegate()->getColumnDimension('N')->setWidth(12.17);
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth(11.50);
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth(11.50);
                $event->sheet->getDelegate()->getColumnDimension('I')->setWidth(11.50);
                $event->sheet->getDelegate()->getColumnDimension('J')->setWidth(11.50);
                $event->sheet->getDelegate()->getColumnDimension('R')->setWidth(16.75);
                $event->sheet->getDelegate()->getColumnDimension('S')->setWidth(25.33);
                $event->sheet->getDelegate()->getColumnDimension('AD')->setWidth(1.63);
                $event->sheet->getDelegate()->getColumnDimension('AN')->setWidth(1.63);
                $event->sheet->getDelegate()->getColumnDimension('AT')->setWidth(1.63);
                $event->sheet->getDelegate()->getColumnDimension('AY')->setWidth(1.63);
                $event->sheet->getDelegate()->getColumnDimension('BC')->setWidth(1.63);
                $event->sheet->getDelegate()->getColumnDimension('U')->setWidth(10.20);
                $event->sheet->getDelegate()->getColumnDimension('W')->setWidth(10.20);
                $event->sheet->getDelegate()->getColumnDimension('Y')->setWidth(10.20);
                $event->sheet->getDelegate()->getColumnDimension('AE')->setWidth(10.20);
                $event->sheet->getDelegate()->getColumnDimension('AB')->setWidth(11.20);
                $event->sheet->getDelegate()->getColumnDimension('AG')->setWidth(10.20);
                $event->sheet->getDelegate()->getColumnDimension('AI')->setWidth(10.20);
                $event->sheet->getDelegate()->getColumnDimension('AO')->setWidth(20.20);
                $event->sheet->getDelegate()->getColumnDimension('AA')->setWidth(17.20);
                $event->sheet->getDelegate()->getColumnDimension('AC')->setWidth(11.20);
                $event->sheet->getDelegate()->getColumnDimension('AF')->setWidth(12.50);
                $event->sheet->getDelegate()->getColumnDimension('AH')->setWidth(11.50);
                $event->sheet->getDelegate()->getColumnDimension('AJ')->setWidth(7.50);
                $event->sheet->getDelegate()->getColumnDimension('AK')->setWidth(17.50);
                $event->sheet->getDelegate()->getColumnDimension('AL')->setWidth(13.50);
                $event->sheet->getDelegate()->getColumnDimension('AM')->setWidth(13.50);
                $event->sheet->getDelegate()->getColumnDimension('AP')->setWidth(14.50);
                $event->sheet->getDelegate()->getColumnDimension('AQ')->setWidth(14.50);
                $event->sheet->getDelegate()->getColumnDimension('AR')->setWidth(14.50);
                $event->sheet->getDelegate()->getColumnDimension('AS')->setWidth(14.50);
                $event->sheet->getDelegate()->getColumnDimension('AU')->setWidth(12.25);
                $event->sheet->getDelegate()->getColumnDimension('AV')->setWidth(12.25);
                $event->sheet->getDelegate()->getColumnDimension('AW')->setWidth(8.50);
                $event->sheet->getDelegate()->getColumnDimension('AX')->setWidth(8.50);
                $event->sheet->getDelegate()->getColumnDimension('AZ')->setWidth(12.50);
                $event->sheet->getDelegate()->getColumnDimension('BA')->setWidth(14.50);
                $event->sheet->getDelegate()->getColumnDimension('BB')->setWidth(12.50);
                $event->sheet->getDelegate()->getColumnDimension('BD')->setWidth(13.50);
                $event->sheet->getDelegate()->getColumnDimension('BE')->setWidth(13.50);
                // Merge cells
                # first headings merge
                $sheet->mergeCells('U2:AC2');
                $sheet->mergeCells('AE2:AM2');
                $sheet->mergeCells('AO2:AS2');
                # second headings merge
                $sheet->mergeCells('U3:V3');
                $sheet->mergeCells('W3:X3');
                $sheet->mergeCells('Y3:Z3');
                $sheet->mergeCells('AE3:AF3');
                $sheet->mergeCells('AG3:AH3');
                $sheet->mergeCells('AI3:AJ3');
                $sheet->mergeCells('AW3:AX3');
                # third heading merge
                $sheet->mergeCells('A3:A4');
                $sheet->mergeCells('B3:B4');
                $sheet->mergeCells('C3:C4');
                $sheet->mergeCells('D3:D4');
                $sheet->mergeCells('E3:E4');
                $sheet->mergeCells('F3:F4');
                $sheet->mergeCells('G3:G4');
                $sheet->mergeCells('H3:H4');
                $sheet->mergeCells('I3:I4');
                $sheet->mergeCells('J3:J4');
                $sheet->mergeCells('K3:K4');
                $sheet->mergeCells('L3:L4');
                $sheet->mergeCells('M3:M4');
                $sheet->mergeCells('N3:N4');
                $sheet->mergeCells('O3:O4');
                $sheet->mergeCells('P3:P4');
                $sheet->mergeCells('Q3:Q4');
                $sheet->mergeCells('R3:R4');
                $sheet->mergeCells('S3:S4');
            },
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $baseFill = $this->fillSolid('FFFF00');


        $firstHeading = array_merge($baseFill, [
            'font' => [
                'name' => 'Calibri',
                'bold' => false,
                'size' => 12,
            ],
            'alignment' => [
                'vertical' => 'center',
                'horizontal' => 'center',
            ],
        ]);

        $secondHeading = array_merge($baseFill, [
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'size' => 11,
            ],
            'alignment' => [
                'vertical' => 'center',
                'horizontal' => 'center',
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => [
                        'rgb' => '000000'
                    ]
                ]
            ]
        ]);


        return [
            'U2'  => $firstHeading,
            'AE2' => $firstHeading,
            'AO2' => $firstHeading,
            3 => $secondHeading,
            4 => $secondHeading,
            'F3' => $this->fillSolid('FFC000'),
            'AZ3' => $this->fillSolid('92D050'),
            'AZ4' => $this->fillSolid('92D050'),
            'BB3' => $this->fillSolid('FF0000'),
            'BB4' => $this->fillSolid('FF0000'),
            'G3' => $this->fillSolid('FFC000'),
            'T3' => $this->clearCell(),
            'AD3' => $this->clearCell(),
            'AN3' => $this->clearCell(),
            'AT3' => $this->clearCell(),
            'BC3' => $this->clearCell(),
            'AY3' => $this->clearCell(),
            'T4' => $this->clearCell(),
            'AD4' => $this->clearCell(),
            'AN4' => $this->clearCell(),
            'AT4' => $this->clearCell(),
            'AY4' => $this->clearCell(),
            'BC4' => $this->clearCell(),
        ];
    }

    protected function clearCell () {
        return array_merge($this->fillSolid('FFFFFF'), [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => [
                        'rgb' => 'FFFFFF'
                    ]
                ]
            ]
        ]);
    }

    protected function fillSolid (string $rgb) {
        return [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => $rgb
                ]
            ]
        ];
    }

    public function startCell(): string
    {
        return 'A2';
    }
}
