<?php
require_once __DIR__ . '/session.php';

confirm_logged_in();

atenea_redirect_disabled_module(
    'Modulo desactivado',
    'La subida de archivos heredada fue deshabilitada durante la limpieza de Atenea.'
);
