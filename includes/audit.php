<?php
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';

function requestIdAtenea(): string
{
    static $id = null;
    if ($id === null) {
        $entrante = trim((string) ($_SERVER['HTTP_X_REQUEST_ID'] ?? ''));
        $id = preg_match('/^[a-f0-9]{32}$/i', $entrante) ? strtolower($entrante) : bin2hex(random_bytes(16));
    }
    return $id;
}

function claveAuditoriaSensible(string $clave): bool
{
    return preg_match('/password|contrasena|hash|token|otp|codigo|code|cvc|card|tarjeta|authorization|cookie|session|secret/i', $clave) === 1;
}

function sanitizarMetadataAuditoria(mixed $valor, int $profundidad = 0): mixed
{
    if ($profundidad > 4) return '[limite]';
    if (is_array($valor)) {
        $limpio = [];
        foreach (array_slice($valor, 0, 50, true) as $clave => $item) {
            $claveTexto = (string) $clave;
            if (claveAuditoriaSensible($claveTexto)) continue;
            $limpio[$claveTexto] = sanitizarMetadataAuditoria($item, $profundidad + 1);
        }
        return $limpio;
    }
    if (is_bool($valor) || is_int($valor) || is_float($valor) || $valor === null) return $valor;
    return sanitizarTextoAuditoria((string) $valor, 500);
}

function sanitizarTextoAuditoria(string $texto, int $limite = 500): string
{
    $texto = strip_tags($texto);
    $texto = preg_replace('/\b(?:\d[ -]*?){13,19}\b/', '[dato de pago oculto]', $texto) ?: $texto;
    $texto = preg_replace('/(?i)\b(?:bearer|authorization)\s+[A-Za-z0-9._~+\/-]+=*/', '[credencial oculta]', $texto) ?: $texto;
    $texto = preg_replace('/\b[a-f0-9]{32,}\b/i', '[valor tecnico oculto]', $texto) ?: $texto;
    return mb_substr(preg_replace('/[\r\n\t]+/', ' ', $texto) ?: $texto, 0, $limite);
}

function resumirUserAgentAtenea(string $agente): string
{
    if ($agente === '' || strtoupper($agente) === 'CLI') return 'CLI';
    $navegador = str_contains($agente, 'Edg/') ? 'Edge' : (str_contains($agente, 'Firefox/') ? 'Firefox' : (str_contains($agente, 'Chrome/') ? 'Chrome' : (str_contains($agente, 'Safari/') ? 'Safari' : 'Otro navegador')));
    $sistema = str_contains($agente, 'Windows') ? 'Windows' : (str_contains($agente, 'Android') ? 'Android' : (preg_match('/iPhone|iPad/',$agente) ? 'iOS' : (str_contains($agente, 'Mac OS') ? 'macOS' : (str_contains($agente, 'Linux') ? 'Linux' : 'SO no identificado'))));
    return $navegador . ' / ' . $sistema;
}

function ipAuditoriaAtenea(): ?string
{
    $ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
}

function registrarAuditoria(array $evento, ?PDO $pdo = null): bool
{
    try {
        $pdo ??= obtenerConexion();
        $metadata = sanitizarMetadataAuditoria($evento['metadata'] ?? []);
        $json = $metadata === [] ? null : json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $actorRole = (string)($evento['actor_role'] ?? ($_SESSION['usuario_rol'] ?? ''));
        $activeMode = (string)($evento['active_mode'] ?? ($_SESSION['hybrid_mode'] ?? ''));
        $route = mb_substr((string)($evento['route'] ?? ($_SERVER['REQUEST_URI'] ?? 'CLI')), 0, 255);
        $correlationId = strtolower((string)($evento['correlation_id'] ?? requestIdAtenea()));
        if (!preg_match('/^[a-f0-9]{32}$/', $correlationId)) $correlationId = requestIdAtenea();
        $consulta = $pdo->prepare('INSERT INTO audit_logs(actor_user_id,actor_role,active_mode,target_user_id,event_type,module,entity_type,entity_id,action,result,description,metadata,ip_address,user_agent,route,request_id) VALUES(:actor,:actor_role,:active_mode,:target,:event_type,:module,:entity_type,:entity_id,:action,:result,:description,:metadata,:ip,:agent,:route,:request_id)');
        $consulta->execute([
            'actor' => isset($evento['actor_user_id']) ? (int) $evento['actor_user_id'] : ($_SESSION['usuario_id'] ?? null),
            'actor_role' => $actorRole !== '' ? mb_substr($actorRole, 0, 40) : null,
            'active_mode' => in_array($activeMode, ['admin','docente'], true) ? $activeMode : null,
            'target' => isset($evento['target_user_id']) ? (int) $evento['target_user_id'] : null,
            'event_type' => mb_substr((string) ($evento['event_type'] ?? 'system.event'), 0, 100),
            'module' => mb_substr((string) ($evento['module'] ?? 'system'), 0, 80),
            'entity_type' => !empty($evento['entity_type']) ? mb_substr((string) $evento['entity_type'], 0, 80) : null,
            'entity_id' => isset($evento['entity_id']) ? mb_substr((string) $evento['entity_id'], 0, 100) : null,
            'action' => mb_substr((string) ($evento['action'] ?? 'view'), 0, 100),
            'result' => mb_substr((string) ($evento['result'] ?? 'success'), 0, 30),
            'description' => sanitizarTextoAuditoria((string) ($evento['description'] ?? 'Evento registrado.'), 500),
            'metadata' => $json,
            'ip' => ipAuditoriaAtenea(),
            'agent' => resumirUserAgentAtenea((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'CLI')),
            'route' => sanitizarTextoAuditoria($route, 255),
            'request_id' => $correlationId,
        ]);
        return true;
    } catch (Throwable $error) {
        error_log('Auditoria Atenea: ' . $error->getMessage());
        return false;
    }
}

function actualizarActividadUsuario(int $usuarioId, ?PDO $pdo = null): void
{
    try {
        $pdo ??= obtenerConexion();
        $pdo->prepare('UPDATE usuarios SET last_activity_at=NOW() WHERE id=:id AND deleted_at IS NULL')->execute(['id' => $usuarioId]);
    } catch (Throwable $error) {
        error_log('Actividad Atenea: ' . $error->getMessage());
    }
}
