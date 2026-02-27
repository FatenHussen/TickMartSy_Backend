<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>تقرير حركة المنتجات</title>
    <style>
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { direction: rtl; padding: 20px; font-size: 12px; }
        .header { background: #667eea; color: white; padding: 20px; margin-bottom: 20px; text-align: center; }
        .header h1 { font-size: 24px; margin: 0; }
        .section { margin-bottom: 30px; }
        .section-title { background: #667eea; color: white; padding: 10px; font-size: 16px; font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table thead { background: #667eea; color: white; }
        table th, table td { padding: 10px; text-align: right; border: 1px solid #dee2e6; }
        table tbody tr:nth-child(even) { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="header">
        <h1>تقرير حركة المنتجات</h1>
        <p>تاريخ الإنشاء: {{ $generated_at }}</p>
    </div>

    <div class="section">
        <div class="section-title">المنتجات الأكثر مبيعاً</div>
        <table>
            <thead>
                <tr>
                    <th>رقم المنتج</th>
                    <th>اسم المنتج</th>
                    <th>الكمية المباعة</th>
                    <th>إجمالي الإيرادات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['top_selling'] as $item)
                <tr>
                    <td>{{ $item['product_id'] }}</td>
                    <td>{{ is_array($item['product_name']) ? ($item['product_name']['ar'] ?? $item['product_name']['en'] ?? 'N/A') : $item['product_name'] }}</td>
                    <td>{{ number_format($item['total_sold']) }}</td>
                    <td>{{ number_format($item['total_revenue'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">المنتجات الأقل مبيعاً</div>
        <table>
            <thead>
                <tr>
                    <th>رقم المنتج</th>
                    <th>اسم المنتج</th>
                    <th>الكمية المباعة</th>
                    <th>إجمالي الإيرادات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['least_selling'] as $item)
                <tr>
                    <td>{{ $item['product_id'] }}</td>
                    <td>{{ is_array($item['product_name']) ? ($item['product_name']['ar'] ?? $item['product_name']['en'] ?? 'N/A') : $item['product_name'] }}</td>
                    <td>{{ number_format($item['total_sold']) }}</td>
                    <td>{{ number_format($item['total_revenue'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">المنتجات غير النشطة</div>
        <table>
            <thead>
                <tr>
                    <th>رقم المنتج</th>
                    <th>اسم المنتج</th>
                    <th>SKU</th>
                    <th>الفئة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['inactive_products'] as $product)
                <tr>
                    <td>{{ $product['id'] }}</td>
                    <td>{{ is_array($product['name']) ? ($product['name']['ar'] ?? $product['name']['en'] ?? 'N/A') : $product['name'] }}</td>
                    <td>{{ $product['sku'] }}</td>
                    <td>{{ is_array($product['category']) ? ($product['category']['ar'] ?? $product['category']['en'] ?? 'N/A') : $product['category'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
