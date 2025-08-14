<?php


//
namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
//this class ExportMonthlyTenantsIncludingNotPaid
class ExportMonthlyTenants implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    private $building_id;   
    private $month;         // "2025-03" مثلاً
    private $monthText;     // "Mar-2025"
    private $year;          // "2025"
    private $month3;        // "Mar"
    private $results;

    public function __construct($request)
    {
        // مثلاً: $request->month = "2025-03"
        $this->building_id = $request->building_id;   // قد يكون null
        $this->month       = $request->month;
        
        // نفكّكها: year = "2025", month_num = "03"
        $parts = explode('-', $this->month); // ["2025", "03"]
        $this->year   = $parts[0];          // 2025
        // تحويل الرقم إلى اسم الشهر الكامل (مثلاً 03 => "March") ثم نأخذ أوّل 3 حروف => "Mar"
        $fullMonthName = date('F', mktime(0, 0, 0, $parts[1], 10)); 
        $this->month3  = substr($fullMonthName, 0, 3); // "Mar"

        // للتحسين: نضبط monthText = "Mar-2025"
        $this->monthText = $this->month3.'-'.$this->year;

        // نبني الـ Collection:
        $this->results = $this->prepareCollection();
    }

    private function prepareCollection()
    {
        // 1) بناء الاستعلام لجلب كل الشقق + المستأجرين + المباني
        //    لاحظ سنستخدم LEFT JOIN مع payments
        $query = DB::table('apartments')
            ->join('tenants', 'tenants.apartment_id', '=', 'apartments.id')
            ->join('buildings', 'buildings.id', '=', 'apartments.building_id')
            ->leftJoin('payments', 'payments.apartment_id', '=', 'apartments.id')
            ->leftJoin('users', 'users.id', '=', 'tenants.tenant_id')
            ->select([
                'buildings.name as building_name',
                'apartments.name as apartment_name',
                'users.name as tenant_name',
                'users.email as tenant_email',
                'users.phone as tenant_phone',
                'tenants.start_date',
                'tenants.end_date',
                'tenants.price as tenant_price',
                // ربما نحتاج pay_monthes, total_amount 
                // لأجل الاحتساب هل دفع هذا الشهر أم لا
                'payments.pay_monthes',
                'payments.total_amount as payment_total',
            ])
            ->whereRaw('tenants.id IN (select max(id) from tenants group by apartment_id)')
            ->whereNull('apartments.deleted_at')
            ->whereNull('tenants.deleted_at')
            ->whereNull('users.deleted_at')
            ->whereNull('buildings.deleted_at')
            // لا نضع شرط على pay_monthes هنا حتى لا نخفي من لم يدفع
            ->orderBy('buildings.name')
            ->orderBy('apartments.name');

        // 2) إذا كان عندنا building_id نطبّق الشرط
        if ($this->building_id) {
            $query->where('apartments.building_id', $this->building_id);
        }

        // نفّذ الاستعلام
        $rows = $query->get();

        // 3) نريد دمج الصفوف المتعلقة بنفس (building + apartment + tenant)
        //    مع جمع pay_monthes (حتى لو دفعات متعددة).
        $grouped = [];

        foreach ($rows as $row) {
            // المفتاح لتمييز كل شقة/مستأجر في عقار
            $key = $row->building_name.'|'.$row->apartment_name.'|'.$row->tenant_name;

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'building_name' => $row->building_name,
                    'apartment_name'=> $row->apartment_name,
                    'tenant_name'   => $row->tenant_name,
                    'tenant_email'  => $row->tenant_email,
                    'tenant_phone'  => $row->tenant_phone,
                    'start_date'    => $row->start_date,
                    'end_date'      => $row->end_date,
                    'tenant_price'  => $row->tenant_price,
                   'all_pay_monthes' => [],   // سنجمع هنا كل الشهور المدفوعة
                ];
            }

            // لو عنده صف في payments، قد يكون pay_monthes = "Jan-2025,Feb-2025,..."
            if (! empty($row->pay_monthes)) {
                // نفصل الشهور ونضيفها
                $months = explode(',', $row->pay_monthes);
                $grouped[$key]['all_pay_monthes'] = array_merge(
                    $grouped[$key]['all_pay_monthes'],
                    $months
                );
            }
        }

        // 4) تحويلها لشكل نهائي
        $final = [];
        foreach ($grouped as $item) {
            // نزيل المكررات
            $uniqueMonths = array_unique($item['all_pay_monthes']);
            // هل تتضمن الشهر المطلوب "Mar-2025"؟
            // يكفي نفحص وجود $this->monthText ضمن المصفوفة
            $paidThisMonth = in_array($this->monthText, $uniqueMonths) ? 'نعم' : 'لا';

            // حولها لنص مفصول بفواصل
            $monthsCell = implode(',', $uniqueMonths);

            $final[] = [
                'building_name'  => $item['building_name'],
                'apartment_name' => $item['apartment_name'],
                'tenant_name'    => $item['tenant_name'],
                'tenant_email'   => $item['tenant_email'],
                'tenant_phone'   => $item['tenant_phone'],
                'start_date'     => $item['start_date'] ? date('Y-m-d', strtotime($item['start_date'])) : '',
                'end_date'       => $item['end_date']   ? date('Y-m-d', strtotime($item['end_date']))   : '',
                'tenant_price'   => $item['tenant_price'],
                'paid_this_month'=> $paidThisMonth,
            //    'all_paid_months'=> $monthsCell,    // جميع الشهور المدفوعة
            ];
        }

        return collect($final);
    }

    public function collection()
    {
        return $this->results;
    }

    public function headings(): array
    {
        return [
            'اسم العقار',        // building_name
            'اسم الوحدة',        // apartment_name
            'اسم المستأجر',      // tenant_name
            'البريد الإلكتروني', // tenant_email
            'رقم الهاتف',         // tenant_phone
            'تاريخ بدء العقد',    // start_date
            'تاريخ انتهاء العقد', // end_date
            'السعر الشهري',      // tenant_price
            'هل دفع هذا الشهر؟',   // paid_this_month
        //    'الشهور المدفوعة',    // all_paid_months
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // تنسيق الرؤوس مثلاً
                $event->sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => ['bold' => true]
                ]);
                // اسم التبويب
                $event->sheet->setTitle("تقرير الشهر {$this->monthText}");
            },
        ];
    }
}
