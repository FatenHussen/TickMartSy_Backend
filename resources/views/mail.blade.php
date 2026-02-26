<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <title>رمز التحقق</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f8fa;
            direction: rtl;
            text-align: right;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f7f8fa;
            padding: 20px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            text-align: center;
        }
        .header-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin-bottom: 15px;
        }
        .header h1 {
            color: #ffffff;
            font-size: 24px;
            margin: 0;
        }
        .content {
            padding: 40px 30px;
        }
        .content p {
            color: #333333;
            font-size: 16px;
            line-height: 1.8;
            margin: 15px 0;
        }
        .otp-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            margin-bottom: 10px;
            display: block;
        }
        .otp {
            color: #ffffff;
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            display: block;
        }
        .info-box {
            background: #fff3cd;
            border-right: 4px solid #ffc107;
            padding: 15px 20px;
            border-radius: 8px;
            margin: 25px 0;
        }
        .info-box p {
            color: #856404;
            font-size: 14px;
            margin: 5px 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 25px 20px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        .footer p {
            color: #888888;
            font-size: 13px;
            line-height: 1.6;
            margin: 5px 0;
        }

        /* Mobile Responsive */
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 10px;
            }
            .container {
                border-radius: 8px;
            }
            .header {
                padding: 30px 15px;
            }
            .header-icon {
                width: 60px;
                height: 60px;
                font-size: 30px;
            }
            .header h1 {
                font-size: 20px;
            }
            .content {
                padding: 25px 20px;
            }
            .content p {
                font-size: 15px;
            }
            .otp-box {
                padding: 20px 15px;
                margin: 20px 0;
            }
            .otp {
                font-size: 28px;
                letter-spacing: 5px;
            }
            .info-box {
                padding: 12px 15px;
                margin: 20px 0;
            }
            .info-box p {
                font-size: 13px;
            }
            .footer {
                padding: 20px 15px;
            }
            .footer p {
                font-size: 12px;
            }
        }

        @media only screen and (max-width: 400px) {
            .header {
                padding: 25px 10px;
            }
            .content {
                padding: 20px 15px;
            }
            .otp {
                font-size: 24px;
                letter-spacing: 3px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="container">
            <div class="header">
                <div class="header-icon">🔐</div>
                <h1>رمز التحقق</h1>
            </div>

            <div class="content">
                <p><strong>أهلاً بك 👋</strong></p>

                <p>شكراً لاستخدامك منصتنا. لإتمام عملية التحقق، يرجى استخدام الرمز التالي:</p>

                <div class="otp-box">
                    <span class="otp-label">رمز التحقق الخاص بك</span>
                    <span class="otp">{{ $otp }}</span>
                </div>

                <div class="info-box">
                    <p><strong>⏳ تنبيه مهم:</strong></p>
                    <p>• هذا الرمز صالح لمدة <strong>ساعة واحدة</strong> فقط من وقت استلام الرسالة</p>
                    <p>• إذا لم تكن أنت من طلب هذا الرمز، يرجى تجاهل هذه الرسالة</p>
                    <p>• لا تشارك هذا الرمز مع أي شخص</p>
                </div>

                <p>مع أطيب التحيات،<br><strong>فريق الدعم</strong></p>
            </div>

            <div class="footer">
                <p>هذا بريد إلكتروني تلقائي. يرجى عدم الرد على هذه الرسالة.</p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </div>
</body>
</html>
