# Correcciones 6 — Etapa 4

## Resultado

Se implementó el flujo académico individual sobre las capacitaciones, secciones e inscripciones reales de las etapas anteriores. No se inició la etapa 5.

La plantilla utilizada para certificados es exactamente `img/certificado/certificado sin nombre.png` (1449 × 1086 px, orientación horizontal 4:3). Se incrusta sin recortar ni deformar y se superponen nombre, capacitación, fecha, duración, número único y QR verificable. El PDF y los archivos académicos se guardan fuera del directorio público.

## Respaldo y migración

- Respaldo previo: `artifacts/db_atenea_pre_correcciones6_etapa4_20260717_102931.sql`.
- SHA-256: `50AA07E3B4D3FD96E87ACEF70AC26C510A70DF14E6D07A3B52ED6323215B1798`.
- Migración: `src/database/migrations/019_flujo_academico_certificados.sql`.
- Codificación/motor: `utf8mb4_unicode_ci`, InnoDB.
- La migración se aplicó dos veces consecutivas sin errores para comprobar idempotencia.

La migración amplía `contenidos` con sección, módulo, tipo, orden, publicación, fecha límite, obligatoriedad y peso. Crea `entregas_contenido`, `entrega_evidencias`, `entrega_revisiones`, `progreso_contenido` y `certificados_capacitacion`; también agrega la confirmación de finalización a `inscripciones_capacitacion`. Incluye claves foráneas, índices, unicidad por intento/certificado y restricciones `CHECK` de notas entre `0.00` y `10.00`.

## Flujo implementado

- El docente administra únicamente contenidos de secciones asignadas: video privado, texto, documento, enlace, actividad, evaluación y recurso descargable.
- Los videos externos se limitan a HTTPS de YouTube o Vimeo. Los videos cargados nunca se guardan como BLOB.
- Se valida MIME real, extensión, tamaño, nombre y ruta; los nombres almacenados son aleatorios.
- El estudiante solo ve y entrega en su inscripción/sección. Abrir una página registra `visto_at`, pero no completa el contenido.
- Las actividades/evaluaciones solo aportan progreso cuando la última entrega está aprobada. Los contenidos informativos requieren la acción explícita “Marcar como estudiado”.
- Cada corrección crea un intento nuevo. La revisión conserva estado y nota anteriores, retroalimentación, docente y fecha.
- Aprobar, rechazar o solicitar corrección exige retroalimentación y genera una notificación.
- El docente ve entregas y progreso de sus secciones. El administrador supervisa contenidos, entregas, revisiones, notas, progreso y certificados de toda la plataforma.
- Un certificado solo se genera con 100 %, al menos un requisito obligatorio y ninguna obligación pendiente, rechazada o en corrección. La emisión es idempotente.
- La descarga privada comprueba rol y propiedad. El QR usa un token aleatorio de 256 bits y una página pública que no expone IDs internos.

## Límites de archivos

- Video: `ACADEMIC_VIDEO_MAX_MB`, 250 MB por defecto.
- Documento de contenido: 20 MB.
- Evidencia: 10 MB por archivo, máximo 5 archivos por intento.
- Ruta privada: `ACADEMIC_STORAGE_PATH`; el ejemplo usa `C:/xampp/atenea-private/academico`.

En producción, `ACADEMIC_STORAGE_PATH` debe existir fuera del document root y ser escribible únicamente por el proceso PHP. También deben alinearse `upload_max_filesize` y `post_max_size` de PHP con el límite de video elegido.

## Archivos de esta etapa

- `.env.example`, `.gitignore`.
- `includes/academico_flujo.php`, `includes/portal_estudiante_layout.php`.
- `src/database/migrations/019_flujo_academico_certificados.sql`.
- `src/academico/archivo.php`.
- `src/docente/contenidos.php`, `contenido_guardar.php`, `contenido_editar.php`, `contenido_actualizar.php`, `entregas.php`, `entrega-revisar.php`, `progreso.php`, `finalizacion.php` y `partials/_sidebar.php`.
- `src/estudiantes/curso.php`, `contenido.php`, `contenido-accion.php`, `certificados.php`, `certificado-generar.php`.
- `src/dashboard/academico/seguimiento.php`, `src/dashboard/academico/finalizacion.php` y `src/dashboard/partials/_sidebar.php`.
- `src/website/verificar-certificado.php`.
- `tests/integration/correcciones6_etapa4.php`.

## Pruebas realizadas

Comando reproducible:

```powershell
C:\xampp\php\php.exe tests\integration\correcciones6_etapa4.php
```

Resultado: `OK 14 pruebas`.

Se comprobó:

1. Propiedad de sección para docente y denegación a docente ajeno.
2. Visualización sin progreso automático.
3. Finalización explícita y progreso ponderado.
4. Actividad rechazada bloqueando certificado.
5. Rechazo de notas `-0.01` y `10.01` por MySQL.
6. Segundo intento aprobado, promedio decimal y progreso 100 %.
7. Emisión idempotente, un certificado por inscripción y PDF válido en almacenamiento privado.
8. Bloqueo de traversal de ruta y proveedores de video no autorizados.
9. Sintaxis PHP de los 20 archivos ejecutables de la etapa.
10. Segunda ejecución de la migración sin error.
11. Limpieza de datos temporales: cero usuarios de prueba y cero certificados huérfanos.

## Antes y después

Antes, el panel de certificados era una pantalla estática; los contenidos y evaluaciones heredados no estaban ligados al nuevo pago/sección, abrir páginas podía confundirse con avance y no existía una cadena segura entrega–revisión–progreso–PDF.

Después, cada sección tiene un temario ordenado por módulos; cada estudiante tiene intentos, evidencias, revisiones, nota y progreso independientes; docente y administrador disponen de supervisión; el certificado oficial se habilita únicamente al cumplir todos los requisitos y se descarga con autorización.
