<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $heading }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f3f0; color: #22231f; font-family: Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f3f3f0; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 560px; background-color: #ffffff; border-radius: 16px;">
                    <tr>
                        <td style="padding: 36px 32px;">
                            <p style="margin: 0 0 28px; color: #25553e; font-size: 15px; font-weight: 700;">Bågspår</p>
                            <h1 style="margin: 0 0 20px; font-size: 24px; line-height: 1.3;">{{ $heading }}</h1>
                            <p style="margin: 0 0 14px; font-size: 16px; line-height: 1.6;">{{ $greeting }}</p>
                            <p style="margin: 0 0 24px; font-size: 16px; line-height: 1.6;">{{ $body }}</p>
                            <p style="margin: 0 0 24px;">
                                <a href="{{ $url }}" style="display: inline-block; padding: 13px 20px; border-radius: 999px; background-color: #25553e; color: #ffffff; font-size: 15px; font-weight: 700; text-decoration: none;">{{ $actionText }}</a>
                            </p>
                            <p style="margin: 0 0 8px; color: #555750; font-size: 14px; line-height: 1.6;">{{ $fallbackText }}</p>
                            <p style="margin: 0 0 24px; font-size: 14px; line-height: 1.6; word-break: break-all;">
                                <a href="{{ $url }}" style="color: #25553e;">{{ $url }}</a>
                            </p>
                            <p style="margin: 0 0 24px; color: #555750; font-size: 14px; line-height: 1.6;">{{ $notice }}</p>
                            <p style="margin: 0; font-size: 14px; line-height: 1.6;">Med vänliga hälsningar,<br>Bågspår</p>
                        </td>
                    </tr>
                </table>
                <p style="margin: 16px 0 0; color: #686a64; font-size: 12px; line-height: 1.5;">© {{ now()->year }} Bågspår. Alla rättigheter förbehållna.</p>
            </td>
        </tr>
    </table>
</body>
</html>
