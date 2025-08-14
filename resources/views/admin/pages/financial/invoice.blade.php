<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة</title>
        <style>
            .header {
                height: 10px;
                background-color: #79ac69;
                color: white;
            }
            
            table{
                width: 100%;
                justify-content: center;
                color:#444;
            }
            
            table thead{
                background-color:#333;
                color:#fff;
            }
            
            .head_table {
                    background-color:#1a6296;
                    color:#fff
                }
                
            tr {
                border: none;
                text-align: center;
            }

            td{
                border: none;
            }

        </style>
</head>
    <body>
        <table class="table table-borderless">
            <tr>
                <td style="width: 50%; text-align: left;">
                    <p>{{ $financial_transaction->paidOn }} <strong>التاريخ:</strong> </p>
                </td>
                <td style="width: 50%; text-align: right;">
                    <h1>إيصال إيجار</h1>
                </td>
            </tr>
        </table>
        <hr>
        <div class="invoice-details" style="text-align: right;">
            @if($financial_transaction->tenancy->deleted_at)
                <p><strong>عقار محذوف</strong></p>
            @endif
            <p>{{ $financial_transaction->id }} <strong>رقم العملية:</strong></p>
            <p>{{ $financial_transaction->orderReferenceNumber }} <strong>مرجع الطلب:</strong></p>
            @if ($financial_transaction->resultCode == 'CAPTURED')
                <p>{{ $financial_transaction->payment->notes ? $financial_transaction->payment->notes : 'لا توجد ملاحظات'}} <strong>ملاحظات الدفع:</strong></p>
            @endif
            <p>{{ $financial_transaction->resultCode == 'CAPTURED' ? 'ناجح' : 'غير ناجح' }} <strong>حالة الدفع:</strong> </p>
            <p>{{ $financial_transaction->tenant->name }} <strong>المستأجر:</strong> </p>
        </div>
        <table style="padding: 5px ; text-align: center; width: 100%; border: 1px solid black; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="width: 25% !important; border: 1px solid black; border-collapse: collapse;">المبلغ</th>
                    <th style="width: 25% !important; border: 1px solid black; border-collapse: collapse;">عن شهر</th>
                    <th style="width: 25% !important; border: 1px solid black; border-collapse: collapse;">الوحدة</th>
                    <th style="width: 25% !important; border: 1px solid black; border-collapse: collapse;">اسم المبنى</th>


                </tr>
            </thead>
            <tbody>
                @if ($financial_transaction->resultCode == 'CAPTURED')
                    @foreach ($financial_transaction->payment ? explode(",", $financial_transaction->payment->pay_monthes) : [] as $month)
                        <tr class="compound_row mb-3 record">
                            <td style="vertical-align: middle; width: 25% !important; border: 1px solid black; border-collapse: collapse;">{{ $financial_transaction->total_amount ? ($financial_transaction->total_amount / $financial_transaction->quantity) : 'عقار محذوف' }}</td>
                            <td style="vertical-align: middle; width: 25% !important; border: 1px solid black; border-collapse: collapse;">{{ $month ?? ' ' }}</td>
                            <td style="vertical-align: middle; width: 25% !important; border: 1px solid black; border-collapse: collapse;">{{ $financial_transaction->tenancy->apartment->name ?? 'عقار محذوف' }}</td>
                            <td style="vertical-align: middle; width: 25% !important; border: 1px solid black; border-collapse: collapse;">{{ $financial_transaction->tenancy->building->name ?? 'عقار محذوف' }}</td>
                        </tr>
                    @endforeach
                @endif

            </tbody>
        </table>
        <div class="total-amount" style="text-align: right;">
            <p><strong>الإجمالي فقط</strong> {{ $financial_transaction->total_amount}} د.ك لاغير </p>
        </div>
    </body>
</html>