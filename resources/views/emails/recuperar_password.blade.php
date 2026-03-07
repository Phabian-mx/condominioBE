<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        .container {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background-color: #ffffff;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #f7fafc;
            padding-bottom: 20px;
        }
        .code-box {
            background-color: #edf2f7;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin: 25px 0;
            border: 1px dashed #4a5568;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 10px;
            color: #2d3748;
        }
        .footer {
            font-size: 12px;
            color: #a0aec0;
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="color: #1a202c;">🔐 Recuperación de Acceso</h2>
        </div>

        <p style="color: #4a5568; line-height: 1.6;">
            Hola, hemos recibido una solicitud para restablecer la contraseña de tu cuenta en <strong>Condominio</strong>.
        </p>

        <p style="color: #4a5568;">Utiliza el siguiente código para continuar:</p>

        <div class="code-box">
            <span class="code">{{ $codigo }}</span>
        </div>

        <p style="color: #e53e3e; font-size: 14px; font-weight: bold;">
            ⚠️ Este código expirará en 15 minutos.
        </p>

        <p style="color: #718096; font-size: 14px;">
            Si no solicitaste este cambio, puedes ignorar este mensaje de forma segura; tu contraseña actual no cambiará.
        </p>

        <div class="footer">
            <p>&copy; 2026 Condominio - Blackstyle Services</p>
        </div>
    </div>
</body>
</html>
