<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/env.php';

function configuracionCicloCuentasAtenea(): array
{
    $noticeRaw = entornoAtenea('ACCOUNT_NOTICE_DAYS', '90,30,7');
    $noticeDays = array_values(array_unique(array_filter(array_map('intval', explode(',', $noticeRaw)), static fn(int $d): bool => $d > 0 && $d <= 365)));
    rsort($noticeDays);
    return [
        'inactivity_years' => max(1, min(20, (int)entornoAtenea('ACCOUNT_INACTIVITY_YEARS', '3'))),
        'notice_days' => $noticeDays ?: [90,30,7],
        'grace_days' => max(7, min(365, (int)entornoAtenea('ACCOUNT_GRACE_DAYS', '30'))),
        'enabled' => filter_var(entornoAtenea('ACCOUNT_CLEANUP_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN),
    ];
}
