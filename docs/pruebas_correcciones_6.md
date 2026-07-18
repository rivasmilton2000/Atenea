# Pruebas de Correcciones 6

Fecha: 17 de julio de 2026. Entorno: XAMPP local, `http://localhost/Atenea`, base `db_atenea`.

## Resultados automatizados

- Lint PHP: 308 archivos propios, 0 errores.
- Etapa 3: 10 pruebas; además, concurrencia repetida tres veces consecutivas con resultado correcto.
- Etapa 4: 14 pruebas.
- Etapa 5: 15 pruebas.
- Etapa 6: 13 pruebas.
- Total de aserciones únicas de integración en etapas 3–6: 52.
- `git diff --check`: sin errores; solo avisos esperados LF/CRLF.

Comandos:

```powershell
C:\xampp\php\php.exe tests\integration\correcciones6_etapa3.php
C:\xampp\php\php.exe tests\integration\correcciones6_etapa4.php
C:\xampp\php\php.exe tests\integration\correcciones6_etapa5.php
C:\xampp\php\php.exe tests\integration\correcciones6_etapa6.php
```

## HTTP y frontend

- `GET /Atenea/index.php`: 200.
- Preview con token falso: 403.
- Editor sin sesión: 302 hacia autenticación.
- Login tradicional: página 200; no se ejecutó autenticación con una contraseña real.
- Inicio Google OAuth: 302 a Google con callback `http://localhost/Atenea/src/auth/google-callback.php`, `state`, nonce y PKCE. No se completó el consentimiento interactivo.
- Noticias y capacitaciones públicas: 200.
- HTML del index: título “Lo que ofrecemos”, enlace correcto `/noticias.php`, tres noticias y ausencia de `noticas`.
- Catálogo, detalle y carrito leen los datos editoriales de producto desde la publicación vigente; el inventario continúa siendo transaccional y en vivo.
- Existen capturas previas de etapa 2 para 1366, 768 y 375 px. La interfaz autenticada del editor no se capturó durante esta ejecución.

## Matriz funcional y seguridad

| Área | Evidencia | Resultado |
|---|---|---|
| Roles/permisos | propiedad docente, agenda y chats ajenos | Probado |
| CSRF | endpoints POST revisados y tokens usados | Auditoría de código |
| SQL injection | consultas nuevas preparadas; IDs validados | Auditoría de código |
| XSS | escapes de salida y eliminación de scripts en chat | Probado |
| Subidas | MIME/extensión/tamaño/nombre aleatorio | Probado parcialmente |
| Sesiones | cookies HttpOnly/SameSite; preview ligada a sesión | Probado/código |
| Stripe | precio del snapshot publicado, webhook idempotente, pago duplicado | Probado con objetos simulados; sin cobro real |
| Cupo 30 | último cupo y webhooks concurrentes | Probado |
| Máximo docente 2 | trigger/base y backend | Probado |
| Entregas/notas | propiedad, intentos, 0–10, historial | Probado |
| Progreso/certificado | visto no completa, 100 %, PDF/idempotencia | Probado |
| Certificado ajeno | descarga exige propietario/admin | Auditoría de código |
| Chat | deduplicación, no leído, XSS, bloqueo, acceso ajeno | Probado |
| SMTP | fallo real a puerto cerrado, error sanitizado | Probado fallo; éxito pendiente |
| IMAP | falta de configuración sin simulación | Probado fallo; éxito pendiente |
| Preview | token válido/falso, rol, sesión, publicación/restauración | Probado |
| Secretos | `.env` ignorado; búsqueda sin valores incrustados | Probado |
| Webhook falso | firma Stripe obligatoria en endpoint | Auditoría de código |

## Rendimiento

- Index obtiene una instantánea publicada en una consulta y procesa relaciones en memoria.
- El polling del editor consulta un único hash cada dos segundos y aplica debounce.
- Chat y paneles limitan resultados; listados administrativos heredados mantienen paginación o límites.
- IMAP solo se ejecuta manualmente o por cron.
- No se cargan videos/archivos privados desde MySQL.

## Incidencias y límites de la prueba

La primera ejecución conjunta de etapa 3 falló una vez en la aserción concurrente. La ejecución aislada inmediata y tres repeticiones consecutivas posteriores pasaron. No se reprodujo nuevamente, pero debe vigilarse el log de deadlocks y tiempos de respuesta en producción.

No se consideran probados hasta aportar credenciales/servicios externos: finalización interactiva de Google OAuth, cobro Stripe real y recepción de su webhook público, envío SMTP exitoso, sincronización IMAP exitosa y entrega real de correo. Tampoco se realizó pentest externo ni prueba de carga.
