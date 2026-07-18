<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/capacitaciones.php';
[$script,$eventoId,$pagoId,$sessionId]=array_pad($argv,4,'');
$evento=(object)['id'=>$eventoId,'type'=>'checkout.session.completed'];
$sesion=(object)['id'=>$sessionId,'client_reference_id'=>(string)$pagoId,'amount_total'=>12550,'currency'=>'usd','payment_status'=>'paid','payment_intent'=>'pi_concurrente_'.$pagoId,'metadata'=>(object)['tipo'=>'capacitacion','capacitacion_pago_id'=>(string)$pagoId]];
procesarWebhookCapacitacion($evento,$sesion);
