<?php
require_once __DIR__ . '/session.php';

confirm_logged_in();

atenea_redirect_disabled_module(
    'Modulo desactivado',
    'La eliminacion de archivos del modulo heredado ya no forma parte del sistema Atenea.'
);
