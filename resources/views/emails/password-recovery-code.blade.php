<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <title>Código para recuperar tu contraseña</title>
    </head>
    <body style="margin:0;padding:28px;background:#eef4f8;color:#183247;font-family:Arial,Helvetica,sans-serif;">
        <div style="max-width:560px;margin:0 auto;padding:32px;border:1px solid #d5e2eb;border-radius:20px;background:#ffffff;box-shadow:0 16px 42px rgba(19,51,75,.12);">
            <p style="margin:0 0 8px;color:#087cab;font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">
                Gobierno Municipal de Coatepeque
            </p>
            <h1 style="margin:0 0 18px;font-size:26px;line-height:1.2;">
                Recuperación de contraseña
            </h1>
            <p style="margin:0 0 12px;line-height:1.6;">
                Hola, {{ $user->name }}.
            </p>
            <p style="margin:0 0 22px;line-height:1.6;">
                Escribe este código en la pantalla de recuperación:
            </p>
            <div style="padding:18px 20px;border-radius:14px;background:#102b43;color:#ffffff;font-size:32px;font-weight:800;letter-spacing:10px;text-align:center;">
                {{ $code }}
            </div>
            <p style="margin:22px 0 0;line-height:1.6;">
                El código vence en 10 minutos. Si no solicitaste este cambio,
                puedes ignorar el mensaje.
            </p>
        </div>
    </body>
</html>
