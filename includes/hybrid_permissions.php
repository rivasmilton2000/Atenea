<?php
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/audit.php';

const ATENEA_HYBRID_ROLE = 'administrador_docente';
const ATENEA_HYBRID_LEGACY_ROLE = 'administracion_docente';

function esRolAdministradorDocenteAtenea(?string $rol): bool
{
    return in_array($rol, [ATENEA_HYBRID_ROLE, ATENEA_HYBRID_LEGACY_ROLE], true);
}

function permisosBaseAdministradorDocenteAtenea(): array
{
    return ['hybrid.admin.access','hybrid.docente.access','dashboard.view','users.view','orders.view','orders.manage','audit.view','academic.courses.view','academic.students.view','academic.content.manage','academic.tasks.manage','academic.evaluations.manage','academic.grades.manage','academic.communications.send','academic.calendar.view','academic.notifications.view','academic.tracking.view'];
}

function catalogoPermisosHibridosAtenea(): array
{
    return [
        'Modos' => [
            'hybrid.admin.access' => 'Activar modo Administración',
            'hybrid.docente.access' => 'Activar modo Docente',
        ],
        'Administración' => [
            'dashboard.view' => 'Ver dashboard',
            'users.view' => 'Ver usuarios',
            'users.edit' => 'Gestionar usuarios (sin roles ni permisos)',
            'website.view' => 'Ver contenido del website',
            'website.manage' => 'Editar contenido del website',
            'products.view' => 'Ver productos',
            'products.manage' => 'Gestionar productos',
            'training.view' => 'Ver capacitaciones',
            'training.manage' => 'Gestionar capacitaciones',
            'orders.view' => 'Ver compras',
            'orders.manage' => 'Actualizar estados de compras',
            'communications.view' => 'Ver correos',
            'communications.reply' => 'Responder correos',
            'newsletter.view' => 'Consultar boletín y campañas',
            'newsletter.manage' => 'Gestionar campañas del boletín',
            'newsletter.export' => 'Exportar suscriptores del boletín',
            'reports.view' => 'Ver reportes',
            'notifications.view' => 'Ver notificaciones',
            'audit.view' => 'Ver bitácoras limitadas',
        ],
        'Docencia' => [
            'academic.courses.view' => 'Ver clases asignadas',
            'academic.students.view' => 'Ver estudiantes asignados',
            'academic.content.manage' => 'Publicar contenidos',
            'academic.tasks.manage' => 'Crear tareas',
            'academic.evaluations.manage' => 'Crear evaluaciones',
            'academic.grades.manage' => 'Registrar calificaciones',
            'academic.communications.send' => 'Enviar mensajes',
            'academic.calendar.view' => 'Usar calendario',
            'academic.notifications.view' => 'Ver notificaciones docentes',
            'academic.tracking.view' => 'Consultar seguimiento académico',
        ],
    ];
}

function clavesPermisosHibridosAtenea(): array
{
    $claves = [];
    foreach (catalogoPermisosHibridosAtenea() as $grupo) $claves = array_merge($claves, array_keys($grupo));
    return $claves;
}

function permisoHibridoUsuarioAtenea(int $usuarioId, string $permiso, ?PDO $pdo = null): bool
{
    if ($usuarioId < 1 || !in_array($permiso, clavesPermisosHibridosAtenea(), true)) return false;
    try {
        $pdo ??= obtenerConexion();
        $q = $pdo->prepare('SELECT habilitado FROM usuario_permisos WHERE usuario_id=:usuario AND permiso=:permiso LIMIT 1');
        $q->execute(['usuario' => $usuarioId, 'permiso' => $permiso]);
        $valor=$q->fetchColumn();
        return $valor===false ? in_array($permiso,permisosBaseAdministradorDocenteAtenea(),true) : (int)$valor===1;
    } catch (Throwable $e) {
        error_log('Permiso híbrido Atenea: ' . $e->getMessage());
        return false;
    }
}

function permisosHibridosUsuarioAtenea(int $usuarioId, ?PDO $pdo = null): array
{
    $resultado = array_fill_keys(clavesPermisosHibridosAtenea(), false);
    foreach(permisosBaseAdministradorDocenteAtenea() as$permiso)if(array_key_exists($permiso,$resultado))$resultado[$permiso]=true;
    if ($usuarioId < 1) return $resultado;
    $pdo ??= obtenerConexion();
    $q = $pdo->prepare('SELECT permiso,habilitado FROM usuario_permisos WHERE usuario_id=:usuario');
    $q->execute(['usuario' => $usuarioId]);
    foreach ($q->fetchAll() as $fila) if (array_key_exists($fila['permiso'], $resultado)) $resultado[$fila['permiso']] = (int)$fila['habilitado'] === 1;
    return $resultado;
}

function guardarPermisosHibridosAtenea(int $usuarioId, array $habilitados, int $actorId, ?PDO $pdo = null): void
{
    if ($usuarioId < 1 || $actorId < 1 || $usuarioId === $actorId) throw new DomainException('No puedes modificar tus propios permisos.');
    $pdo ??= obtenerConexion();
    $q = $pdo->prepare("SELECT rol,estado,deleted_at FROM usuarios WHERE id=:id FOR UPDATE");
    $q->execute(['id' => $usuarioId]);
    $usuario = $q->fetch();
    if (!$usuario || !esRolAdministradorDocenteAtenea((string)$usuario['rol']) || $usuario['estado'] !== 'activo' || $usuario['deleted_at']) throw new DomainException('La cuenta híbrida no está disponible.');

    $actuales = permisosHibridosUsuarioAtenea($usuarioId, $pdo);
    $permitidos = array_fill_keys(array_intersect(array_map('strval', $habilitados), clavesPermisosHibridosAtenea()), true);
    $upsert = $pdo->prepare('INSERT INTO usuario_permisos(usuario_id,permiso,habilitado,actualizado_por) VALUES(:usuario,:permiso,:valor,:actor) ON DUPLICATE KEY UPDATE habilitado=VALUES(habilitado),actualizado_por=VALUES(actualizado_por),updated_at=CURRENT_TIMESTAMP');
    $historial = $pdo->prepare('INSERT INTO usuario_permisos_historial(usuario_id,permiso,valor_anterior,valor_nuevo,cambiado_por) VALUES(:usuario,:permiso,:anterior,:nuevo,:actor)');
    $cambios = [];
    foreach (clavesPermisosHibridosAtenea() as $permiso) {
        $nuevo = isset($permitidos[$permiso]);
        if ($actuales[$permiso] === $nuevo) continue;
        $upsert->execute(['usuario'=>$usuarioId,'permiso'=>$permiso,'valor'=>(int)$nuevo,'actor'=>$actorId]);
        $historial->execute(['usuario'=>$usuarioId,'permiso'=>$permiso,'anterior'=>(int)$actuales[$permiso],'nuevo'=>(int)$nuevo,'actor'=>$actorId]);
        $cambios[$permiso] = $nuevo;
    }
    if ($cambios) {
        $pdo->prepare('UPDATE usuarios SET session_version=session_version+1 WHERE id=:id')->execute(['id'=>$usuarioId]);
        if (!registrarAuditoria(['actor_user_id'=>$actorId,'target_user_id'=>$usuarioId,'event_type'=>'hybrid.permissions_changed','module'=>'users','entity_type'=>'user','entity_id'=>$usuarioId,'action'=>'update_permissions','result'=>'success','description'=>'Se actualizaron permisos individuales de una cuenta Administración_Docente.','metadata'=>['changes'=>$cambios]], $pdo)) throw new RuntimeException('No fue posible auditar el cambio de permisos.');
    }
}

function modoHibridoActualAtenea(): ?string
{
    if (!esRolAdministradorDocenteAtenea($_SESSION['usuario_rol'] ?? null)) return null;
    $modo = (string)($_SESSION['hybrid_mode'] ?? '');
    if (!in_array($modo, ['admin', 'docente'], true)) return null;
    return permisoHibridoUsuarioAtenea((int)$_SESSION['usuario_id'], 'hybrid.' . $modo . '.access') ? $modo : null;
}

function exigirModoHibridoAtenea(string $modo): void
{
    if (!esRolAdministradorDocenteAtenea($_SESSION['usuario_rol'] ?? null)) return;
    if (modoHibridoActualAtenea() !== $modo) {
        registrarFalloGlobalAtenea('Contexto híbrido no autorizado: ' . $modo, 403);
        mostrarPaginaErrorAtenea(403);
    }
}

function cambiarModoHibridoAtenea(string $modo): string
{
    if (!esRolAdministradorDocenteAtenea($_SESSION['usuario_rol'] ?? null) || !in_array($modo, ['admin','docente'], true)) throw new DomainException('El modo solicitado no es válido.');
    if (!permisoHibridoUsuarioAtenea((int)$_SESSION['usuario_id'], 'hybrid.' . $modo . '.access')) throw new DomainException('Ese modo no está habilitado para tu cuenta.');
    $_SESSION['hybrid_mode'] = $modo;
    registrarAuditoria(['actor_user_id'=>(int)$_SESSION['usuario_id'],'target_user_id'=>(int)$_SESSION['usuario_id'],'event_type'=>'hybrid.mode_changed','module'=>'auth','entity_type'=>'session','action'=>'switch_mode','result'=>'success','description'=>'La cuenta híbrida cambió de contexto.','metadata'=>['mode'=>$modo]]);
    return $modo === 'admin' ? atenea_url('src/administador_docente/dashboard/index.php') : atenea_url('src/docente/index.php');
}

function permisoRutaAdministrativaHibridaAtenea(string $ruta, string $metodo = 'GET'): ?string
{
    $ruta = strtolower(str_replace('\\', '/', $ruta));
    $esEscritura = strtoupper($metodo) !== 'GET' || preg_match('~/(?:accion|guardar|eliminar|editar|crear|actualizar|publicar|restaurar)[^/]*\.php$~', $ruta);
    if (str_contains($ruta, '/src/administador_docente/dashboard/')) return 'hybrid.admin.access';
    if (str_contains($ruta, '/src/comunicaciones/')) return str_contains($ruta, 'redactar') || $esEscritura ? 'communications.reply' : 'communications.view';
    if (str_contains($ruta, '/src/dashboard/usuarios/')) return $esEscritura ? 'users.edit' : 'users.view';
    if (preg_match('~/src/dashboard/(?:website|secciones|elementos|noticias|navbar)/~', $ruta)) return $esEscritura ? 'website.manage' : 'website.view';
    if (preg_match('~/src/dashboard/(?:productos|categorias)/~', $ruta)) return $esEscritura ? 'products.manage' : 'products.view';
    if (preg_match('~/src/dashboard/(?:capacitaciones|academico)/~', $ruta)) return $esEscritura ? 'training.manage' : 'training.view';
    if (preg_match('~/src/dashboard/(?:pedidos|facturas)/~', $ruta)) return $esEscritura ? 'orders.manage' : 'orders.view';
    if (str_contains($ruta, '/src/dashboard/notificaciones/')) return 'notifications.view';
    if (str_contains($ruta, '/src/dashboard/comunicaciones/')) return $esEscritura ? 'communications.reply' : 'communications.view';
    if (str_contains($ruta, '/src/dashboard/newsletter/')) return $esEscritura ? 'newsletter.manage' : 'newsletter.view';
    if (str_contains($ruta, '/src/dashboard/bitacora/')) return str_ends_with($ruta,'/detalle.php') ? null : 'audit.view';
    if ($ruta === '' || str_ends_with($ruta, '/src/dashboard/index.php')) return 'dashboard.view';
    return null;
}

function permisoRutaDocenteHibridaAtenea(string $ruta): ?string
{
    $archivo = strtolower(basename(parse_url($ruta, PHP_URL_PATH) ?: $ruta));
    return match ($archivo) {
        'cursos.php', 'clase.php' => 'academic.courses.view',
        'estudiantes.php' => 'academic.students.view',
        'contenidos.php', 'contenido-vista.php', 'contenido_guardar.php', 'contenido_actualizar.php', 'contenido_eliminar.php', 'contenido-comentario.php' => 'academic.content.manage',
        'tareas.php', 'tarea_guardar.php' => 'academic.tasks.manage',
        'evaluaciones.php', 'evaluacion_guardar.php' => 'academic.evaluations.manage',
        'calificaciones.php', 'calificar.php', 'entregas.php' => 'academic.grades.manage',
        'comunicaciones.php', 'comunicacion_enviar.php' => 'academic.communications.send',
        'calendario.php' => 'academic.calendar.view',
        'progreso.php' => 'academic.tracking.view',
        default => null,
    };
}

function renderizarSelectorModoHibridoAtenea(): void
{
    if (!esRolAdministradorDocenteAtenea($_SESSION['usuario_rol'] ?? null)) return;
    $actual = modoHibridoActualAtenea();
    $admin = permisoHibridoUsuarioAtenea((int)$_SESSION['usuario_id'], 'hybrid.admin.access');
    $docente = permisoHibridoUsuarioAtenea((int)$_SESSION['usuario_id'], 'hybrid.docente.access');
    ?>
    <form class="d-flex align-items-center gap-2 me-3" method="post" action="<?=atenea_url('src/administador_docente/cambiar-modo.php')?>">
      <input type="hidden" name="csrf_token" value="<?=atenea_e(obtenerTokenCsrf())?>">
      <label class="small fw-semibold mb-0 d-none d-lg-inline" for="ateneaHybridMode">Modo actual</label>
      <select class="form-select form-select-sm" id="ateneaHybridMode" name="modo" onchange="this.form.submit()" aria-label="Cambiar contexto de trabajo">
        <option value="" disabled <?=$actual===null?'selected':''?>>Seleccionar</option>
        <?php if($admin):?><option value="admin" <?=$actual==='admin'?'selected':''?>>Administración</option><?php endif;?>
        <?php if($docente):?><option value="docente" <?=$actual==='docente'?'selected':''?>>Docente</option><?php endif;?>
      </select>
      <span class="badge bg-primary text-white d-none d-xl-inline"><?=atenea_e($actual==='admin'?'Administración':($actual==='docente'?'Docente':'Sin acceso'))?></span>
    </form>
    <?php
}
