<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; direction: rtl; padding: 20px; font-size: 12px; }
        .header { background: #667eea; color: white; padding: 20px; margin-bottom: 20px; }
        .header h1 { font-size: 24px; margin-bottom: 10px; }
        .summary { background: #f8f9fa; padding: 15px; margin-bottom: 20px; }
        .summary-item { margin-bottom: 10px; }
        .summary-item .label { color: #6c757d; font-size: 11px; }
        .summary-item .value { color: #212529; font-size: 18px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table thead { background: #667eea; color: white; }
        table th, table td { padding: 12px; text-align: right; border: 1px solid #dee2e6; }
        table tbody tr:nth-child(even) { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sales Report</h1>
        <p>Generated: {{ $generated_at }}</p>
    </div>
    <div class="summary">
        <div class="summary-item">
            <div class="label">Total Orders</div>
            <div class="value">{{ number_format($data['total_orders']) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Revenue</div>
            <div class="value">{{ number_format($data['total_revenue'], 2) }}</div>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Order Code</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Delivery</th>
                <th>Date</th>
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
