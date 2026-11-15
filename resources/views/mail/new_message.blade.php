{{-- Owner notification for a new inbox message. Every value is escaped; line breaks are kept with pre-wrap. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New message</title>
</head>
<body style="margin: 0; padding: 24px; background: #f4f5f7; font-family: Arial, Helvetica, sans-serif; color: #1b1b20;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px;">
        <tr>
            <td style="padding: 24px 28px; border-bottom: 3px solid #ff014f;">
                <strong style="font-size: 18px;">New {{ strtolower($inboxMessage->sourceLabel()) }} message</strong>
                <div style="margin-top: 4px; font-size: 13px; color: #6b6b75;">{{ $inboxMessage->created_at?->format('j M Y, H:i') }}</div>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 28px; font-size: 14px; line-height: 1.6;">
                <div><strong>From:</strong> {{ $inboxMessage->name }} &lt;{{ $inboxMessage->email }}&gt;</div>
                @if (filled($inboxMessage->phone))
                    <div><strong>Phone:</strong> {{ $inboxMessage->phone }}</div>
                @endif
                @if (filled($inboxMessage->subject))
                    <div><strong>Subject:</strong> {{ $inboxMessage->subject }}</div>
                @endif
                @if ($inboxMessage->project)
                    <div><strong>Project:</strong> {{ $inboxMessage->project->title }}</div>
                @endif
                <div style="margin-top: 16px; padding: 16px; background: #f4f5f7; border-radius: 8px; white-space: pre-wrap;">{{ $inboxMessage->body }}</div>
            </td>
        </tr>
        <tr>
            <td style="padding: 0 28px 24px; font-size: 13px; color: #6b6b75;">
                Reply to this email to answer {{ $inboxMessage->name }} directly, or
                <a href="{{ route('admin.messages.show', $inboxMessage) }}" style="color: #ff014f;">open it in the inbox</a>.
            </td>
        </tr>
    </table>
</body>
</html>
