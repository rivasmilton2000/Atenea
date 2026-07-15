<?php
declare(strict_types=1);
interface DteProviderInterface { public function generate(array $pedido,array $config):array; public function sign(array $documento,array $config):array; public function submit(array $documento,array $config):array; public function queryStatus(string $codigoGeneracion,array $config):array; public function invalidate(array $documento,string $motivo,array $config):array; public function getGraphicalRepresentation(array $documento,array $pedido,array $config):string; }
