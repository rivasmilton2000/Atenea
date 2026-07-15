<?php
declare(strict_types=1);
require_once __DIR__.'/DteProviderInterface.php';
final class SimulationDteProvider implements DteProviderInterface {
 public function generate(array $pedido,array $config):array{return construirJsonDteFactura($pedido,$config);}
 public function sign(array $documento,array $config):array{$documento['_sello']='SIMULADO-'.strtoupper(substr(hash('sha256',json_encode($documento,JSON_THROW_ON_ERROR)),0,32));return $documento;}
 public function submit(array $documento,array $config):array{return ['estado'=>'SIMULADO','sello'=>$documento['_sello']??null,'observaciones'=>'Documento de simulación sin validez fiscal; no fue enviado a Hacienda.'];}
 public function queryStatus(string $codigoGeneracion,array $config):array{return ['estado'=>'SIMULADO','codigoGeneracion'=>$codigoGeneracion];}
 public function invalidate(array $documento,string $motivo,array $config):array{return ['estado'=>'INVALIDADO_SIMULADO','motivo'=>$motivo];}
 public function getGraphicalRepresentation(array $documento,array $pedido,array $config):string{return renderizarPdfDte($documento,$pedido,$config,true);}
}
