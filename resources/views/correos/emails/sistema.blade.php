<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }}</title>
</head>
<body style="margin:0;background:#f3f6f9;color:#212529;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f6f9;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 3px 12px rgba(30,41,59,.08);">
                    <tr>
                        <td style="padding:22px 34px;text-align:center;background:{{ $colorPrimario }};">
                            @if ($logoUrl)
                                <img src="{{ $logoUrl }}" alt="{{ $nombreMarca }}" style="display:block;max-width:180px;max-height:64px;width:auto;height:auto;margin:0 auto 10px;">
                            @endif
                            <div style="font-size:20px;font-weight:700;color:#ffffff;">{{ $nombreMarca }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 34px 32px;">
                            <h1 style="margin:0 0 18px;font-size:24px;line-height:1.3;text-align:center;color:#212529;">{{ $titulo }}</h1>
                            <div style="font-size:15px;line-height:1.7;color:#495057;white-space:pre-line;">{{ $mensaje }}</div>

                            @if ($textoBoton && $urlBoton)
                                <div style="padding:26px 0 16px;text-align:center;">
                                    <a href="{{ $urlBoton }}" style="display:inline-block;padding:12px 22px;border-radius:6px;background:{{ $colorPrimario }};color:#ffffff;text-decoration:none;font-weight:600;">{{ $textoBoton }}</a>
                                </div>
                                <p style="margin:16px 0 0;font-size:12px;line-height:1.5;color:#878a99;word-break:break-all;">
                                    Si el boton no funciona, copia este enlace en tu navegador:<br>
                                    <a href="{{ $urlBoton }}" style="color:{{ $colorPrimario }};">{{ $urlBoton }}</a>
                                </p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 34px;background:#f8f9fa;border-top:1px solid #e9ebec;text-align:center;font-size:12px;line-height:1.5;color:#878a99;">
                            Este es un correo automático. Por favor, no respondas a este mensaje.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
