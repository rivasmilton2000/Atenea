# Correcciones 6 — Etapa 3 de 6

Fecha de ejecución: 17 de julio de 2026.

## Respaldo

- Archivo local: `artifacts/db_atenea_pre_correcciones6_etapa3_20260717_095550.sql`.
- Tamaño: 129313 bytes.
- SHA-256: `D3A980C7E970AB04E0E6B62BF74A1981DC58B8DBF04C389966FA66D7481A828B`.
- Está excluido de Git porque contiene información real.

## Arquitectura reutilizada

Se mantuvo PHP, mysqli/PDO sobre MySQL y la arquitectura monolítica. `asignaturas` es la fuente real de capacitaciones; `docentes_asignaturas` conserva la relación de docentes autorizados y `estudiantes_docentes` se actualiza como capa de compatibilidad para los portales académico, docente y estudiante existentes.

La integración comercial de productos no se reutilizó como si una capacitación fuera inventario físico. Se reutilizaron la configuración y SDK de Stripe, el endpoint de webhook, `stripe_eventos`, sesiones, CSRF, auditoría, notificaciones y correo. Los pagos académicos se registran en una tabla propia porque `pagos` exige un `pedido_id` de productos.

## Migración

`src/database/migrations/018_capacitaciones_pagos_inscripciones.sql`:

- amplía `asignaturas` con slug, descripciones, imagen, tipo, nivel, precio, duración, fechas, publicación, cupo, requisitos, objetivos, modalidad, certificado, orden, activo y archivado;
- migra las tres capacitaciones existentes desde `elementos_seccion` sin borrar los elementos del index;
- crea `capacitacion_pagos` con sesión Stripe, Payment Intent, importe, moneda, estado y evento;
- crea `capacitacion_secciones` con docente, fechas, horario, estado y cupo máximo 30;
- crea `inscripciones_capacitacion` con pago único y estado `pendiente_asignacion`;
- crea `inscripcion_movimientos` para conservar origen, destino, docentes, motivo, administrador y fecha;
- añade claves foráneas, índices, restricciones `CHECK` y triggers que bloquean una tercera capacitación activa para un docente;
- usa InnoDB y `utf8mb4_unicode_ci`.

La migración fue ejecutada dos veces correctamente para validar su repetibilidad.

## Flujo de pago e inscripción

1. El formulario envía únicamente `capacitacion_id` y el token CSRF.
2. El servidor bloquea la capacitación y consulta el precio real en `asignaturas`.
3. Se rechaza otro pago pendiente o pagado del mismo usuario y capacitación.
4. Stripe Checkout se crea en servidor con una clave de idempotencia.
5. La página de éxito solo consulta y muestra el estado; nunca confirma el pago.
6. `webhook.php` verifica la firma de Stripe antes de delegar el evento académico.
7. El manejador valida sesión, referencia, importe, moneda y `payment_status=paid`.
8. Dentro de una transacción registra el pago, crea una sola inscripción y bloquea la capacitación para serializar cupos.
9. Selecciona una vez, con `random_int`, entre secciones/docentes elegibles. La selección queda persistida.
10. Si no hay sección, crea una con capacidad máxima 30. Si no hay docente, conserva el pago y crea `pendiente_asignacion`.
11. Se generan notificaciones idempotentes para usuario y administrador y se intenta el correo reutilizando el sistema existente.

Los eventos expirados, fallidos y reembolsados también actualizan el estado académico. `stripe_eventos.stripe_event_id` evita reprocesar el mismo evento.

## Administración

El módulo `src/dashboard/capacitaciones/` permite:

- crear, editar, activar/desactivar, duplicar y archivar;
- seleccionar docentes autorizados con validación backend y trigger;
- consultar docentes, secciones e inscritos;
- crear secciones, cambiar docente y abrir/cerrar;
- buscar y filtrar alumnos;
- asignar inscripciones pendientes o mover alumnos con verificación de cupo;
- registrar cada movimiento sin modificar entregas, progreso ni notas.

El archivado es lógico. Pagos, inscripciones, entregas, notas y progreso no se eliminan.

## Pruebas automatizadas

Ejecutado con:

`C:\xampp\php\php.exe tests\integration\correcciones6_etapa3.php`

Resultado: 10 pruebas aprobadas.

- límite backend de dos capacitaciones por docente;
- límite mediante trigger de base de datos;
- sección con 29 estudiantes llega exactamente a 30;
- webhook repetido no duplica la inscripción;
- segundo pago no supera un cupo completo;
- dos procesos de webhook simultáneos no ocupan dos veces el último cupo;
- ausencia de docentes conserva el pago y deja asignación pendiente;
- movimiento manual valida cupo y crea historial;
- capacitación archivada conserva pagos e inscripciones;
- el Checkout no lee ni acepta un precio enviado por el navegador.

La suite crea usuarios, docentes, capacitaciones, pagos y eventos temporales y los elimina al terminar. Se verificó `usuarios_temp=0`, `cursos_temp=0` y `eventos_temp=0`.

Pruebas HTTP adicionales:

- listado, formulario, secciones e inscripciones del administrador: HTTP 200 con sesión administrativa;
- alta administrativa: HTTP 302 y registro creado;
- duplicación: HTTP 302 y una copia en borrador;
- archivado: HTTP 302, estado `archivada`, inactiva y con eliminación lógica;
- listado público y detalle válido: HTTP 200;
- slug con texto SQL: HTTP 404.

## Archivos modificados o creados

- `.gitignore`
- `includes/admin_metricas.php`
- `includes/capacitaciones.php`
- `src/database/migrations/018_capacitaciones_pagos_inscripciones.sql`
- `src/pagos/webhook.php`
- `src/pagos/crear-checkout-capacitacion.php`
- `src/pagos/success-capacitacion.php`
- `src/website/courses.php`
- `src/website/capacitacion.php`
- `src/estudiantes/cursos.php`
- `src/dashboard/includes/cms.php`
- `src/dashboard/partials/_sidebar.php`
- `src/dashboard/capacitaciones/index.php`
- `src/dashboard/capacitaciones/editar.php`
- `src/dashboard/capacitaciones/accion.php`
- `src/dashboard/capacitaciones/secciones.php`
- `src/dashboard/capacitaciones/seccion-accion.php`
- `src/dashboard/capacitaciones/inscripciones.php`
- `src/dashboard/capacitaciones/inscripcion-accion.php`
- `tests/integration/correcciones6_etapa3.php`
- `tests/integration/webhook_capacitacion_worker.php`

## Configuración externa pendiente

La aplicación local usa `STRIPE_MODE=test` y el webhook real es:

`http://localhost/Atenea/src/pagos/webhook.php`

En Stripe Dashboard debe existir un endpoint HTTPS público con la misma ruta sobre el dominio definitivo de producción. `APP_URL_PRODUCTION` todavía está vacío, por lo que no se inventa una URL de producción.

El endpoint debe recibir como mínimo:

- `checkout.session.completed`;
- `checkout.session.async_payment_succeeded`;
- `checkout.session.async_payment_failed`;
- `checkout.session.expired`;
- `payment_intent.payment_failed`;
- `charge.refunded`.

El secreto de firma debe configurarse en `STRIPE_WEBHOOK_SECRET`; no se escribe en Git, HTML ni JavaScript. Para una prueba externa real se debe usar Stripe CLI o el Dashboard en modo test y confirmar que el evento retorna HTTP 200.
