<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        المصروفات
    </title>
    <style>
        .header {
            height: 10px;
            background-color: #79ac69;
            color: white;
        }

        table {
            width: 100%;
            justify-content: center;
            color: #444;
        }

        table thead {
            background-color: #333;
            color: #fff;
        }

        .head_table {
            background-color: #1a6296;
            color: #fff
        }

        tr {
            border: none;
            text-align: center;
        }

        td {
            border: none;
        }
    </style>
</head>

<body>
    <div>
        <table
            style="padding: 5px ; text-align: center; width: 100%; border: 1px solid black; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="width: 20% !important; border: 1px solid black; border-collapse: collapse;">
                        {{ __('pages.input_date') }}
                    </th>
                    <th style="width: 20% !important; border: 1px solid black; border-collapse: collapse;">
                        {{ __('pages.maintenance_invoice_date') }}
                    </th>
                    <th style="width: 20% !important; border: 1px solid black; border-collapse: collapse;">
                        القيمة
                    </th>
                    <th style="width: 20% !important; border: 1px solid black; border-collapse: collapse;">
                        {{__('pages.building_name') }}
                    </th>
                    <th style="width: 20% !important; border: 1px solid black; border-collapse: collapse;">
                        {{ __('pages.maintenances') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($maintenances as $maintenance)
                <tr>
                    <td
                        style="vertical-align: middle; width: 20% !important; border: 1px solid black; border-collapse: collapse;">
                        {{ date('d-m-Y', strtotime($maintenance->created_at)) }}
                    </td>
                    <td
                        style="vertical-align: middle; width: 20% !important; border: 1px solid black; border-collapse: collapse;">
                        {{ date('d-m-Y', strtotime($maintenance->invoice_date)) }}
                    </td>
                    <td
                        style="vertical-align: middle; width: 20% !important; border: 1px solid black; border-collapse: collapse;">
                        {{ $maintenance->cost }}
                    </td>
                    <td
                        style="vertical-align: middle; width: 20% !important; border: 1px solid black; border-collapse: collapse;">
                        @if($maintenance->building){{ $maintenance->building->name }}@endif
                    </td>
                    <td
                        style="vertical-align: middle; width: 20% !important; border: 1px solid black; border-collapse: collapse;">
                        {{ $maintenance->name }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>