<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Segnalazione foto</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f4f4f5; margin: 0; padding: 24px; color: #18181b; }
        .card { background: #fff; border-radius: 12px; max-width: 540px; margin: 0 auto; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #ef4444; padding: 24px; color: #fff; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 700; }
        .header p { margin: 4px 0 0; font-size: 14px; opacity: .85; }
        .body { padding: 24px; }
        .thumb { width: 100%; border-radius: 8px; margin-bottom: 20px; display: block; }
        .field { margin-bottom: 16px; }
        .label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #71717a; margin-bottom: 4px; }
        .value { font-size: 15px; color: #18181b; }
        .comment-box { background: #f4f4f5; border-radius: 8px; padding: 12px 14px; font-size: 14px; color: #3f3f46; line-height: 1.5; }
        .btn { display: inline-block; background: #4f46e5; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-top: 20px; }
        .footer { padding: 16px 24px; border-top: 1px solid #f4f4f5; font-size: 12px; color: #a1a1aa; }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <h1>⚠️ Segnalazione foto ricevuta</h1>
        <p>Un utente ha segnalato una foto nell'evento «{{ $report->event->title }}»</p>
    </div>

    <div class="body">

        @if($thumbUrl)
            <img src="{{ $thumbUrl }}" alt="Foto segnalata" class="thumb">
        @endif

        <div class="field">
            <div class="label">Evento</div>
            <div class="value">{{ $report->event->title }} — {{ $report->event->event_date->format('d/m/Y') }}</div>
        </div>

        <div class="field">
            <div class="label">Motivo</div>
            <div class="value">
                @php
                    $reasons = [
                        'inappropriate' => 'Contenuto inappropriato',
                        'privacy'       => 'Violazione della privacy',
                        'copyright'     => 'Copyright / uso non autorizzato',
                        'other'         => 'Altro',
                    ];
                @endphp
                {{ $reasons[$report->reason] ?? $report->reason }}
            </div>
        </div>

        @if($report->comment)
            <div class="field">
                <div class="label">Commento dell'utente</div>
                <div class="comment-box">{{ $report->comment }}</div>
            </div>
        @endif

        <div class="field">
            <div class="label">IP segnalatore</div>
            <div class="value" style="font-family: monospace; font-size: 13px;">{{ $report->reporter_ip }}</div>
        </div>

        <a href="{{ $adminUrl }}" class="btn">Vai alla gestione foto →</a>
    </div>

    <div class="footer">
        Segnalazione ricevuta il {{ $report->created_at->format('d/m/Y \a\l\l\e H:i') }}
    </div>
</div>
</body>
</html>
