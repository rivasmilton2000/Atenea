# Google OAuth para Atenea

Esta ruta se conserva para enlaces y despliegues anteriores. La configuración vigente de Correcciones 6, etapa 1, está documentada en [`google_oauth_configuracion.md`](google_oauth_configuracion.md).

La URI ya no se configura mediante `GOOGLE_REDIRECT_URI`: la aplicación la genera desde `APP_URL_LOCAL` o `APP_URL_PRODUCTION` y el callback único `src/auth/google-callback.php`. Consulte la guía vigente antes de cambiar Google Cloud o las variables del servidor.
