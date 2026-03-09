<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <title>بيانات حساب البائع</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.8;
            color: #333;
            background-color: #f4f4f4;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
        }
        .content {
            padding: 30px 20px;
        }
        .content p {
            margin: 15px 0;
            font-size: 15px;
        }
        .credentials-box {
            background: #f8f9fa;
            border-right: 4px solid #667eea;
            padding: 20px 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .credential-item {
            margin: 15px 0;
        }
        .credential-label {
            font-weight: bold;
            color: #667eea;
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .credential-value {
            color: #333;
            font-family: 'Courier New', monospace;
            background: #fff;
            padding: 10px 12px;
            border-radius: 3px;
            display: block;
            border: 1px solid #e0e0e0;
            word-break: break-all;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 14px 30px;
            background: #667eea;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
        }
        .button:hover {
            background: #5568d3;
        }
        .button-container {
            text-align: center;
            margin: 25px 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            line-height: 1.6;
        }
        .warning {
            background: #fff3cd;
            border-right: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning strong {
            color: #856404;
            display: block;
            margin-bottom: 10px;
            font-size: 15px;
        }
        .warning ul {
            margin: 10px 0;
            padding-right: 20px;
        }
        .warning li {
            margin: 8px 0;
            font-size: 14px;
        }

        /* Mobile Responsive */
        @media only screen and (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 4px;
            }
            .header {
                padding: 25px 15px;
            }
            .header h1 {
                font-size: 20px;
            }
            .content {
                padding: 20px 15px;
            }
            .content p {
                font-size: 14px;
            }
            .credentials-box {
                padding: 15px 12px;
                margin: 15px 0;
            }
            .credential-label {
                font-size: 13px;
            }
            .credential-value {
                padding: 8px 10px;
                font-size: 13px;
            }
            .button {
                padding: 12px 25px;
                font-size: 15px;
                width: 100%;
                display: block;
            }
            .warning {
                padding: 12px;
                margin: 15px 0;
            }
            .warning strong {
                font-size: 14px;
            }
            .warning li {
                font-size: 13px;
                margin: 6px 0;
            }
            .footer {
                padding: 15px;
                font-size: 11px;
            }
        }

        /* Very Small Screens */
        @media only screen and (max-width: 400px) {
            .container {
                margin: 5px;
            }
            .header h1 {
                font-size: 18px;
            }
            .content {
                padding: 15px 10px;
            }
            .credentials-box {
                padding: 12px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 مرحباً بك في منصتنا!</h1>
        </div>

        <div class="content">
            <p>عزيزي/عزيزتي <strong>{{ $vendorName }}</strong>،</p>

            <p>تهانينا! تمت الموافقة على طلب التسجيل الخاص بك. نحن سعداء بانضمامك إلينا!</p>

            <p>تم إنشاء حساب البائع الخاص بك لمتجر <strong>{{ $vendorName }}</strong>
                 وتم تفعيل اشتراكك في الباقة الأساسية لدينا لضمان البدء في الاستفادة من جميع الخدمات.
                . فيما يلي بيانات تسجيل الدخول الخاصة بك:</p>

            <div class="credentials-box">
                <div class="credential-item">
                    <span class="credential-label">البريد الإلكتروني:</span>
                    <span class="credential-value">{{ $email }}</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">كلمة المرور:</span>
                    <span class="credential-value">{{ $password }}</span>
                </div>
            </div>

            <div class="warning">
                <strong>⚠️ تنبيه أمني مهم:</strong>
                <ul>
                    <li>يرجى تغيير كلمة المرور فوراً بعد أول تسجيل دخول</li>
                    <li>احتفظ ببيانات الدخول الخاصة بك بشكل آمن ولا تشاركها مع أي شخص</li>
                    <li>استخدم كلمة مرور قوية وفريدة لحسابك</li>
                </ul>
            </div>

            <div class="button-container">
                <a href="{{ config('app.url') }}/vendor/login" class="button">
                    تسجيل الدخول إلى حسابك
                </a>
            </div>

            <p style="margin-top: 30px;">
                إذا كان لديك أي أسئلة أو تحتاج إلى مساعدة، لا تتردد في التواصل مع فريق الدعم الخاص بنا.
            </p>

            <p>
                مع أطيب التحيات،<br>
                <strong>فريق الإدارة</strong>
            </p>
        </div>

        <div class="footer">
            <p>هذا بريد إلكتروني تلقائي. يرجى عدم الرد على هذه الرسالة.</p>
            <p style="margin-top: 8px;">&copy; {{ date('Y') }} {{ config('app.name') }}. جميع الحقوق محفوظة.</p>
        </div>
    </div>
</body>
</html>
