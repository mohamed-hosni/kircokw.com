<?php

namespace App\Exports;

use Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportMaintenanceAll implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $maintenances = DB::table('maintenances')
            ->join('buildings', 'buildings.id', '=', 'maintenances.building_id')
            ->leftJoin('apartments', 'apartments.id', '=', 'maintenances.apartment_id')
            ->select([
                'buildings.name as building_name',
                'apartments.name as apartment_name',
                'maintenances.name as name',
                'maintenances.cost as price',
                'maintenances.note as note',
                'maintenances.invoice_date as invoice_date',
                DB::raw('DATE(maintenances.created_at) as created_at')
            ])
            ->whereNull('maintenances.deleted_at');

        if (Auth::user()->role_id == 2) {
            $maintenances->where('maintenances.user_id', Auth::user()->id);
        }

        // 👇 فلترة التاريخ
        if ($this->request->filled('from')) {
            $maintenances->whereDate('maintenances.invoice_date', '>=', $this->request->from);
        }

        if ($this->request->filled('to')) {
            $maintenances->whereDate('maintenances.invoice_date', '<=', $this->request->to);
        }

        return $maintenances->get();
    }

    public function headings(): array
    {
        return [
            __('pages.building_name'),
            __('pages.appartment_name'),
            'الأسم',
            'القيمة',
            __('pages.note'),
            __('pages.maintenance_invoice_date'),
            __('pages.input_date'),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->setTitle(__('pages.fees'));
                $event->sheet->getStyle('A1:G1')->applyFromArray([
                    'font' => ['bold' => true],
                ]);
            },
        ];
    }
}
