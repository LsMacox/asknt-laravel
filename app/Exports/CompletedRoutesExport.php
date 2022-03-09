<?php

namespace App\Exports;


use App\Models\LoadingZone;
use App\Models\ShipmentList\Shipment;
use App\Models\Wialon\WialonNotification;
use Illuminate\Support\Carbon;
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

class CompletedRoutesExport implements WithHeadings, FromCollection, WithStyles, WithCustomStartCell, WithEvents, WithMapping
{
    protected $shipments;

    public function __construct($shipments)
    {
        $this->shipments = $shipments;
    }

    public function collection()
    {
        $shipments = $this->shipments->with(['loadingZones' => function ($query) {
            $query->withTrashed();
        }, 'retailOutlets' => function ($query) {
            $query->withTrashed()->with('shipmentOrders');
        }, 'wialonNotifications' => function ($query) {
            $query->withTrashed();
        }])->get();

        $res = collect();

        $shipments->each(function ($shipment) use ($res) {
            $res->add($shipment);
            $shipment->retailOutlets()->withTrashed()->each(function ($retailOutlet) use ($shipment, $res) {
                $retailOutlet->shipment = $shipment;
                $res->add($retailOutlet);
            });
        });

        return $res;
    }

    public function map($data): array
    {
        $shipment = $data->shipment ?? $data;

        $wNotifications = $shipment->wialonNotifications;

        $actionGeofences = $shipment->actionGeofences()
            ->get()
            ->sortBy('created_at');

        $firstGeofence = $actionGeofences->where('is_entrance', true)->first();
        $lastGeofence = $actionGeofences->where('is_entrance', false)->last();

        $loadingType = $shipment->actionGeofences()->where('pointable_type', LoadingZone::getMorphClass())->get();
        $loadingEntranceDate = optional($loadingType->where('is_entrance', true)->first())->created_at;
        $loadingDepartureDate = optional($loadingType->where('is_entrance', false)->first())->created_at;

        $lPlanDate = Carbon::parse($shipment->date->format('d.m.Y').' '.$shipment->time->format('H:i'));
        $allWeights = $shipment->retailOutlets->pluck('shipmentOrders')->flatten(1)->sum('weight');

        if ($data instanceof Shipment) {
            return [
                $shipment->loadingZones->first()->name ?? '',
                $shipment->id ?? '',
                '',
                '',
                Shipment::markToString($shipment->mark),
                !empty($shipment->car) ? $wNotifications->first()->object_id : '',
                empty($shipment->car) ? $wNotifications->first()->object_id : '',
                $shipment->car ?? '',
                $shipment->trailer ?? '',
                $shipment->weight,
                $shipment->driver,
                $shipment->carrier,
                '',
                '',
                '',
                $shipment->retailOutlets->count() ?? '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                $lastGeofence->created_at->diffInMinutes($firstGeofence->created_at),
                $actionGeofences->sum('mileage'),
                $allWeights,
                '',
                $shipment->temperature['from'].'-'.$shipment->temperature['to'],
                '',
                $loadingType->where('is_entrance', false)->first()->temp ?? '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ' ',
            ];
        }

        $retailOutletActionGeofences = $data->actionWialonGeofences()->get();

        $shipmentRetailOutlet = $data->shipmentRetailOutlet()->first();

        $actDeparture = $data->actionWialonGeofences()->where('is_entrance', false)->first();
        $pointable = $actDeparture->pointable()->withTrashed()->first();
        $actNextEntrance = $shipment->retailOutlets->where('turn',
            $pointable->turn + 1 <= $shipment->retailOutlets->count()
            ? $pointable->turn + 1
            : $pointable->turn
        )->first()
            ->actionWialonGeofences()->where('is_entrance', true)->first();

        $actEntranceDate = optional($data->actionWialonGeofences()->where('is_entrance', true)->first())->created_at;
        $actDepartureDate = optional($actDeparture)->created_at;

        $actTemps = $wNotifications->where('action_type', WialonNotification::ACTION_TEMP)
            ->first()
            ->actionTemps()->get();

        $actBetweenPointsTemps = $wNotifications
            ->where('action_type', WialonNotification::ACTION_TEMP)
            ->first()
            ->actionTemps()
            ->whereBetween(
                'created_at',
                [
                    $actEntranceDate,
                    $actDepartureDate
                ]
            )
            ->get()
            ->sortBy('created_at');

        $avgTemp = $actBetweenPointsTemps->avg('temp');
        $isTempNormal = $avgTemp > $shipment->temperature['from'] && $avgTemp < $shipment->temperature['to'];

        $actTempsCount = $actTemps->count();
        $normTemp = $actTemps->where('temp', '>=', $shipment->temperature['from'] - 2)->where('temp', '<=', $shipment->temperature['to'] + 2)
            ->count();
        $warnTemp = $actTemps->filter(function ($act) use ($shipment) {
            $from = (double) $shipment->temperature['from'] - 2.1;
            $to = (double) $shipment->temperature['to'] + 4;
            return $act->temp <= $from || $act->temp >= $to;
        })->count();
        $criticalTemp = $actTemps->filter(function ($act) use ($shipment) {
            $from = (double) $shipment->temperature['from'] - 4.1;
            $to = (double) $shipment->temperature['to'] + 4.1;
            return $act->temp <= $from || $act->temp >= $to;
        })->count();

        $tempPercent = function ($count) use ($actTempsCount) {
            return $count ? round(100 * ($count / $actTempsCount)) . '%' : null;
        };

        $planDateFrom = Carbon::parse($shipmentRetailOutlet->date->format('d.m.Y').' '.$shipmentRetailOutlet->arrive_from->format('H:i'));
        $planDateTo = Carbon::parse($shipmentRetailOutlet->date->format('d.m.Y').' '.$shipmentRetailOutlet->arrive_to->format('H:i'));

        return [
            $shipment->loadingZones->first()->name ?? '',
            $shipment->id ?? '',
            $data->shipmentOrders->pluck('id')->implode(', '),
            '',
            Shipment::markToString($shipment->mark),
            !empty($shipment->car) ? $wNotifications->first()->object_id : '',
            empty($shipment->car) ? $wNotifications->first()->object_id : '',
            $shipment->car ?? '',
            $shipment->trailer ?? '',
            $shipment->weight,
            $shipment->driver,
            $shipment->carrier,
            $data->turn ?? '',
            $data->turn ?? '',
            $data->code ?? '',
            '',
            $data->name ?? '',
            $data->name ?? '',
            $data->address ?? '',
            '',
            $shipment->date->format('d.m.Y'),
            $shipment->time->format('H:i'),
            optional($loadingEntranceDate)->format('d.m.Y') ?? '',
            optional($loadingEntranceDate)->format('H:i') ?? '',
            optional($loadingDepartureDate)->format('d.m.Y') ?? '',
            optional($loadingDepartureDate)->format('H:i') ?? '',
            optional($loadingEntranceDate)->diffInMinutes($loadingDepartureDate) ?? '',
            optional($loadingEntranceDate)->diffInMinutes($lPlanDate) ?? '',
            optional($loadingEntranceDate) ? $loadingEntranceDate->between($lPlanDate->subMinutes(5), $lPlanDate->addMinutes(5)) ? 'Вовремя' : 'Опоздал' : '',
            '',
            $planDateFrom->format('d.m.Y'),
            $shipmentRetailOutlet->arrive_from->format('H:i') . '-' . $shipmentRetailOutlet->arrive_to->format('H:i'),
            optional($actEntranceDate)->format('d.m.Y') ?? '',
            optional($actEntranceDate)->format('H:i') ?? '',
            optional($actDepartureDate)->format('d.m.Y') ?? '',
            optional($actDepartureDate)->format('H:i') ?? '',
            optional($actEntranceDate)->diffInMinutes($actDepartureDate) ?? '',
            optional($actEntranceDate)->diffInMinutes($planDateFrom) ?? '',
            optional($actEntranceDate) ? $actEntranceDate->between($planDateFrom, $planDateTo) ? 'Вовремя' : 'Опоздал' : '',
            '',
            optional($actDepartureDate)->diffInMinutes($actNextEntrance->created_at) ?? '',
            '',
            $data->shipmentOrders()->get()->implode('weight', ', '),
            $data->shipmentOrders()->get()->implode('product', ', '),
            $shipment->temperature['from'].'-'.$shipment->temperature['to'],
            '',
            $loadingType->where('is_entrance', false)->first()->temp ?? '',
            $retailOutletActionGeofences->where('is_entrance', true)->first()->temp,
            $avgTemp ?? '',
            $isTempNormal ? 'да' : 'нет',
            '',
            $tempPercent($normTemp) ?? '' ,
            $tempPercent($warnTemp) ?? '',
            $tempPercent($criticalTemp) ?? '',
            '',
            '',
            ' ',
        ];
    }

    public function headings(): array
    {
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
            [' '],
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
                $shipmentStartDate = $this->shipments->get()->first()->created_at;
                $shipmentEndDate = $this->shipments->get()->last()->created_at;
                $event->sheet->setCellValue('A1', 'Температурный отчет по уровню сервиса сформирован '.now()->format('d.m.Y H:i').' за период с '.
                    $shipmentStartDate->format('d.m.Y').' по '.
                    $shipmentEndDate->format('d.m.Y')
                );
                $event->sheet->setCellValue('U2', 'Точка загрузки');
                $event->sheet->setCellValue('AE2', 'Точка выгрузки');
                $event->sheet->setCellValue('AO2', 'Техническая информация');
                // Cell sizes
                $sheet->getRowDimension('1')->setRowHeight(53);
                $sheet->getRowDimension('2')->setRowHeight(23);
                $sheet->getRowDimension('3')->setRowHeight(113);
                // Set content cells row height
                $contentCellStart = 5;
                $this->collection()->each(function ($row) use ($sheet, &$contentCellStart) {
                    $sheet->getRowDimension($contentCellStart)->setRowHeight(45);
                    $contentCellStart++;
                });
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
                $sheet->mergeCells('A1:G1');
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

        $res = [
            'A1'  => [
                'font' => [
                    'name' => 'Calibri',
                    'bold' => false,
                    'size' => 18,
                ],
                'alignment' => [
                    'vertical' => 'center',
                    'horizontal' => 'center',
                    'wrapText' => true,
                ],
            ],
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

        $contentCellStart = 5;
        $this->collection()->each(function ($row) use ($sheet, &$res, &$contentCellStart) {
            if ($row instanceof Shipment) {
                $res[$contentCellStart] = array_merge($this->fillSolid('A6A6A6'), [
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
            } else {
                $res[$contentCellStart] = [
                    'font' => [
                        'name' => 'Arial',
                        'bold' => false,
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
                ];
            }
            $contentCellStart++;
        });

        return $res;
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
