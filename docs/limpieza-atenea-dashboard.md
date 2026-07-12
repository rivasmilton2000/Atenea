# Limpieza profunda del dashboard de Atenea

## Objetivo
Dejar el panel administrativo y los accesos internos enfocados en Atenea, retirando modulos heredados del sistema escolar anterior sin hacer borrados destructivos a ciegas.

## Modulos eliminados del dashboard, sidebar o rutas principales
- Asignaturas
- Labores
- Vehiculos
- Grados
- Doc. asignaturas
- Distribucion de asignaturas
- Inventario academico heredado
- Evaluaciones heredadas
- Evaluaciones entregadas heredadas
- Notas de estudiantes heredadas
- Calendario de actividades heredado
- Documentacion academica heredada
- Pagos academicos heredados
- Gestor de archivos heredado
- Cuentas / settings legacy (`sa_cuentas_usuarios*`, `user.php`, `us_*.php`, `settings*.php`)

## Modulos conservados
- Inicio
- Estudiantes
- Docentes / Facilitadores
- Cursos / Capacitacion
- Inscripciones
- Videos de capacitacion
- Record escolar
- Certificados
- Pagos / Compras
- Productos / Tienda
- Noticias
- Galeria
- Pagina publica / Homepage
- Configuracion del sitio
- Usuarios
- Roles y permisos
- Perfil

## Modulos adaptados
- `programas_educativos` se presenta como `Cursos / Capacitacion`.
- `course_enrollments` se presenta como `Inscripciones`.
- `course_videos` se presenta como `Videos de capacitacion`.
- `ordenes` + `dte_documents` se presentan como `Pagos / Compras`.
- `productos` + `categorias_productos` se presentan como `Productos / Tienda`.
- La gestion vieja de cuentas se reemplaza por `Usuarios` y `Roles y permisos`.
- El rol docente se mantiene visible como `Docentes / Facilitadores`.

## Nuevo sidebar
### Panel
- Inicio

### Gestion
- Estudiantes
- Docentes / Facilitadores
- Cursos / Capacitacion
- Inscripciones
- Videos de capacitacion
- Record escolar
- Certificados
- Pagos / Compras
- Productos / Tienda

### Contenido
- Noticias
- Galeria
- Pagina publica / Homepage
- Configuracion del sitio

### Sistema
- Usuarios
- Roles y permisos
- Perfil
- Cerrar sesion

## Dashboard actual
Las tarjetas principales de `dashboard_admin.php` y `sa_vista.php` ahora muestran:
- Estudiantes registrados
- Cursos / Capacitaciones
- Inscripciones activas
- Videos de capacitacion
- Certificados emitidos
- Pagos / Compras
- Productos / Tienda
- Usuarios activos

## Como funciona ahora Estudiantes
- La fuente principal es `users` + `public_users`.
- Se deja fuera del flujo principal la tabla legacy `estudiantes`.
- Los registros normales, con Google y creados desde administracion quedan como usuarios reales del sistema.
- Si un usuario publico antiguo tenia `TYPE_ID` vacio, ahora se normaliza a `TYPE_ID = 3` (`Estudiante`).
- El listado muestra:
  - nombre completo
  - correo
  - telefono
  - fecha de alta
  - tipo de registro (`Normal`, `Google`, `Admin`)
  - estado
  - curso activo
  - acciones de perfil, cursos y record escolar
- El boton `Agregar estudiante` ahora crea:
  - una cuenta real en `users`
  - un perfil real en `public_users`
  - rol `Estudiante`
  - origen `admin`

## Como funciona ahora Videos de capacitacion
- La administracion usa `curso_videos_admin.php` y `curso_videos_edit.php`.
- Los videos pueden ser:
  - por enlace
  - por archivo subido
- Cada video queda ligado a un curso mediante `programa_id`.
- El acceso permite:
  - activacion masiva para todos los inscritos del curso
  - activacion individual por inscripcion
- Los estudiantes solo ven:
  - videos activos
  - videos de cursos en los que tienen inscripcion valida
  - videos habilitados por acceso masivo o individual
- El progreso por video y la liberacion de certificado se apoya en:
  - `course_video_access`
  - `course_video_progress`
  - `course_enrollments`

## Tablas afectadas
### Tablas activas
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
- `dte_documents`
- `about`
- `facilities`
- `noticias`
- `galeria`
- `configmail`

### Tablas legacy que quedan obsoletas o bloqueadas
- `estudiantes`
- `asignaturas`
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

## Rutas eliminadas o bloqueadas
Se bloquearon por `atenea_legacy_route_groups()` o se redirigen a su equivalente Atenea:
- `archivos.php`
- `asignaturas*.php`
- `sa_asignaturas*.php`
- `labores*.php`
- `sa_labores*.php`
- `vehiculos*.php`
- `sa_vehiculos*.php`
- `grados*.php`
- `sa_grados*.php`
- `doc_asignaturas*.php`
- `sa_doc_asignaturas*.php`
- `dis_asignaturas*.php`
- `sa_dis_asignaturas*.php`
- `inventario*.php`
- `sa_inventario*.php`
- `documentacion*.php`
- `sa_documentacion*.php`
- `evaluaciones*.php`
- `sa_evaluaciones*.php`
- `sa_eva_entregadas*.php`
- `sa_not_estudiantes*.php`
- `calendario*.php`
- `sa_calendario*.php`
- `videos_admin.php`
- `videos_edit.php`
- `videos_transac.php`
- `pagos_academicos*.php`
- `academic_payment_*.php`
- `sa_cuentas_usuarios*.php`
- `user.php`
- `us_*.php`
- `settings.php`
- `settings_edit.php`

## Cambios de permisos
- `SuperAdmin`: acceso total.
- `Admin`: acceso a estudiantes, cursos, inscripciones, videos, pagos, certificados, productos y contenido.
- `Estudiante`: acceso a cuenta publica, curso activo, videos habilitados, record escolar, certificados y compras.
- `Docente / Facilitador`: panel reducido mientras se valida la siguiente fase funcional.
- `Personal`: panel reducido mientras se decide si seguira dentro del producto.
- Los endpoints de contenido y catalogo (`about`, `servicios`, `noticias`, `galeria`, `configmail`, `productos`) ahora exigen `Admin` o `SuperAdmin`.
- Los modulos legacy ya no dependen solo de ocultamiento visual: tambien quedan bloqueados por ruta directa.

## Archivos modificados
### Helpers y autenticacion
- `includes/atenea_auth.php`
- `includes/material_dashboard.php`
- `includes/atenea_admin.php`

### Sidebars
- `includes/sidebar.php`
- `includes/sidebar_admin.php`
- `includes/sidebar_superadmin.php`
- `includes/sidebar_estudiante.php`
- `includes/sidebar_docente.php`
- `includes/sidebar_personal.php`

### Dashboards
- `pages/dashboard_admin.php`
- `pages/sa_vista.php`
- `pages/estudiante_vista.php`
- `pages/docentes_vista.php`
- `pages/empleados_vista.php`

### Estudiantes / usuarios / control academico
- `pages/estudiantes.php`
- `pages/sa_estudiantes.php`
- `pages/estudiante_usuario.php`
- `pages/inscripciones_admin.php`
- `pages/record_escolar_admin.php`
- `pages/usuarios_admin.php`
- `pages/roles_permisos.php`

### Compras, sitio y contenido
- `pages/compras_admin.php`
- `pages/pagina_publica_admin.php`
- `pages/configuracion_sitio.php`
- `pages/about_admin.php`
- `pages/about_transac.php`
- `pages/servicios.php`
- `pages/servicios_transac.php`
- `pages/servicios_delete.php`
- `pages/noticias_admin.php`
- `pages/noticias_edit.php`
- `pages/noticias_transac.php`
- `pages/noticias_delete.php`
- `pages/galeria_home.php`
- `pages/galeria_transac.php`
- `pages/galeria_delete.php`
- `pages/configmail_admin.php`
- `pages/configmail_transac.php`
- `pages/productos_admin.php`
- `pages/productos_add.php`
- `pages/productos_edit.php`
- `pages/productos_transac.php`
- `pages/productos_delete.php`
- `pages/categorias_productos.php`

### Registro / autenticacion publica
- `pages/processregister.php`
- `pages/process_google_register.php`

### Migraciones y docs
- `Database/migrations/2026_07_10_limpieza_atenea.sql`
- `docs/limpieza-atenea-dashboard.md`

## Resultado operativo esperado
- El menu principal ya no expone modulos del sistema anterior.
- El dashboard solo muestra metricas utiles para Atenea.
- Estudiantes se alimenta de usuarios reales.
- Los usuarios con Google o registro normal aparecen como estudiantes cuando su cuenta corresponde al rol `Estudiante`.
- Los videos del curso siguen el flujo real de cursos, inscripciones y accesos.
- Las rutas legacy quedan documentadas y bloqueadas, sin dejar enlaces rotos en la navegacion principal.
