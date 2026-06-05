<!DOCTYPE html>
<html>
    <body style="font-family: sans-serif; padding: 20px;">
        <h2>📡 New Firmware Available: {{ $deviceName }}</h2>
        <p>A new firmware version has been detected on the Cudy download page.</p>
        <table>
            <tr><td><strong>Previous Version:</strong></td><td>{{ $currentVersion }}</td></tr>
            <tr><td><strong>New Version:</strong></td><td style="color:green;"><strong>{{ $newVersion }}</strong></td></tr>
        </table>
        <br>
        <a href="{{ $updateUrl }}" style="background:#2563eb;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;">
            View Download Page
        </a>
        <p style="color:#888;font-size:12px;margin-top:30px;">Sent by your Update Monitor</p>
    </body>
</html>