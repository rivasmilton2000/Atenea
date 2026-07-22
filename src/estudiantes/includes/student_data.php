<?php
declare(strict_types=1);

function mensajesNoLeidosEstudianteAtenea(PDO $pdo,int $usuarioId): int
{
    try{
        $q=$pdo->prepare("SELECT COUNT(*) FROM chat_mensajes m JOIN chat_participantes p ON p.conversacion_id=m.conversacion_id AND p.usuario_id=:u AND p.archivado_at IS NULL LEFT JOIN chat_lecturas l ON l.mensaje_id=m.id AND l.usuario_id=:u2 WHERE m.remitente_id<>:u3 AND m.estado='activo' AND l.mensaje_id IS NULL");
        $q->execute(['u'=>$usuarioId,'u2'=>$usuarioId,'u3'=>$usuarioId]);
        return (int)$q->fetchColumn();
    }catch(Throwable $e){
        error_log('Resumen de mensajes del estudiante: '.$e->getMessage());
        return 0;
    }
}
