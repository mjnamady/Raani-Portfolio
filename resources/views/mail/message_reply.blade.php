{{-- The owner's reply as the visitor receives it. Every value is escaped; line breaks are kept with pre-wrap. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $reply->subject }}</title>
</head>
<body style="margin: 0; padding: 24px; background: #f4f5f7; font-family: Arial, Helvetica, sans-serif; color: #1b1b20;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px;">
        <tr>
            <td style="padding: 24px 28px 8px; font-size: 15px; line-height: 1.7;">
                <div style="white-space: pre-wrap;">{{ $reply->body }}</div>
                <div style="margin-top: 20px; color: #6b6b75;">{{ $siteName }}</div>
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 28px 24px;">
                <div style="padding-left: 14px; font-size: 13px; line-height: 1.6; color: #6b6b75; border-left: 3px solid #e0e0e6;">
                    <div style="margin-bottom: 6px;">On {{ $original->created_at?->format('j M Y') }}, {{ $original->name }} wrote:</div>
                    <div style="white-space: pre-wrap;">{{ $original->body }}</div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
