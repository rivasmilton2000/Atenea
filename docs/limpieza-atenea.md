# Limpieza Atenea

## Objetivo
Depurar la capa administrativa, los accesos internos y las rutas heredadas para que Atenea quede enfocada en su operacion real como Escuela de Naturopatia Holistica, sin hacer borrados destructivos de tablas o modulos que todavia puedan tener dependencias activas.

## Modulos que se conservan
- Inicio / Dashboard administrativo y SuperAdmin.
- Usuarios y cuentas del sistema.
- Estudiantes.
- Docentes / Facilitadores.
- Cursos / Capacitacion.
- Inscripciones / Matriculas.
- Videos del curso.
- Record escolar.
- Certificados.
- Pagos / Compras y facturacion DTE.
- Productos / Tienda.
- Noticias.
- Galeria.
- Pagina publica.
- Configuracion del sitio / correo.
- Perfil de usuario.

## Modulos que se renombran o adaptan
- `Docente` se presenta en interfaz como `Docente / Facilitador`.
- `Estudiante` se presenta en gestion de cuentas como `Estudiante / Usuario`.
- `programas_educativos` queda presentado como `Cursos / Capacitacion`.
- `course_enrollments` queda presentado como `Inscripciones / Matriculas`.
- `course_videos` queda presentado como `Videos del curso`.
- `dte_documents` y su configuracion se integran bajo `Pagos / Compras` y `Facturacion DTE`.
- `curso_inscripciones_admin.php` concentra la gestion administrativa de inscripciones.
- `record_escolar_admin.php` concentra la vista administrativa del record escolar.

## Modulos que se eliminan del menu, se ocultan o se bloquean
- Asignaturas.
- Archivos heredados.
- Labores.
- Vehiculos.
- Grados.
- Doc. asignaturas.
- Distribucion de asignaturas.
- Inventario academico heredado.
- Documentacion academica heredada.
- Contenidos de evaluacion.
- Evaluaciones.
- Evaluaciones entregadas.
- Notas de estudiantes heredadas.
- Calendario academico heredado.
- Pagos academicos heredados.
- Respaldo BD desde menu principal.
- Reportes e inventario legacy (`supplier`, `transaction`, `reports`, exportaciones antiguas).

## Tablas que se conservan
- `users`
- `type`
- `public_users`
- `programas_educativos`
- `course_enrollments`
- `course_videos`
- `course_video_access`
- `course_video_progress`
- `productos`
- `categorias_productos`
- `ordenes`
- `orden_detalles`
- `orden_facturas`
- `dte_documents`
- `about`
- `facilities`
- `noticias`
- `galeria`
- `configmail`

## Tablas que quedan obsoletas o en observacion
No se eliminan en esta fase por seguridad. Se mantienen fuera del menu y ahora tambien fuera de uso por ruta directa.

- `asignaturas`
- `docentes_asignaturas`
- `estudiantes_docentes`
- `grados`
- `jobs`
- `vehicles`
- `archivos`
- `contenidos`
- `evaluaciones`
- `ev_entregadas`
- `notas`
- `actividades`
- `inventario`
- `academic_charges`
- `academic_cycles`
- tablas `sa_*` / legacy asociadas a flujos escolares antiguos
- tabla externa `u445672402_escuela.archivos` detectada en el modulo heredado `archivos.php`

## Rutas retiradas del menu o desactivadas
- `archivos.php`
- `backend_upload.php`
- `backend_download.php`
- `backend_delete.php`
- `asignaturas.php`
- `sa_asignaturas.php`
- `labores.php`
- `sa_labores.php`
- `vehiculos.php`
- `sa_vehiculos.php`
- `grados.php`
- `sa_grados.php`
- `doc_asignaturas.php`
- `sa_doc_asignaturas.php`
- `dis_asignaturas.php`
- `sa_dis_asignaturas.php`
- `inventario.php`
- `sa_inventario.php`
- `documentacion.php`
- `sa_documentacion.php`
- `con_evaluacion.php`
- `sa_con_evaluacion.php`
- `evaluaciones.php`
- `sa_evaluaciones.php`
- `sa_eva_entregadas.php`
- `sa_not_estudiantes.php`
- `pagos_academicos.php`
- `sa_pagos_academicos.php`
- `estudiante_pagos.php`
- `academic_payment_create.php`
- `academic_payment_success.php`
- `academic_payment_dte_download.php`
- `sa_respaldo_bd.php`

## Archivos eliminados
- Ningun archivo legacy se elimino fisicamente en esta fase.

## Archivos creados
- `docs/limpieza-atenea.md`
- `Database/migrations/2026_07_10_limpieza_atenea.sql`

## Archivos modificados
- `includes/atenea_auth.php`
- `includes/atenea_console.php`
- `includes/sidebar_admin.php`
- `includes/sidebar_superadmin.php`
- `includes/sidebar_estudiante.php`
- `includes/sidebar_docente.php`
- `includes/sidebar_personal.php`
- `pages/session.php`
- `pages/archivos.php`
- `pages/backend_upload.php`
- `pages/backend_download.php`
- `pages/backend_delete.php`
- `pages/dashboard_admin.php`
- `pages/sa_vista.php`
- `pages/estudiante_vista.php`
- `pages/docentes_vista.php`
- `pages/empleados_vista.php`
- `pages/sa_cuentas_usuarios.php`
- `pages/sa_cuentas_usuarios_searchfrm1.php`
- `pages/sa_cuentas_usuarios_searchfrm2.php`
- `pages/sa_cuentas_usuarios_edit1.php`
- `pages/sa_cuentas_usuarios_edit2.php`
- `pages/sa_cuentas_usuarios_edit3.php`
- `pages/sa_cuentas_usuarios_edit4.php`
- `pages/sa_cuentas_usuarios_transac1.php`
- `pages/sa_cuentas_usuarios_transac2.php`
- `pages/sa_cuentas_usuarios_delete.php`
- `pages/about_admin.php`
- `pages/about_transac.php`
- `pages/noticias_admin.php`
- `pages/noticias_edit.php`
- `pages/noticias_transac.php`
- `pages/noticias_delete.php`
- `pages/galeria_home.php`
- `pages/galeria_transac.php`
- `pages/galeria_delete.php`
- `pages/servicios.php`
- `pages/servicios_transac.php`
- `pages/servicios_delete.php`
- `pages/configmail_admin.php`
- `pages/configmail_transac.php`
- `pages/productos_admin.php`
- `pages/productos_add.php`
- `pages/productos_edit.php`
- `pages/productos_transac.php`
- `pages/productos_delete.php`
- `pages/categorias_productos.php`

## Decisiones de permisos
- `SuperAdmin` mantiene acceso total a los modulos de Atenea.
- `Admin` mantiene gestion operativa de cursos, estudiantes, productos, contenido, pagos y certificados.
- `Estudiante`, `Docente` y `Personal` conservan acceso al sistema, pero con paneles simplificados y sin modulos heredados del sistema anterior.
- Los endpoints de gestion de contenido y catalogo se endurecen para `Admin` y `SuperAdmin`.
- Los endpoints de gestion de cuentas (`sa_cuentas_usuarios_*`) ahora exigen `SuperAdmin` de forma explicita.
- Las cuentas internas solo pueden crearse o editarse con roles validos de Atenea: `Admin`, `Personal` y `Docente / Facilitador`.
- Las cuentas de estudiantes quedan fijadas al rol `Estudiante / Usuario`.
- Las rutas heredadas bloqueadas se redirigen a su modulo Atenea equivalente o al dashboard del perfil segun corresponda.

## Riesgos encontrados
- Los roles internos `Personal`, `Estudiante` y `Docente` siguen existiendo en la tabla `type` porque tienen usuarios activos enlazados.
- Existen tablas heredadas del sistema escolar anterior que no se eliminaron todavia para evitar romper vistas antiguas no auditadas.
- Hay doble modelo de usuario: interno (`users`) y publico (`public_users`). Antes de una limpieza destructiva de roles o tablas se debe decidir si ambos seguiran coexistiendo.
- Los modulos legacy siguen presentes en el repositorio, pero ya no se exponen desde los dashboards y ahora tambien quedan bloqueados por ruta directa en esta fase.
- El modulo `archivos.php` estaba conectado a una base externa distinta de Atenea. Quedo neutralizado y documentado como riesgo heredado.

## Recomendacion antes de una limpieza destructiva
1. Validar con usuarios reales si `Personal` seguira existiendo dentro de Atenea.
2. Confirmar si `Docente` se renombrara tambien a nivel de base de datos o solo en interfaz.
3. Revisar si algun reporte, exportacion o integracion externa sigue consultando tablas legacy.
4. Definir si los estudiantes internos y los usuarios publicos terminaran conviviendo o si habra una migracion hacia un solo modelo.
5. Solo despues de esa validacion ejecutar una segunda fase de retiro fisico de archivos y `DROP TABLE` de modulos obsoletos.
