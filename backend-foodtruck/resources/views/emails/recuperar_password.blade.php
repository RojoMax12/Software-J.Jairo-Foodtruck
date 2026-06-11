<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña - DiCreme</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #4A5568;">¡Hola!</h2>
    <p>Has solicitado restablecer la contraseña para tu cuenta en el sistema <strong>DiCreme</strong>.</p>
    <p>Haz clic en el siguiente botón para continuar con el proceso. Este enlace expirará en 15 minutos:</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $url }}" style="background-color: #3182ce; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
            Restablecer Contraseña
        </a>
    </div>

    <p style="font-size: 12px; color: #718096;">Si el botón no funciona, puedes copiar y pegar este enlace en tu navegador:</p>
    <p style="font-size: 12px; color: #3182ce; word-break: break-all;">{{ $url }}</p>
    
    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
    <p style="font-size: 12px; color: #a0aec0;">Si tú no solicitaste este cambio, puedes ignorar este correo de forma segura.</p>
</body>
</html>