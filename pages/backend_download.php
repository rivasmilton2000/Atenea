<?php
require_once __DIR__ . '/session.php';

confirm_logged_in();

atenea_redirect_disabled_module(
    'Modulo desactivado',
    'La descarga del modulo de archivos heredado ya no esta disponible en Atenea.'
);
