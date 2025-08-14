<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportAnnualReport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */

    private $apartments;
    private $monthSums = [];

    private $building_id;
    private $year;
    private $allMonths = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];

    public function __construct($request)
    {
        $this->building_id = $request->building_id;
        $this->year = date('Y',strtotime(explode('?', $request->date)[0]));
        $this->apartments = $this->collection();
    }

    public function collection()
    {
        // make annual excel sheets with column for each month
        $apartments = DB::table('apartments')
            ->leftJoin('tenants', 'tenants.apartment_id', '=', 'apartments.id')
            ->whereRaw('tenants.id IN (select max(id) from tenants group by apartment_id)')
            ->leftJoin('payments', 'payments.apartment_id', '=', 'apartments.id')
            ->leftJoin('financial_transactions', 'financial_transactions.id', '=', 'payments.financial_transaction_id')
            ->leftJoin('users', 'users.id', '=', 'payments.tenant_id')
            ->select([
                'apartments.name as apartment_name',
                'users.name as users_name',
                'tenants.start_date as start_date',
                'tenants.end_date as end_date',
                'payments.total_amount as price',
                'payments.id as payment_id',
                DB::raw('SUM(CASE WHEN payments.pay_monthes LIKE "%' . $this->year . '%" THEN payments.total_amount ELSE 0 END) as total_price'),
                DB::raw('GROUP_CONCAT(CASE WHEN payments.pay_monthes LIKE "%' . $this->year . '%" THEN payments.pay_monthes END SEPARATOR \',\') as paid_months')
            ])
            ->whereNull('apartments.deleted_at')
            ->whereNull('tenants.deleted_at')
            ->whereNull('payments.deleted_at')
            ->whereNull('financial_transactions.deleted_at')
            ->whereNull('users.deleted_at')
            // ->where('apartments.status', 1)
            ->where('apartments.building_id', $this->building_id)
            ->groupBy('tenants.id', 'apartments.name', 'users.name', 'tenants.start_date', 'tenants.end_date', 'payments.total_amount', 'payments.id')
            ->distinct()
            ->get();

        foreach ($this->allMonths as $month) {
            $this->monthSums[$month] = 0;
        }
        // Iterate through apartments
        foreach ($apartments as $apartment) {
            if (empty($apartment->paid_months)) {
                $apartments = $apartments->reject(function ($value, $key) use ($apartment, $apartments) {
                    return $value->payment_id == $apartment->payment_id;
                });
            }
            
            // Split the pay_monthes string into an array
            $pay_monthes = explode(',', $apartment->paid_months);
            // Create properties for all 12 months and set the price
            foreach ($this->allMonths as $month) {
                $yearMonth = $month . '-' . $this->year;
                // Calculate the monthly amount
                if (in_array($yearMonth, $pay_monthes)) {
                    $apartment->$yearMonth = (float)$apartment->total_price / count($pay_monthes);
                } else {
                    $apartment->$yearMonth = 0.0;
                }
                $this->monthSums[$month] += $apartment->$yearMonth;
            }
        }

        // $apartments = $apartments->map(function ($item, $key) use ($apartments) {
        //     if ($key > 0 && isset($apartments[$key - 1])) {
        //         $pay_monthes_0 = explode(',', $apartments[$key - 1]->paid_months);
        //         $pay_monthes_1 = explode(',', $item->paid_months);
        //         if ($item->users_name == $apartments[$key - 1]->users_name && $item->total_price / count($pay_monthes_1) == $apartments[$key - 1]->total_price / count($pay_monthes_0) ){
        //             $apartments[$key - 1]->total_price += $item->total_price;
        //             $apartments[$key - 1]->paid_months = implode(',', array_unique(array_merge($pay_monthes_0, $pay_monthes_1)));
        //             foreach ($this->allMonths as $month) {
        //                 $yearMonth = $month . '-' . $this->year;
        //                 $apartments[$key - 1]->$yearMonth += $item->$yearMonth;
        //             }

        //             return null;
        //         }
        //     }

        //     return $item;
        // })->filter();
        
        $mergedApartments = []; // this will replace your map/filter step

        foreach ($apartments as $apartment) {
            // If $mergedApartments is not empty, compare the current apartment
            // to the last inserted entry
            if (!empty($mergedApartments)) {
                $lastIndex = count($mergedApartments) - 1;
                $lastItem = $mergedApartments[$lastIndex];

                // Check if we want to merge
                $pay_monthes_0 = explode(',', $lastItem->paid_months);
                $pay_monthes_1 = explode(',', $apartment->paid_months);

                // Example of the same check you were doing:
                if (
                    $apartment->users_name === $lastItem->users_name
                    && ($apartment->total_price / count($pay_monthes_1))
                    === ($lastItem->total_price / count($pay_monthes_0))
                ) {
                    // Merge logic here:
                    $mergedApartments[$lastIndex]->total_price += $apartment->total_price;
                    $mergedApartments[$lastIndex]->paid_months = implode(
                        ',',
                        array_unique(array_merge($pay_monthes_0, $pay_monthes_1))
                    );

                    // Update all month columns
                    foreach ($this->allMonths as $month) {
                        $yearMonth = $month . '-' . $this->year;
                        $mergedApartments[$lastIndex]->$yearMonth += $apartment->$yearMonth;
                    }

                    // We merged the row, so *do not* add $apartment to the array again.
                    continue;
                }
            }

            // If it’s not a merge, just add this row
            $mergedApartments[] = $apartment;
        }
        $apartments = collect($mergedApartments);

        // sum the apartment revenu
        $totalSum = 0;
        foreach ($apartments as $apartment) {
            $totalSum += $apartment->total_price;
            $apartment->sum = $apartment->total_price;
            unset($apartment->payment_id);
            unset($apartment->total_price);
        }

        $sumRow = [
            'المجموع',
            '', // An empty cell
            '', // An empty cell
            '', // An empty cell
            '', // An empty cell
            '', // An empty cell
        ];
        foreach ($this->allMonths as $month) {
            $sumRow[] = $this->monthSums[$month];
        }
        array_push($sumRow, $totalSum);
        $apartments->push($sumRow);

        return $apartments;
    }

    public function headings(): array
    {
        // set title
        return [
            'أسم الوحدة',
            'أسم المستأجر',
            'بداية العقد',
            'نهاية العقد',
            'القيمة الإيجارية',
            'الشهور المدفوعة',
            ...$this->allMonths,
            'المجموع'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->setTitle("التقرير السنوي لعام " . $this->year);
                $event->sheet->getStyle('A1:Z1')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);
                // merge the last row and make it bold
                $event->sheet->mergeCells('A' . ($this->apartments->count() + 1) . ':F' . ($this->apartments->count() + 1));
                $event->sheet->getStyle('A' . ($this->apartments->count() + 1) . ':S' . ($this->apartments->count() + 1))->applyFromArray([
                    'font' => [
                        'bold' => true
                    ],
                    // merge
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                // make the last column bold
                $event->sheet->getStyle('S2:S' . ($this->apartments->count() + 1))->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);
            },
        ];
    }
}
