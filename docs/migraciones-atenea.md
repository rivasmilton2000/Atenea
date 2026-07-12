# Migraciones de Atenea

Las migraciones se aplican en orden cronologico desde `Database/migrations` y nunca deben exponerse en mensajes de la interfaz.

## Pago antes de inscripcion

Aplicar `2026_07_11_capacitacion_pago_antes_inscripcion.sql` despues de las migraciones de capacitacion del 9 de julio.

La migracion crea `course_payment_requests` y cambia los valores iniciales de `course_enrollments` a estados no activos. El flujo operativo es:

1. El estudiante pulsa `Pagar ahora` y Atenea crea una orden pendiente.
2. Stripe Checkout procesa el pago de una sola vez.
3. Atenea verifica la sesion de Stripe en el retorno exitoso.
4. La orden y el pago cambian a pagado y la inscripcion se activa en la misma transaccion.
5. Si Stripe cancela o no confirma, no se crea la inscripcion.
6. Videos, progreso, aprobacion y certificado verifican el pago confirmado.

Antes de desplegar, ejecutar la migracion con las credenciales del entorno y comprobar:

```sql
SHOW TABLES LIKE 'course_payment_requests';
SHOW COLUMNS FROM course_enrollments LIKE 'estado_curso';
```

Si el esquema no esta disponible, la interfaz muestra un mensaje temporal y amigable; los detalles se consultan exclusivamente en logs o durante el despliegue.
