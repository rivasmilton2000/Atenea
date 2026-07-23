<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once dirname(__DIR__,2).'/includes/stripe_finalizacion.php';$pdo=obtenerConexion();$limite=max(1,min(100,(int)($argv[1]??25)));$sesiones=[];
$q=$pdo->query("SELECT stripe_checkout_session_id FROM pedidos WHERE es_intencion_checkout=1 AND payment_status='pending' AND stripe_checkout_session_id IS NOT NULL ORDER BY id LIMIT {$limite}");$sesiones=array_merge($sesiones,$q->fetchAll(PDO::FETCH_COLUMN));
$restante=max(0,$limite-count($sesiones));if($restante){$q=$pdo->query("SELECT stripe_checkout_session_id FROM capacitacion_pagos WHERE es_intencion_checkout=1 AND estado='pendiente' AND stripe_checkout_session_id IS NOT NULL ORDER BY id LIMIT {$restante}");$sesiones=array_merge($sesiones,$q->fetchAll(PDO::FETCH_COLUMN));}
$r=['revisadas'=>0,'pagadas'=>0,'no_confirmadas'=>0,'errores'=>0];foreach($sesiones as$s){$r['revisadas']++;try{finalizarCompraStripe((string)$s,null,'admin_sync_'.hash('sha256',(string)$s));$r['pagadas']++;}catch(DomainException){$r['no_confirmadas']++;}catch(Throwable$e){$r['errores']++;error_log('Sincronización Stripe '.$s.': '.$e->getMessage());}}echo json_encode($r,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
