# Configuración del formulario de contacto

1. Copie `.env.example` como `.env` en la raíz del proyecto.
2. En `GMAIL_SMTP_USER` coloque la cuenta de Gmail que enviará los mensajes.
3. En `GMAIL_SMTP_APP_PASSWORD` coloque una contraseña de aplicación de Google, nunca la contraseña normal de Gmail.
4. En `CONTACT_RECIPIENT` coloque el correo que recibirá los mensajes.
5. Cree un reCAPTCHA v2 de tipo casilla para el dominio y copie sus claves en `RECAPTCHA_SITE_KEY` y `RECAPTCHA_SECRET_KEY`.
6. Desde `includes/mail`, ejecute `composer install` si la carpeta privada `vendor` no existe.

Para generar la contraseña de aplicación: active la verificación en dos pasos de la cuenta de Google, abra **Cuenta de Google > Seguridad > Contraseñas de aplicaciones**, cree una para Atenea y copie los 16 caracteres generados.

Los archivos `.env`, `includes/config/mail.php`, `includes/mail/vendor/` y `logs/*.log` están ignorados por Git. No deben subirse al repositorio. Como alternativa a `.env`, puede copiar `includes/config/mail.example.php` como `includes/config/mail.php` y completar sus valores.
