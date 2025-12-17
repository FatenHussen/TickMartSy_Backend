<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>  </title>

    <style>
        body {
            direction: rtl !important;
            text-align: right !important;
            font-family: 'Tahoma', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f8fa;
        }

        .container {
            direction: rtl !important;
            text-align: right !important;
            width: 100%;
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 30px 25px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .header {
            direction: rtl !important;
            text-align: center !important;
            margin-bottom: 25px;
        }

        .header img {
            max-width: 130px;
            border-radius: 50%;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }

        .content {
            direction: rtl !important;
            text-align: right !important;
            font-size: 16px;
            color: #333333;
            line-height: 1.8;
        }

        .otp {
            display: inline-block;
            font-weight: bold;
            background-color: #f1f1f1;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 18px;
            color: #222;
            margin: 10px 0;
        }

        .footer {
            direction: rtl !important;
            text-align: center !important;
            font-size: 14px;
            color: #888888;
            margin-top: 25px;
            border-top: 1px solid #eeeeee;
            padding-top: 15px;
        }
    </style>
</head>

<body style="direction: rtl; text-align: right;">

    <div class="container" style="direction: rtl; text-align: right;">

        <div class="header" style="text-align: center;">
            <img src="https://yallabazaar.app/images/logo-yalla-bazar.png" alt="شعار يلا بازار">
        </div>

        <div class="content" style="direction: rtl; text-align: right;">
            <p>أهلاً بك 👋</p>

            <p>
                شكراً لانضمامك إلى 
                <br>
                رمز التحقق الخاص بك هو:
            </p>

            <p class="otp">{{ $password }}</p>

            <p>
                ⏳ هذا الرمز صالح لمدة <strong>ساعة واحدة</strong> فقط من وقت استلام الرسالة.  
                <br>
                إذا لم تكن أنت من طلب تسجيل الدخول، يرجى تجاهل هذه الرسالة.
            </p>

            <p>
                مع أطيب التحيات،  
                <br>
            </p>
        </div>

       

    </div>

</body>

</html>
