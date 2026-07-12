<?php
declare(strict_types=1);
require_once __DIR__ . '/conexion.php';
function configuracionPortalPredeterminada(): array { return [
'login_titulo'=>'Iniciar sesión','login_subtitulo'=>'Accede a tu cuenta de estudiante de Atenea','login_texto_boton'=>'Iniciar sesión','login_imagen_fondo'=>'','login_imagen_lateral'=>'src/estudiantes/assets/images/auth/01.png',
'registro_titulo'=>'Crear una cuenta','registro_subtitulo'=>'Regístrate como estudiante de Atenea','registro_texto_boton'=>'Crear cuenta','registro_imagen_fondo'=>'','registro_imagen_lateral'=>'src/estudiantes/assets/images/auth/02.png',
'panel_titulo'=>'Portal del estudiante','panel_subtitulo'=>'Tu espacio de aprendizaje en Atenea','panel_texto_bienvenida'=>'Bienvenido a tu portal','panel_imagen_banner'=>'','panel_imagen_fondo'=>'','portal_logo'=>'img/atenea-logo.png','avatar_predeterminado'=>'src/estudiantes/assets/images/avatars/01.png','texto_pie_pagina'=>'Atenea Escuela de Naturopatía Holística']; }
function obtenerConfiguracionesPortalEstudiante(): array { static $d; if(is_array($d))return $d; $d=configuracionPortalPredeterminada(); try { foreach(obtenerConexion()->query('SELECT clave,valor FROM configuracion_portal_estudiante')->fetchAll() as $f) if(array_key_exists($f['clave'],$d)&&$f['valor']!=='')$d[$f['clave']]=$f['valor']; } catch(Throwable $e){error_log('Portal estudiante: '.$e->getMessage());} return $d; }
function obtenerConfiguracionPortalEstudiante(string $clave,string $fallback=''): string { return (string)(obtenerConfiguracionesPortalEstudiante()[$clave]??$fallback); }
