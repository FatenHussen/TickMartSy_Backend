<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <title>قبول طلب مزود خدمة</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.8;
            color: #1f2937;
            background-color: #f3f4f6;
            padding: 16px;
        }

        .container {
            max-width: 620px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .header {
            background: linear-gradient(135deg, #0f766e, #0ea5e9);
            color: #ffffff;
            text-align: center;
            padding: 28px 20px;
        }

        .header h1 {
            font-size: 22px;
            font-weight: 700;
        }

        .content {
            padding: 28px 22px;
        }

        .content p {
            margin-bottom: 14px;
            font-size: 15px;
        }

        .note {
            margin: 18px 0;
            padding: 14px;
            border-radius: 8px;
            background: #ecfeff;
            border-right: 4px solid #14b8a6;
        }

        .note strong {
            display: block;
            margin-bottom: 6px;
            color: #0f766e;
        }

        .footer {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            text-align: center;
            padding: 18px;
            font-size: 12px;
            line-height: 1.7;
        }

        @media only screen and (max-width: 640px) {
            body {
                padding: 8px;
            }

            .header h1 {
                font-size: 20px;
            }

            .content {
                padding: 20px 14px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>تم قبول طلبك كمزود خدمة</h1>
    </div>

    <div class="content">
        <p>مرحباً <strong>{{ $vendorName }}</strong>،</p>

        <p>
            يسعدنا إبلاغك بأنه تمت الموافقة على طلب التسجيل الخاص بك كمزود خدمة، وتم إنشاء ملفك بشكل مبدئي على المنصة.
        </p>

        <div class="note">
            <strong>ماذا بعد؟</strong>
            لا يوجد حالياً دخول مباشر إلى لوحة تحكم لمزود الخدمة. سيتواصل معك فريق الإدارة قريباً للحصول على بقية التفاصيل، وبعدها يتم إدخال الخدمات وتجهيز الحساب من طرف الإدارة.
        </div>

        <p>مع أطيب التحيات،<br><strong>فريق {{ config('app.name') }}</strong></p>
    </div>

    <div class="footer">
        <p>هذه رسالة آلية، يرجى عدم الرد عليها.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. جميع الحقوق محفوظة.</p>
    </div>
</div>
</body>
</html>
