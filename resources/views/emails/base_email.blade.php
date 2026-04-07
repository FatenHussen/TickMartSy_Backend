@component('mail::message')
<div style="text-align: center;">
  {!! nl2br(e($messageText)) !!}
</div>

**Contact Us**
- 📧 Email: [tickmart@tickmart.com](mailto:tickmart@tickmart.com)
- 📞 Phone: +963 999 999 999

🌐 Follow us on:
- [Instagram](https://www.instagram.com/e)
- [Facebook](https://www.facebook.com/s)

Best regards,<br>
{{ config('app.name') }}

@endcomponent