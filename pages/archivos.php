<?php
require_once __DIR__ . '/session.php';

confirm_logged_in();

atenea_redirect_disabled_module(
    'Modulo desactivado',
    'El gestor de archivos heredado fue retirado porque seguia conectado a una base externa ajena a Atenea.'
);
