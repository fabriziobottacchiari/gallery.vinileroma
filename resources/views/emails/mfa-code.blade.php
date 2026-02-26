<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Codice di accesso</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f4f4f5; margin: 0; padding: 40px 20px; }
        .container { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .header { background: #1e293b; padding: 32px; text-align: center; }
        .header h1 { color: #fff; font-size: 20px; margin: 0; letter-spacing: .5px; }
        .body { padding: 40px 32px; text-align: center; }
        .body p { color: #374151; font-size: 15px; margin: 0 0 24px; }
        .code { display: inline-block; font-size: 42px; font-weight: 700; letter-spacing: 10px; color: #1e293b; background: #f1f5f9; border-radius: 8px; padding: 16px 32px; margin: 8px 0 24px; font-family: 'Courier New', monospace; }
        .expiry { font-size: 13px; color: #6b7280; }
        .footer { border-top: 1px solid #e5e7eb; padding: 20px 32px; text-align: center; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>
        <div class="body">
            <p>Usa il seguente codice per completare l'accesso al pannello di amministrazione.</p>
            <div class="code">{{ $code }}</div>
            <p class="expiry">Il codice è valido per <strong>15 minuti</strong>.</p>
            <p style="color:#9ca3af;font-size:13px;">Se non hai richiesto questo codice, ignora questa email.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }} — Tutte le email sono transazionali.
        </div>
    </div>
</body>
</html>
