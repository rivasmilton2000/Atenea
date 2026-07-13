# Stripe Checkout

Copie `.env.example` como `.env` y agregue las claves del modo elegido. Use juntas las claves `pk_test_`, `sk_test_` y el secreto `whsec_` obtenido con Stripe CLI para pruebas. Para producción sustituya las dos primeras por `pk_live_` y `sk_live_`, cree un webhook de producción y reemplace también `STRIPE_WEBHOOK_SECRET`; nunca mezcle modos.

URL del webhook local: `http://localhost/Atenea/src/pagos/webhook.php` (Stripe no puede acceder a localhost sin Stripe CLI).

URL pública: `https://SU-DOMINIO/Atenea/src/pagos/webhook.php`.

Eventos: `checkout.session.completed`, `checkout.session.expired`, `payment_intent.payment_failed` y `charge.refunded`.

Pruebas locales: `stripe listen --forward-to http://localhost/Atenea/src/pagos/webhook.php`. Copie el `whsec_...` mostrado a `.env`. Luego use Stripe Checkout con las tarjetas de prueba documentadas por Stripe. En otra instalación ejecute `composer install --no-dev --optimize-autoloader` dentro de `includes/stripe`.
