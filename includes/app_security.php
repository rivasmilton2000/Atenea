<?php

if (!function_exists('atenea_storage_root')) {
    function atenea_storage_root(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage';
    }
}

if (!function_exists('atenea_storage_ensure_directory')) {
    function atenea_storage_ensure_directory(string $path): void
    {
        if ($path === '') {
            return;
        }

        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
    }
}

if (!function_exists('atenea_app_key_path')) {
    function atenea_app_key_path(): string
    {
        return atenea_storage_root() . DIRECTORY_SEPARATOR . 'app.key';
    }
}

if (!function_exists('atenea_normalize_app_key')) {
    function atenea_normalize_app_key(string $rawKey): string
    {
        $rawKey = trim($rawKey);
        if ($rawKey === '') {
            return '';
        }

        if (strpos($rawKey, 'base64:') === 0) {
            $decoded = base64_decode(substr($rawKey, 7), true);
            if ($decoded !== false && $decoded !== '') {
                return $decoded;
            }
        }

        $maybeDecoded = base64_decode($rawKey, true);
        if ($maybeDecoded !== false && strlen($maybeDecoded) >= 32) {
            return $maybeDecoded;
        }

        return $rawKey;
    }
}

if (!function_exists('atenea_app_key')) {
    function atenea_app_key(): string
    {
        static $cachedKey;

        if (is_string($cachedKey) && $cachedKey !== '') {
            return $cachedKey;
        }

        $envCandidates = [
            getenv('ATENEA_APP_KEY'),
            getenv('APP_KEY'),
        ];

        foreach ($envCandidates as $candidate) {
            if (!is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            $normalized = atenea_normalize_app_key($candidate);
            if ($normalized !== '') {
                $cachedKey = $normalized;

                return $cachedKey;
            }
        }

        $keyPath = atenea_app_key_path();
        if (is_file($keyPath)) {
            $storedKey = file_get_contents($keyPath);
            if (is_string($storedKey)) {
                $normalized = atenea_normalize_app_key($storedKey);
                if ($normalized !== '') {
                    $cachedKey = $normalized;

                    return $cachedKey;
                }
            }
        }

        $generatedKey = random_bytes(32);
        atenea_storage_ensure_directory(dirname($keyPath));
        file_put_contents($keyPath, 'base64:' . base64_encode($generatedKey), LOCK_EX);
        $cachedKey = $generatedKey;

        return $cachedKey;
    }
}

if (!function_exists('atenea_encrypt_secret')) {
    function atenea_encrypt_secret(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $key = hash('sha256', atenea_app_key(), true);
        $iv = random_bytes(16);
        $ciphertext = openssl_encrypt($value, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        if ($ciphertext === false) {
            throw new RuntimeException('No se pudo cifrar la informacion sensible.');
        }

        $hmac = hash_hmac('sha256', $iv . $ciphertext, $key, true);

        return base64_encode($iv . $hmac . $ciphertext);
    }
}

if (!function_exists('atenea_decrypt_secret')) {
    function atenea_decrypt_secret(string $payload): string
    {
        $payload = trim($payload);
        if ($payload === '') {
            return '';
        }

        $decoded = base64_decode($payload, true);
        if ($decoded === false || strlen($decoded) < 49) {
            return '';
        }

        $key = hash('sha256', atenea_app_key(), true);
        $iv = substr($decoded, 0, 16);
        $hmac = substr($decoded, 16, 32);
        $ciphertext = substr($decoded, 48);
        $calculatedHmac = hash_hmac('sha256', $iv . $ciphertext, $key, true);

        if (!hash_equals($hmac, $calculatedHmac)) {
            return '';
        }

        $plaintext = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        return $plaintext === false ? '' : $plaintext;
    }
}

if (!function_exists('atenea_csrf_session_key')) {
    function atenea_csrf_session_key(): string
    {
        return '_atenea_csrf_tokens';
    }
}

if (!function_exists('atenea_csrf_token')) {
    function atenea_csrf_token(string $formName): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $formName = trim($formName);
        if ($formName === '') {
            $formName = 'default';
        }

        $sessionKey = atenea_csrf_session_key();
        if (!isset($_SESSION[$sessionKey]) || !is_array($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = [];
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION[$sessionKey][$formName] = [
            'token' => $token,
            'expires_at' => time() + 7200,
        ];

        return $token;
    }
}

if (!function_exists('atenea_verify_csrf_token')) {
    function atenea_verify_csrf_token(string $formName, ?string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $formName = trim($formName);
        if ($formName === '') {
            $formName = 'default';
        }

        $sessionKey = atenea_csrf_session_key();
        $sessionValue = $_SESSION[$sessionKey][$formName] ?? null;

        if (!is_array($sessionValue)) {
            return false;
        }

        $expectedToken = (string) ($sessionValue['token'] ?? '');
        $expiresAt = (int) ($sessionValue['expires_at'] ?? 0);

        if ($expectedToken === '' || $token === null || $token === '') {
            return false;
        }

        if ($expiresAt > 0 && time() > $expiresAt) {
            unset($_SESSION[$sessionKey][$formName]);

            return false;
        }

        $isValid = hash_equals($expectedToken, $token);

        if ($isValid) {
            unset($_SESSION[$sessionKey][$formName]);
        }

        return $isValid;
    }
}

if (!function_exists('atenea_require_csrf_token')) {
    function atenea_require_csrf_token(string $formName, ?string $token): void
    {
        if (!atenea_verify_csrf_token($formName, $token)) {
            throw new RuntimeException('La validacion CSRF fallo. Recarga la pagina e intentalo nuevamente.');
        }
    }
}
