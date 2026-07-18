# Configuración de correo SMTP e IMAP

No escriba credenciales reales en archivos versionados. Configure los valores en `.env` del servidor o como variables de entorno del proceso PHP.

## SMTP (correo saliente)

```dotenv
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
SMTP_USERNAME=cuenta-institucional@dominio.example
SMTP_PASSWORD=contraseña-de-aplicacion
SMTP_FROM_EMAIL=cuenta-institucional@dominio.example
SMTP_FROM_NAME=Atenea
```

- Puerto habitual con STARTTLS: `587` y `tls`.
- Alternativa SMTPS: `465` y `ssl`.
- `SMTP_PASSWORD` debe ser una contraseña de aplicación, no la contraseña normal de la cuenta.
- Atenea usa siempre `SMTP_FROM_EMAIL` como `From`.
- El correo del administrador, docente o estudiante que redactó se añade como `Reply-To`.
- PHPMailer está configurado con autenticación, UTF-8, timeout y errores sanitizados.

Prueba: abra **Administración → Comunicaciones → Estado del servicio**, confirme que SMTP aparezca “Configurado” y envíe un correo a una cuenta controlada desde **Redactar**. Compruebe `From`, `Reply-To`, bandeja Enviados y el registro de error si el servidor rechaza la operación.

## IMAP (correo entrante)

```dotenv
IMAP_HOST=imap.gmail.com
IMAP_PORT=993
IMAP_ENCRYPTION=ssl
IMAP_USERNAME=cuenta-institucional@dominio.example
IMAP_PASSWORD=contraseña-de-aplicacion
IMAP_FOLDER=INBOX
```

La extensión PHP IMAP debe estar habilitada. En XAMPP, revise `C:\xampp\php\php.ini`, habilite `extension=imap` y reinicie Apache. Compruebe con:

```powershell
C:\xampp\php\php.exe -m | Select-String imap
```

Después use **Sincronizar ahora** en el estado del servicio. La sincronización guarda la carpeta y el último UID, usa restricciones únicas por carpeta/UID y `Message-ID`, y no duplica mensajes ya importados.

Si falta cualquier valor o la extensión, la bandeja muestra “IMAP pendiente de configuración”; no se crean correos simulados. Los errores de conexión aparecen sanitizados, sin contraseña ni tokens.

## Seguridad recomendada

- Use una cuenta institucional dedicada y autenticación multifactor.
- Restrinja lectura de `.env` al usuario del proceso PHP.
- Use TLS/SSL y certificados válidos; no habilite conexiones IMAP sin cifrado en producción.
- Rote la contraseña de aplicación cuando cambie personal autorizado.
- Configure `COMMUNICATION_STORAGE_PATH` fuera de `C:\xampp\htdocs` y limite permisos al proceso PHP.
- Alinee `upload_max_filesize` y `post_max_size` con el máximo de adjuntos de 10 MB.
- Revise periódicamente fallos, pendientes, bitácora y mensajes reportados sin divulgar contenido fuera de la política institucional.

## Diagnóstico

1. Verifique host, puerto y cifrado carácter por carácter.
2. Confirme que el proveedor permite SMTP/IMAP y contraseñas de aplicación.
3. Compruebe firewall y resolución DNS desde el servidor.
4. Revise el panel de estado y `errores_sistema`; nunca copie secretos en observaciones.
5. En producción, pruebe primero con un destinatario institucional controlado.
