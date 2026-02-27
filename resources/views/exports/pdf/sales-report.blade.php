<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>تقرير المبيعات</title>
    <style>
        * {
            font-family: 'DejaVu Sans', sans-serif;
        }
        body {
            direction: rtl;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            background: #667eea;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
            margin-top: 0;
        }
        .summary {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .summary-item {
            display: table-cell;
            padding: 10px;
            background: white;
            border-radius: 4px;
            width: 50%;
        }
        .summary-item .label {
            color: #6c757d;
            font-size: 11px;
            display: block;
            margin-bottom: 5px;
        }
        .summary-item .value {
            color: #212529;
            font-size: 18px;
            font-weight: bold;
            display: block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table thead {
            background: #667eea;
            color: white;
        }
        table th, table td {
            padding: 12px;
            text-align: right;
            border: 1px solid #dee2e6;
        }
        table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>تقرير المبيعات</h1>
        <p>تاريخ الإنشاء: {{ $generated_at }}</p>
    </div>
    <div class="summary">
        <div class="summary-row">
            <div class="summary-item">
                <div class="label">إجمالي الطلبات</div>
                <div class="value">{{ number_format($data['total_orders']) }}</div>
            </div>
            <div class="summary-item" style="padding-right: 10px;">
                <div class="label">إجمالي الإيرادات</div>
                <div class="value">{{ number_format($data['total_revenue'], 2) }}</div>
            </div>
        </div>
        <div class="summary-row">
            <div class="summary-item">
                <div class="label">رسوم التوصيل</div>
                <div class="value">{{ number_format($data['total_delivery_fees'], 2) }}</div>
            </div>
            <div class="summary-item" style="padding-right: 10px;">
                <div class="label">إجمالي الخصومات</div>
                <div class="value">{{ number_format($data['total_discounts'], 2) }}</div>
            </div>
        </div>
        <div class="summary-row">
            <div class="summary-item">
                <div class="label">متوسط قيمة الطلب</div>
                <div class="value">{{ number_format($data['average_order_value'], 2) }}</div>
            </div>
 </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>رقم الطلب</th>
                <th>العميل</th>
                <th>الإجمالي</th>
                <th>التوصيل</th>
                <th>التاريخ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['orders'] as $order)
            <tr>
                <td>{{ $order['order_code'] }}</td>
                <td>{{ $order['user'] }}</td>
                <td>{{ number_format($order['total'], 2) }}</td>
                <td>{{ number_format($order['delivery_price'], 2) }}</td>
                <td>{{ $order['delivered_at'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
