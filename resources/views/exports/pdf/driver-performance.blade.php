<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>تقرير أداء السائق</title>
    <style>
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { direction: rtl; padding: 20px; font-size: 12px; }
        .header { background: #667eea; color: white; padding: 20px; margin-bottom: 20px; text-align: center; }
        .header h1 { font-size: 24px; margin: 0; }
        .info-box { background: #f8f9fa; padding: 15px; margin-bottom: 20px; border: 1px solid #dee2e6; }
        .info-row { margin-bottom: 15px; padding: 10px; background: white; border-radius: 4px; }
        .info-label { color: #6c757d; font-size: 11px; display: block; margin-bottom: 5px; }
        .info-value { color: #212529; font-size: 16px; font-weight: bold; display: block; }
    </style>
</head>
<body>
    <div class="header">
        <h1>تقرير أداء السائق</h1>
        <p>تاريخ الإنشاء: {{ $generated_at }}</p>
    </div>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">اسم السائق</span>
            <span class="info-value">{{ is_array($data['driver_name']) ? ($data['driver_name']['ar'] ?? $data['driver_name']['en'] ?? 'N/A') : $data['driver_name'] }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">إجمالي الطلبات</span>
            <span class="info-value">{{ number_format($data['total_orders']) }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">إجمالي الأرباح</span>
            <span class="info-value">{{ \App\Helpers\CurrencyHelper::formatAmount($data['total_earnings']) }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">متوسط وقت التوصيل (دقيقة)</span>
            <span class="info-value">{{ number_format($data['average_delivery_time_minutes'], 2) }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">متوسط التقييم</span>
            <span class="info-value">{{ number_format($data['average_rating'], 1) }} / 5</span>
        </div>

        <div class="info-row">
            <span class="info-label">إجمالي التقييمات</span>
            <span class="info-value">{{ number_format($data['total_ratings']) }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">إجمالي الشكاوى</span>
            <span class="info-value">{{ number_format($data['total_complaints']) }}</span>
        </div>
    </div>
</body>
</html>
