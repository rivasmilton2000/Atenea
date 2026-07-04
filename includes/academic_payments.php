<?php

require_once __DIR__ . '/app_security.php';
require_once __DIR__ . '/atenea_auth.php';

if (!function_exists('atenea_academic_h')) {
    function atenea_academic_h($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('atenea_academic_money')) {
    function atenea_academic_money($value): float
    {
        $amount = round((float) $value, 2);
        if ($amount < 0) {
            throw new RuntimeException('El monto no puede ser negativo.');
        }

        return $amount;
    }
}

if (!function_exists('atenea_academic_assert_tables')) {
    function atenea_academic_assert_tables(mysqli $db): void
    {
        foreach (['academic_cycles', 'academic_payment_plans', 'academic_charges', 'academic_payments', 'academic_payment_details'] as $table) {
            if (!atenea_db_has_table($db, $table)) {
                throw new RuntimeException('Falta la tabla ' . $table . '. Crea las tablas de pagos academicos antes de usar este modulo.');
            }
        }
    }
}

if (!function_exists('atenea_academic_current_role')) {
    function atenea_academic_current_role(): string
    {
        return trim((string) ($_SESSION['TYPE'] ?? ''));
    }
}

if (!function_exists('atenea_academic_require_role')) {
    function atenea_academic_require_role(array $allowedRoles): void
    {
        $role = atenea_academic_current_role();
        if (!in_array($role, $allowedRoles, true)) {
            atenea_render_auth_alert('warning', 'Acceso restringido', 'Este modulo no esta disponible para tu rol actual.', atenea_dashboard_route_for_session());
        }
    }
}

if (!function_exists('atenea_academic_charge_total')) {
    function atenea_academic_charge_total(array $charge): float
    {
        return max(0.0, round((float) $charge['amount'] + (float) $charge['penalty_amount'] - (float) $charge['discount_amount'], 2));
    }
}

if (!function_exists('atenea_academic_charge_balance')) {
    function atenea_academic_charge_balance(array $charge): float
    {
        return max(0.0, round(atenea_academic_charge_total($charge) - (float) $charge['paid_amount'], 2));
    }
}

if (!function_exists('atenea_academic_status_label')) {
    function atenea_academic_status_label(string $status): string
    {
        $labels = [
            'pending' => 'Pendiente',
            'partial' => 'Parcial',
            'paid' => 'Pagado',
            'overdue' => 'Vencido',
            'cancelled' => 'Anulado',
            'waived' => 'Exonerado',
            'pending_payment' => 'Pendiente de pago',
            'failed' => 'Fallido',
            'refunded' => 'Reembolsado',
        ];

        return $labels[$status] ?? ucfirst($status);
    }
}

if (!function_exists('atenea_academic_badge_class')) {
    function atenea_academic_badge_class(string $status): string
    {
        switch ($status) {
            case 'paid':
                return 'badge-success';
            case 'partial':
                return 'badge-info';
            case 'overdue':
            case 'failed':
                return 'badge-danger';
            case 'cancelled':
            case 'waived':
            case 'refunded':
                return 'badge-secondary';
            default:
                return 'badge-warning';
        }
    }
}

if (!function_exists('atenea_academic_refresh_charge_status')) {
    function atenea_academic_refresh_charge_status(mysqli $db, int $chargeId): void
    {
        $stmt = $db->prepare('SELECT amount, discount_amount, penalty_amount, paid_amount, due_date, status FROM academic_charges WHERE id = ? LIMIT 1');
        if (!$stmt) {
            throw new RuntimeException('No se pudo consultar el cargo academico.');
        }

        $stmt->bind_param('i', $chargeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $charge = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        $stmt->close();

        if (!$charge || in_array((string) $charge['status'], ['cancelled', 'waived'], true)) {
            return;
        }

        $balance = atenea_academic_charge_balance($charge);
        if ($balance <= 0.0) {
            $status = 'paid';
        } elseif ((float) $charge['paid_amount'] > 0.0) {
            $status = 'partial';
        } elseif (!empty($charge['due_date']) && strtotime((string) $charge['due_date']) < strtotime(date('Y-m-d'))) {
            $status = 'overdue';
        } else {
            $status = 'pending';
        }

        $update = $db->prepare('UPDATE academic_charges SET status = ? WHERE id = ? LIMIT 1');
        if (!$update) {
            throw new RuntimeException('No se pudo actualizar el estado del cargo academico.');
        }
        $update->bind_param('si', $status, $chargeId);
        $update->execute();
        $update->close();
    }
}

if (!function_exists('atenea_academic_fetch_cycles')) {
    function atenea_academic_fetch_cycles(mysqli $db): array
    {
        $result = mysqli_query($db, 'SELECT id, name, is_active FROM academic_cycles ORDER BY is_active DESC, starts_on DESC, id DESC');
        $rows = [];
        while ($result instanceof mysqli_result && ($row = $result->fetch_assoc())) {
            $rows[] = $row;
        }
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        return $rows;
    }
}

if (!function_exists('atenea_academic_fetch_students')) {
    function atenea_academic_fetch_students(mysqli $db): array
    {
        $result = mysqli_query(
            $db,
            "SELECT e.ESTUDIANTE_ID, e.nombres_estudiante, e.apellidos_estudiante, e.correo_estudiante, g.G_NAME
             FROM estudiantes e
             LEFT JOIN grados g ON g.G_ID = e.grado_id_estudiante
             WHERE e.estado_estudiante = 1
             ORDER BY e.apellidos_estudiante ASC, e.nombres_estudiante ASC"
        );
        $rows = [];
        while ($result instanceof mysqli_result && ($row = $result->fetch_assoc())) {
            $rows[] = $row;
        }
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }

        return $rows;
    }
}

if (!function_exists('atenea_academic_fetch_charges')) {
    function atenea_academic_fetch_charges(mysqli $db, ?int $studentId = null, bool $payableOnly = false): array
    {
        $where = [];
        $types = '';
        $values = [];

        if ($studentId !== null && $studentId > 0) {
            $where[] = 'ac.student_id = ?';
            $types .= 'i';
            $values[] = $studentId;
        }

        if ($payableOnly) {
            $where[] = "ac.status IN ('pending','partial','overdue')";
        }

        $sql = "SELECT ac.*, cy.name AS cycle_name, e.nombres_estudiante, e.apellidos_estudiante, e.correo_estudiante, g.G_NAME
                FROM academic_charges ac
                JOIN academic_cycles cy ON cy.id = ac.cycle_id
                JOIN estudiantes e ON e.ESTUDIANTE_ID = ac.student_id
                LEFT JOIN grados g ON g.G_ID = e.grado_id_estudiante";

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= " ORDER BY COALESCE(ac.due_date, '9999-12-31') ASC, ac.id DESC LIMIT 500";
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('No se pudo cargar el estado de cuenta.');
        }

        if ($types !== '') {
            $stmt->bind_param($types, ...$values);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while ($result instanceof mysqli_result && ($row = $result->fetch_assoc())) {
            $row['total_amount'] = atenea_academic_charge_total($row);
            $row['balance'] = atenea_academic_charge_balance($row);
            $rows[] = $row;
        }
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        $stmt->close();

        return $rows;
    }
}

if (!function_exists('atenea_academic_apply_paid_payment')) {
    function atenea_academic_apply_paid_payment(mysqli $db, int $paymentId, string $paymentIntent = ''): void
    {
        mysqli_begin_transaction($db);
        try {
            $stmt = $db->prepare('SELECT id, status FROM academic_payments WHERE id = ? LIMIT 1 FOR UPDATE');
            if (!$stmt) {
                throw new RuntimeException('No se pudo bloquear el pago.');
            }
            $stmt->bind_param('i', $paymentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $payment = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
            if ($result instanceof mysqli_result) {
                mysqli_free_result($result);
            }
            $stmt->close();

            if (!$payment) {
                throw new RuntimeException('Pago academico no encontrado.');
            }
            if ((string) $payment['status'] === 'paid') {
                mysqli_commit($db);
                return;
            }

            $detailsStmt = $db->prepare('SELECT charge_id, amount FROM academic_payment_details WHERE payment_id = ?');
            if (!$detailsStmt) {
                throw new RuntimeException('No se pudo cargar el detalle del pago.');
            }
            $detailsStmt->bind_param('i', $paymentId);
            $detailsStmt->execute();
            $detailsResult = $detailsStmt->get_result();
            $details = [];
            while ($detailsResult instanceof mysqli_result && ($row = $detailsResult->fetch_assoc())) {
                $details[] = $row;
            }
            if ($detailsResult instanceof mysqli_result) {
                mysqli_free_result($detailsResult);
            }
            $detailsStmt->close();

            foreach ($details as $detail) {
                $amount = (float) $detail['amount'];
                $chargeId = (int) $detail['charge_id'];
                $update = $db->prepare(
                    "UPDATE academic_charges
                     SET paid_amount = LEAST(amount + penalty_amount - discount_amount, paid_amount + ?)
                     WHERE id = ? AND status NOT IN ('cancelled','waived') LIMIT 1"
                );
                if (!$update) {
                    throw new RuntimeException('No se pudo actualizar el cargo.');
                }
                $update->bind_param('di', $amount, $chargeId);
                $update->execute();
                $update->close();
                atenea_academic_refresh_charge_status($db, $chargeId);
            }

            $status = 'paid';
            $updatePayment = $db->prepare('UPDATE academic_payments SET status = ?, stripe_payment_intent = NULLIF(?, \'\'), paid_at = NOW() WHERE id = ? LIMIT 1');
            if (!$updatePayment) {
                throw new RuntimeException('No se pudo confirmar el pago.');
            }
            $updatePayment->bind_param('ssi', $status, $paymentIntent, $paymentId);
            $updatePayment->execute();
            $updatePayment->close();

            mysqli_commit($db);
        } catch (Throwable $exception) {
            mysqli_rollback($db);
            throw $exception;
        }
    }
}

if (!function_exists('atenea_academic_flash_set')) {
    function atenea_academic_flash_set(string $type, string $message): void
    {
        $_SESSION['ATENEA_ACADEMIC_FLASH'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('atenea_academic_flash_pull')) {
    function atenea_academic_flash_pull(): ?array
    {
        $flash = $_SESSION['ATENEA_ACADEMIC_FLASH'] ?? null;
        unset($_SESSION['ATENEA_ACADEMIC_FLASH']);

        return is_array($flash) ? $flash : null;
    }
}
