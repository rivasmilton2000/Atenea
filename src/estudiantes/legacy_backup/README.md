# Recuperación del portal de estudiantes

El diseño anterior no fue restaurado. La única base visual oficial es el template
conservado en `src/estudiantes/dashboard_estudiantes`.

Antes de reconstruir el módulo se creó una copia íntegra en
`src/estudiantes/_backup_dashboard_estudiantes`. Esa copia es preventiva y no se
carga en producción.

La lógica recuperable se obtuvo del historial de Git de forma selectiva: consultas,
validaciones, acciones y vistas ya adaptadas al template actual. No se restauraron
los assets ni el layout Hope UI eliminado.
