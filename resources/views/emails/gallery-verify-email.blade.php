<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifica email — Vinile Roma Gallery</title>
</head>
<body style="margin:0;padding:0;background-color:#09090b;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#09090b;padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">

                    {{-- Header --}}
                    <tr>
                        <td style="padding-bottom:32px;text-align:center;">
                            <p style="margin:0;font-size:22px;font-weight:700;color:#ffffff;letter-spacing:-0.5px;">
                                Vinile Roma Gallery
                            </p>
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td style="background-color:#18181b;border:1px solid #27272a;border-radius:16px;padding:40px;">

                            <p style="margin:0 0 8px;font-size:20px;font-weight:700;color:#ffffff;">
                                Ciao, {{ $user->first_name }}!
                            </p>
                            <p style="margin:0 0 28px;font-size:15px;color:#a1a1aa;line-height:1.6;">
                                Grazie per esserti registrato su <strong style="color:#e4e4e7;">Vinile Roma Gallery</strong>.<br>
                                Clicca sul pulsante qui sotto per verificare il tuo indirizzo email e accedere alle gallerie fotografiche.
                            </p>

                            <table cellpadding="0" cellspacing="0" style="margin:0 auto 28px;">
                                <tr>
                                    <td style="background-color:#4f46e5;border-radius:8px;">
                                        <a href="{{ $verifyUrl }}"
                                           style="display:inline-block;padding:14px 32px;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;">
                                            Verifica la tua email
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px;font-size:13px;color:#71717a;">
                                Il link è valido per <strong>60 minuti</strong>. Se non riesci a cliccare il pulsante, copia questo URL nel browser:
                            </p>
                            <p style="margin:0;font-size:12px;color:#6366f1;word-break:break-all;">
                                {{ $verifyUrl }}
                            </p>

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding-top:24px;text-align:center;">
                            <p style="margin:0;font-size:12px;color:#52525b;">
                                Se non hai creato un account su Vinile Roma Gallery, puoi ignorare questa email.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
