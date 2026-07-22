# Respaldo del dashboard anterior

El diseño Hope UI dejó de ser el layout oficial el 21 de julio de 2026.

Sus assets se conservan temporalmente, sin cambios, en `src/estudiantes/assets/css`,
`src/estudiantes/assets/js`, `src/estudiantes/assets/images` y
`src/estudiantes/assets/vendor`. Ninguna vista oficial carga ahora `hope-ui.css`,
`custom.css`, `dark.css`, `customizer.css`, `rtl.css` ni `hope-ui.js`.

La lógica de los módulos no se movió ni se duplicó: continúa en los controladores
PHP de `src/estudiantes`. El historial de Git conserva además la implementación
anterior de `includes/portal_estudiante_layout.php` para un rollback exacto.

No se deben borrar estos assets hasta completar la validación en producción.
