<?php

require_once __DIR__ . '/atenea_auth.php';

if (!function_exists('atenea_capacitacion_type_options')) {
    function atenea_capacitacion_type_options(): array
    {
        return [
            'curso' => 'Curso',
            'certificacion' => 'Certificación',
        ];
    }
}

if (!function_exists('atenea_capacitacion_normalize_type')) {
    function atenea_capacitacion_normalize_type(?string $type): string
    {
        $normalized = strtolower(trim((string) $type));
        $options = atenea_capacitacion_type_options();

        return array_key_exists($normalized, $options) ? $normalized : 'curso';
    }
}

if (!function_exists('atenea_capacitacion_type_label')) {
    function atenea_capacitacion_type_label(?string $type): string
    {
        $normalized = atenea_capacitacion_normalize_type($type);
        $options = atenea_capacitacion_type_options();

        return $options[$normalized] ?? $options['curso'];
    }
}

if (!function_exists('atenea_capacitacion_schema_flags')) {
    function atenea_capacitacion_schema_flags(mysqli $db): array
    {
        static $cache = [];
        $cacheKey = spl_object_hash($db);

        if (!isset($cache[$cacheKey])) {
            $cache[$cacheKey] = [
                'tipo_programa' => atenea_db_has_column($db, 'programas_educativos', 'tipo_programa'),
                'precio' => atenea_db_has_column($db, 'programas_educativos', 'precio'),
                'duracion' => atenea_db_has_column($db, 'programas_educativos', 'duracion'),
                'modalidad' => atenea_db_has_column($db, 'programas_educativos', 'modalidad'),
                'detalles_programa' => atenea_db_has_column($db, 'programas_educativos', 'detalles_programa'),
                'beneficios' => atenea_db_has_column($db, 'programas_educativos', 'beneficios'),
                'requisitos' => atenea_db_has_column($db, 'programas_educativos', 'requisitos'),
            ];
        }

        return $cache[$cacheKey];
    }
}

if (!function_exists('atenea_capacitacion_select_sql')) {
    function atenea_capacitacion_select_sql(mysqli $db, string $alias = 'pe'): string
    {
        $flags = atenea_capacitacion_schema_flags($db);
        $safeAlias = preg_replace('/[^a-zA-Z0-9_]/', '', $alias) ?: 'pe';

        $selects = [
            $flags['tipo_programa']
                ? "{$safeAlias}.tipo_programa AS tipo_programa"
                : "'curso' AS tipo_programa",
            $flags['precio']
                ? "{$safeAlias}.precio AS precio"
                : '100.00 AS precio',
            $flags['duracion']
                ? "{$safeAlias}.duracion AS duracion"
                : "'' AS duracion",
            $flags['modalidad']
                ? "{$safeAlias}.modalidad AS modalidad"
                : "'' AS modalidad",
            $flags['detalles_programa']
                ? "{$safeAlias}.detalles_programa AS detalles_programa"
                : "'' AS detalles_programa",
            $flags['beneficios']
                ? "{$safeAlias}.beneficios AS beneficios"
                : "'' AS beneficios",
            $flags['requisitos']
                ? "{$safeAlias}.requisitos AS requisitos"
                : "'' AS requisitos",
        ];

        return implode(",\n               ", $selects);
    }
}

if (!function_exists('atenea_capacitacion_price')) {
    function atenea_capacitacion_price(array $programa): float
    {
        $price = $programa['precio'] ?? 100;

        return is_numeric($price) ? (float) $price : 100.0;
    }
}

if (!function_exists('atenea_capacitacion_text_value')) {
    function atenea_capacitacion_text_value($value): string
    {
        return trim((string) $value);
    }
}

if (!function_exists('atenea_capacitacion_text_items')) {
    function atenea_capacitacion_text_items($value): array
    {
        $value = trim((string) $value);
        if ($value === '') {
            return [];
        }

        $normalized = str_replace(["\r\n", "\r"], "\n", $value);
        $lines = preg_split('/\n+/', $normalized) ?: [];
        $items = [];

        foreach ($lines as $line) {
            $line = trim((string) preg_replace('/^[\-\*\x{2022}\d\.\)\s]+/u', '', (string) $line));
            if ($line !== '') {
                $items[] = $line;
            }
        }

        return $items;
    }
}

if (!function_exists('atenea_capacitacion_detail_url')) {
    function atenea_capacitacion_detail_url(int $programId): string
    {
        return 'programa_detalle.php?id=' . $programId;
    }
}

if (!function_exists('atenea_capacitacion_quote_url')) {
    function atenea_capacitacion_quote_url(int $programId): string
    {
        return 'programa_cotizar.php?id=' . $programId;
    }
}

if (!function_exists('atenea_capacitacion_login_quote_url')) {
    function atenea_capacitacion_login_quote_url(int $programId): string
    {
        return atenea_build_login_url(atenea_capacitacion_quote_url($programId), 'quote_required');
    }
}

if (!function_exists('atenea_capacitacion_phase_two_schema_flags')) {
    function atenea_capacitacion_phase_two_schema_flags(mysqli $db): array
    {
        static $cache = [];
        $cacheKey = spl_object_hash($db);

        if (!isset($cache[$cacheKey])) {
            $hasEnrollments = atenea_db_has_table($db, 'course_enrollments');
            $hasVideos = atenea_db_has_table($db, 'course_videos');
            $hasVideoAccess = atenea_db_has_table($db, 'course_video_access');

            $cache[$cacheKey] = [
                'course_enrollments' => $hasEnrollments,
                'course_videos' => $hasVideos,
                'course_video_access' => $hasVideoAccess,
                'course_enrollments_user_id' => $hasEnrollments && atenea_db_has_column($db, 'course_enrollments', 'user_id'),
                'course_videos_mass_enabled' => $hasVideos && atenea_db_has_column($db, 'course_videos', 'mass_enabled'),
                'course_videos_source_type' => $hasVideos && atenea_db_has_column($db, 'course_videos', 'source_type'),
                'course_videos_video_file_path' => $hasVideos && atenea_db_has_column($db, 'course_videos', 'video_file_path'),
                'course_videos_youtube_id' => $hasVideos && atenea_db_has_column($db, 'course_videos', 'youtube_id'),
                'course_video_access_enabled' => $hasVideoAccess && atenea_db_has_column($db, 'course_video_access', 'enabled'),
            ];
        }

        return $cache[$cacheKey];
    }
}

if (!function_exists('atenea_capacitacion_phase_two_ready')) {
    function atenea_capacitacion_phase_two_ready(mysqli $db): bool
    {
        $flags = atenea_capacitacion_phase_two_schema_flags($db);

        return !empty($flags['course_enrollments'])
            && !empty($flags['course_videos'])
            && !empty($flags['course_video_access'])
            && !empty($flags['course_enrollments_user_id'])
            && !empty($flags['course_videos_mass_enabled'])
            && !empty($flags['course_videos_source_type'])
            && !empty($flags['course_videos_video_file_path'])
            && !empty($flags['course_videos_youtube_id'])
            && !empty($flags['course_video_access_enabled']);
    }
}

if (!function_exists('atenea_capacitacion_fetch_program_by_id')) {
    function atenea_capacitacion_fetch_program_by_id(mysqli $db, int $programId, bool $onlyActive = true): ?array
    {
        if ($programId <= 0 || !atenea_db_has_table($db, 'programas_educativos')) {
            return null;
        }

        $sql = "SELECT pe.*,
                       " . atenea_capacitacion_select_sql($db, 'pe') . "
                FROM programas_educativos pe
                WHERE pe.id = ?";

        if ($onlyActive) {
            $sql .= ' AND pe.estado = 1';
        }

        $sql .= ' LIMIT 1';

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $programId);
        $stmt->execute();
        $result = $stmt->get_result();
        $programa = $result instanceof mysqli_result ? $result->fetch_assoc() : null;

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        $stmt->close();

        return $programa ?: null;
    }
}

if (!function_exists('atenea_capacitacion_user_nav_sections')) {
    function atenea_capacitacion_user_nav_sections(string $activePage = 'usuario_vista.php'): array
    {
        return [
            [
                'title' => 'Panel',
                'items' => [
                    ['label' => 'Inicio', 'href' => 'usuario_vista.php', 'icon' => 'dashboard', 'active' => $activePage === 'usuario_vista.php', 'loaderText' => 'Abriendo tu panel...'],
                ],
            ],
            [
                'title' => 'Aprendizaje',
                'items' => [
                    ['label' => 'Mi curso activo', 'href' => 'mi_curso_activo.php', 'icon' => 'workspace_premium', 'active' => $activePage === 'mi_curso_activo.php', 'loaderText' => 'Cargando tu curso activo...'],
                    ['label' => 'Videos del curso', 'href' => 'curso_videos.php', 'icon' => 'play_circle', 'active' => $activePage === 'curso_videos.php', 'loaderText' => 'Cargando videos del curso...'],
                    ['label' => 'Record escolar', 'href' => 'record_escolar.php', 'icon' => 'school', 'active' => $activePage === 'record_escolar.php', 'loaderText' => 'Cargando record escolar...'],
                ],
            ],
            [
                'title' => 'Explorar',
                'items' => [
                    ['label' => 'Capacitacion', 'href' => 'educacion.php', 'icon' => 'school', 'active' => $activePage === 'educacion.php', 'loaderText' => 'Cargando capacitacion...'],
                    ['label' => 'Productos', 'href' => 'productos.php', 'icon' => 'storefront', 'active' => $activePage === 'productos.php', 'loaderText' => 'Cargando productos...'],
                    ['label' => 'Carrito y pago', 'href' => 'carrito.php', 'icon' => 'shopping_cart', 'active' => $activePage === 'carrito.php', 'loaderText' => 'Abriendo carrito y pago...'],
                    ['label' => 'Historial de compras', 'href' => 'historial_compras.php', 'icon' => 'receipt_long', 'active' => $activePage === 'historial_compras.php', 'loaderText' => 'Cargando historial de compras...'],
                    ['label' => 'Sitio publico', 'href' => 'homepage.php', 'icon' => 'public', 'active' => $activePage === 'homepage.php', 'loaderText' => 'Cargando sitio publico...'],
                ],
            ],
        ];
    }
}

if (!function_exists('atenea_capacitacion_normalize_course_status')) {
    function atenea_capacitacion_normalize_course_status(?string $status): string
    {
        $normalized = strtolower(trim((string) $status));
        $allowed = [
            'curso_activo',
            'activo',
            'finalizado',
            'suspendido',
            'cancelado',
            'pendiente',
        ];

        return in_array($normalized, $allowed, true) ? $normalized : 'pendiente';
    }
}

if (!function_exists('atenea_capacitacion_course_status_meta')) {
    function atenea_capacitacion_course_status_meta(?string $status): array
    {
        $status = atenea_capacitacion_normalize_course_status($status);
        $map = [
            'curso_activo' => ['label' => 'Curso activo', 'class' => 'success'],
            'activo' => ['label' => 'Activo', 'class' => 'success'],
            'finalizado' => ['label' => 'Finalizado', 'class' => 'info'],
            'suspendido' => ['label' => 'Suspendido', 'class' => 'warning'],
            'cancelado' => ['label' => 'Cancelado', 'class' => 'danger'],
            'pendiente' => ['label' => 'Pendiente', 'class' => 'secondary'],
        ];

        return $map[$status] ?? $map['pendiente'];
    }
}

if (!function_exists('atenea_capacitacion_normalize_approval_status')) {
    function atenea_capacitacion_normalize_approval_status(?string $status): string
    {
        $normalized = strtolower(trim((string) $status));
        $allowed = [
            'pendiente',
            'en_proceso',
            'aprobado',
            'no_aprobado',
        ];

        return in_array($normalized, $allowed, true) ? $normalized : 'pendiente';
    }
}

if (!function_exists('atenea_capacitacion_approval_status_meta')) {
    function atenea_capacitacion_approval_status_meta(?string $status): array
    {
        $status = atenea_capacitacion_normalize_approval_status($status);
        $map = [
            'pendiente' => ['label' => 'Pendiente', 'class' => 'secondary'],
            'en_proceso' => ['label' => 'En proceso', 'class' => 'warning'],
            'aprobado' => ['label' => 'Aprobado', 'class' => 'success'],
            'no_aprobado' => ['label' => 'No aprobado', 'class' => 'danger'],
        ];

        return $map[$status] ?? $map['pendiente'];
    }
}

if (!function_exists('atenea_capacitacion_progress_percentage')) {
    function atenea_capacitacion_progress_percentage($value): int
    {
        $progress = is_numeric($value) ? (float) $value : 0.0;
        $progress = max(0.0, min(100.0, $progress));

        return (int) round($progress);
    }
}

if (!function_exists('atenea_capacitacion_enrollment_has_video_access')) {
    function atenea_capacitacion_enrollment_has_video_access(array $enrollment): bool
    {
        $courseStatus = atenea_capacitacion_normalize_course_status((string) ($enrollment['estado_curso'] ?? ''));
        $approvalStatus = atenea_capacitacion_normalize_approval_status((string) ($enrollment['estado_aprobacion'] ?? ''));

        return in_array($courseStatus, ['curso_activo', 'activo'], true) || $approvalStatus === 'aprobado';
    }
}

if (!function_exists('atenea_capacitacion_fetch_enrollments_for_public_user')) {
    function atenea_capacitacion_fetch_enrollments_for_public_user(mysqli $db, int $publicUserId, int $programId = 0): array
    {
        if ($publicUserId <= 0 || !atenea_db_has_table($db, 'course_enrollments')) {
            return [];
        }

        $sql = "SELECT ce.*,
                       pe.titulo AS programa_titulo,
                       pe.descripcion_corta AS programa_descripcion_corta,
                       pe.descripcion_completa AS programa_descripcion_completa,
                       pe.imagen AS programa_imagen,
                       pe.nivel AS programa_nivel,
                       pe.instructor AS programa_instructor,
                       " . atenea_capacitacion_select_sql($db, 'pe') . "
                FROM course_enrollments ce
                INNER JOIN programas_educativos pe ON pe.id = ce.programa_id
                WHERE ce.public_user_id = ?";

        $types = 'i';
        $params = [$publicUserId];

        if ($programId > 0) {
            $sql .= ' AND ce.programa_id = ?';
            $types .= 'i';
            $params[] = $programId;
        }

        $sql .= "
            ORDER BY
                CASE
                    WHEN ce.estado_curso = 'curso_activo' THEN 0
                    WHEN ce.estado_aprobacion = 'aprobado' THEN 1
                    ELSE 2
                END,
                ce.updated_at DESC,
                ce.fecha_inscripcion DESC,
                ce.id DESC
        ";

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        if ($programId > 0) {
            $stmt->bind_param('ii', $publicUserId, $programId);
        } else {
            $stmt->bind_param('i', $publicUserId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];

        while ($result instanceof mysqli_result && ($row = $result->fetch_assoc())) {
            $rows[] = $row;
        }

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        $stmt->close();

        return $rows;
    }
}

if (!function_exists('atenea_capacitacion_fetch_active_enrollment_for_public_user')) {
    function atenea_capacitacion_fetch_active_enrollment_for_public_user(mysqli $db, int $publicUserId, int $programId = 0): ?array
    {
        $enrollments = atenea_capacitacion_fetch_enrollments_for_public_user($db, $publicUserId, $programId);

        foreach ($enrollments as $enrollment) {
            if (atenea_capacitacion_enrollment_has_video_access($enrollment)
                || atenea_capacitacion_normalize_course_status((string) ($enrollment['estado_curso'] ?? '')) === 'curso_activo'
            ) {
                return $enrollment;
            }
        }

        return $enrollments[0] ?? null;
    }
}

if (!function_exists('atenea_capacitacion_activate_enrollment')) {
    function atenea_capacitacion_activate_enrollment(mysqli $db, int $publicUserId, int $userId, int $programId): ?array
    {
        if ($publicUserId <= 0 || $userId <= 0 || $programId <= 0 || !atenea_db_has_table($db, 'course_enrollments')) {
            return null;
        }

        $existing = null;
        $stmtExisting = $db->prepare('SELECT id, estado_aprobacion FROM course_enrollments WHERE public_user_id = ? AND programa_id = ? LIMIT 1');
        if ($stmtExisting) {
            $stmtExisting->bind_param('ii', $publicUserId, $programId);
            $stmtExisting->execute();
            $resultExisting = $stmtExisting->get_result();
            $existing = $resultExisting instanceof mysqli_result ? $resultExisting->fetch_assoc() : null;

            if ($resultExisting instanceof mysqli_result) {
                mysqli_free_result($resultExisting);
            }

            $stmtExisting->close();
        }

        if ($existing) {
            $stmtUpdate = $db->prepare(
                "UPDATE course_enrollments
                 SET user_id = ?,
                     estado_curso = 'curso_activo',
                     estado_aprobacion = CASE
                         WHEN estado_aprobacion = 'aprobado' THEN 'aprobado'
                         ELSE 'en_proceso'
                     END,
                     progreso = CASE
                         WHEN progreso < 0 THEN 0
                         WHEN progreso > 100 THEN 100
                         ELSE progreso
                     END
                 WHERE id = ?
                 LIMIT 1"
            );

            if (!$stmtUpdate) {
                return null;
            }

            $enrollmentId = (int) $existing['id'];
            $stmtUpdate->bind_param('ii', $userId, $enrollmentId);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        } else {
            $stmtInsert = $db->prepare(
                "INSERT INTO course_enrollments
                    (public_user_id, user_id, programa_id, estado_curso, estado_aprobacion, progreso)
                 VALUES (?, ?, ?, 'curso_activo', 'en_proceso', 0)"
            );

            if (!$stmtInsert) {
                return null;
            }

            $stmtInsert->bind_param('iii', $publicUserId, $userId, $programId);
            $stmtInsert->execute();
            $stmtInsert->close();
        }

        return atenea_capacitacion_fetch_active_enrollment_for_public_user($db, $publicUserId, $programId);
    }
}

if (!function_exists('atenea_capacitacion_fetch_program_enrollments')) {
    function atenea_capacitacion_fetch_program_enrollments(mysqli $db, int $programId): array
    {
        if ($programId <= 0 || !atenea_db_has_table($db, 'course_enrollments')) {
            return [];
        }

        $stmt = $db->prepare(
            "SELECT ce.*,
                    pu.FIRST_NAME,
                    pu.LAST_NAME,
                    pu.EMAIL,
                    pu.PHONE_NUMBER
             FROM course_enrollments ce
             INNER JOIN public_users pu ON pu.PUBLIC_USER_ID = ce.public_user_id
             WHERE ce.programa_id = ?
             ORDER BY ce.updated_at DESC, ce.fecha_inscripcion DESC, ce.id DESC"
        );

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $programId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];

        while ($result instanceof mysqli_result && ($row = $result->fetch_assoc())) {
            $rows[] = $row;
        }

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        $stmt->close();

        return $rows;
    }
}

if (!function_exists('atenea_capacitacion_fetch_course_videos')) {
    function atenea_capacitacion_fetch_course_videos(mysqli $db, int $programId = 0, bool $onlyActive = false): array
    {
        if (!atenea_db_has_table($db, 'course_videos')) {
            return [];
        }

        $sql = "SELECT cv.*,
                       pe.titulo AS programa_titulo,
                       pe.imagen AS programa_imagen
                FROM course_videos cv
                INNER JOIN programas_educativos pe ON pe.id = cv.programa_id
                WHERE 1 = 1";

        $types = '';
        $params = [];

        if ($programId > 0) {
            $sql .= ' AND cv.programa_id = ?';
            $types .= 'i';
            $params[] = $programId;
        }

        if ($onlyActive) {
            $sql .= ' AND cv.estado = 1';
        }

        $sql .= ' ORDER BY cv.orden ASC, cv.id ASC';

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        if ($programId > 0) {
            $stmt->bind_param('i', $programId);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];

        while ($result instanceof mysqli_result && ($row = $result->fetch_assoc())) {
            $rows[] = $row;
        }

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        $stmt->close();

        return $rows;
    }
}

if (!function_exists('atenea_capacitacion_fetch_course_video_by_id')) {
    function atenea_capacitacion_fetch_course_video_by_id(mysqli $db, int $videoId): ?array
    {
        if ($videoId <= 0 || !atenea_db_has_table($db, 'course_videos')) {
            return null;
        }

        $stmt = $db->prepare(
            "SELECT cv.*,
                    pe.titulo AS programa_titulo,
                    pe.imagen AS programa_imagen
             FROM course_videos cv
             INNER JOIN programas_educativos pe ON pe.id = cv.programa_id
             WHERE cv.id = ?
             LIMIT 1"
        );

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $videoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $video = $result instanceof mysqli_result ? $result->fetch_assoc() : null;

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        $stmt->close();

        return $video ?: null;
    }
}

if (!function_exists('atenea_capacitacion_fetch_video_access_map')) {
    function atenea_capacitacion_fetch_video_access_map(mysqli $db, int $videoId): array
    {
        if ($videoId <= 0 || !atenea_db_has_table($db, 'course_video_access')) {
            return [];
        }

        $stmt = $db->prepare('SELECT * FROM course_video_access WHERE course_video_id = ?');
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $videoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $map = [];

        while ($result instanceof mysqli_result && ($row = $result->fetch_assoc())) {
            $map[(int) ($row['enrollment_id'] ?? 0)] = $row;
        }

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        $stmt->close();

        return $map;
    }
}

if (!function_exists('atenea_capacitacion_set_mass_video_access')) {
    function atenea_capacitacion_set_mass_video_access(mysqli $db, int $videoId, bool $enabled): bool
    {
        if ($videoId <= 0 || !atenea_db_has_table($db, 'course_videos')) {
            return false;
        }

        $stmt = $db->prepare('UPDATE course_videos SET mass_enabled = ? WHERE id = ? LIMIT 1');
        if (!$stmt) {
            return false;
        }

        $enabledFlag = $enabled ? 1 : 0;
        $stmt->bind_param('ii', $enabledFlag, $videoId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}

if (!function_exists('atenea_capacitacion_set_user_video_access')) {
    function atenea_capacitacion_set_user_video_access(mysqli $db, int $videoId, int $enrollmentId, bool $enabled, int $updatedByUserId = 0): bool
    {
        if ($videoId <= 0 || $enrollmentId <= 0 || !atenea_db_has_table($db, 'course_video_access')) {
            return false;
        }

        $stmt = $db->prepare(
            "INSERT INTO course_video_access (course_video_id, enrollment_id, enabled, updated_by_user_id)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                enabled = VALUES(enabled),
                updated_by_user_id = VALUES(updated_by_user_id),
                updated_at = CURRENT_TIMESTAMP"
        );

        if (!$stmt) {
            return false;
        }

        $enabledFlag = $enabled ? 1 : 0;
        $stmt->bind_param('iiii', $videoId, $enrollmentId, $enabledFlag, $updatedByUserId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}

if (!function_exists('atenea_capacitacion_fetch_accessible_videos_for_public_user')) {
    function atenea_capacitacion_fetch_accessible_videos_for_public_user(mysqli $db, int $publicUserId, int $programId = 0): array
    {
        if ($publicUserId <= 0 || !atenea_capacitacion_phase_two_ready($db)) {
            return [];
        }

        $sql = "SELECT cv.*,
                       ce.id AS enrollment_id,
                       ce.estado_curso,
                       ce.estado_aprobacion,
                       pe.titulo AS programa_titulo,
                       pe.imagen AS programa_imagen,
                       COALESCE(cva.enabled, 0) AS individual_enabled
                FROM course_enrollments ce
                INNER JOIN programas_educativos pe ON pe.id = ce.programa_id
                INNER JOIN course_videos cv ON cv.programa_id = ce.programa_id
                LEFT JOIN course_video_access cva
                    ON cva.course_video_id = cv.id
                   AND cva.enrollment_id = ce.id
                WHERE ce.public_user_id = ?
                  AND cv.estado = 1
                  AND (
                        ce.estado_curso = 'curso_activo'
                        OR ce.estado_curso = 'activo'
                        OR ce.estado_aprobacion = 'aprobado'
                  )
                  AND (
                        cv.mass_enabled = 1
                        OR COALESCE(cva.enabled, 0) = 1
                  )";

        $types = 'i';
        $params = [$publicUserId];

        if ($programId > 0) {
            $sql .= ' AND ce.programa_id = ?';
            $types .= 'i';
            $params[] = $programId;
        }

        $sql .= ' ORDER BY cv.orden ASC, cv.id ASC';

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        if ($programId > 0) {
            $stmt->bind_param('ii', $publicUserId, $programId);
        } else {
            $stmt->bind_param('i', $publicUserId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];

        while ($result instanceof mysqli_result && ($row = $result->fetch_assoc())) {
            $rows[] = $row;
        }

        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        $stmt->close();

        return $rows;
    }
}

if (!function_exists('atenea_capacitacion_extract_youtube_id')) {
    function atenea_capacitacion_extract_youtube_id(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        $patterns = [
            '/youtu\.be\/([A-Za-z0-9_-]{6,})/i',
            '/youtube\.com\/watch\?v=([A-Za-z0-9_-]{6,})/i',
            '/youtube\.com\/embed\/([A-Za-z0-9_-]{6,})/i',
            '/youtube\.com\/shorts\/([A-Za-z0-9_-]{6,})/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return '';
    }
}

if (!function_exists('atenea_capacitacion_video_file_public_url')) {
    function atenea_capacitacion_video_file_public_url(?string $relativePath): string
    {
        $relativePath = ltrim(str_replace('\\', '/', trim((string) $relativePath)), '/');
        if ($relativePath === '') {
            return '';
        }

        $projectRoot = realpath(__DIR__ . '/..');
        $absolutePath = realpath(__DIR__ . '/../' . $relativePath);

        if ($projectRoot === false || $absolutePath === false || strpos($absolutePath, $projectRoot) !== 0 || !is_file($absolutePath)) {
            return '';
        }

        return '../' . $relativePath;
    }
}

if (!function_exists('atenea_capacitacion_video_source_meta')) {
    function atenea_capacitacion_video_source_meta(array $video): array
    {
        $sourceType = strtolower(trim((string) ($video['source_type'] ?? 'url')));
        $videoUrl = trim((string) ($video['video_url'] ?? ''));
        $youtubeId = trim((string) ($video['youtube_id'] ?? ''));
        $fileUrl = atenea_capacitacion_video_file_public_url((string) ($video['video_file_path'] ?? ''));

        if ($youtubeId === '' && $videoUrl !== '') {
            $youtubeId = atenea_capacitacion_extract_youtube_id($videoUrl);
        }

        if ($youtubeId !== '') {
            return [
                'type' => 'youtube',
                'label' => 'YouTube',
                'embed_url' => 'https://www.youtube.com/embed/' . $youtubeId,
                'link_url' => $videoUrl !== '' ? $videoUrl : 'https://youtu.be/' . $youtubeId,
            ];
        }

        if ($sourceType === 'upload' && $fileUrl !== '') {
            return [
                'type' => 'upload',
                'label' => 'Archivo',
                'embed_url' => $fileUrl,
                'link_url' => $fileUrl,
            ];
        }

        if ($videoUrl !== '' && preg_match('/\.(mp4|webm|ogg)(\?.*)?$/i', $videoUrl)) {
            return [
                'type' => 'direct',
                'label' => 'Enlace directo',
                'embed_url' => $videoUrl,
                'link_url' => $videoUrl,
            ];
        }

        if ($videoUrl !== '') {
            return [
                'type' => 'external',
                'label' => 'Enlace externo',
                'embed_url' => '',
                'link_url' => $videoUrl,
            ];
        }

        return [
            'type' => 'unknown',
            'label' => 'No disponible',
            'embed_url' => '',
            'link_url' => '',
        ];
    }
}
