<?php
declare(strict_types=1);

function autorizarPortalEstudianteAtenea(bool $permitirPerfilIncompleto=false): array
{
    $permitirPerfilIncompleto ? exigirRol(['usuario']) : exigirPerfilCompleto();
    $perfil=obtenerPerfilUsuario((int)($_SESSION['usuario_id']??0));
    if(!$perfil || (string)($perfil['rol']??'')!=='usuario'){
        header('Location: '.atenea_url('src/login/logout.php'));
        exit;
    }
    return $perfil;
}
