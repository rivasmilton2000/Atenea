<?php

class DteStorage
{
    public const SUBDIRECTORIES = [
        'certificates' => 'certificates',
        'json' => 'json',
        'pdf' => 'pdf',
        'responses' => 'responses',
        'mock' => 'mock',
    ];

    public static function projectRoot(): string
    {
        return dirname(__DIR__, 2);
    }

    public static function root(): string
    {
        return self::projectRoot() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'dte';
    }

    public static function ensureDirectories(): void
    {
        atenea_storage_ensure_directory(atenea_storage_root());
        atenea_storage_ensure_directory(self::root());

        foreach (self::SUBDIRECTORIES as $directory) {
            atenea_storage_ensure_directory(self::directory($directory));
        }
    }

    public static function directory(string $name): string
    {
        $key = trim($name);
        if (!isset(self::SUBDIRECTORIES[$key])) {
            throw new InvalidArgumentException('Directorio DTE no soportado: ' . $key);
        }

        return self::root() . DIRECTORY_SEPARATOR . self::SUBDIRECTORIES[$key];
    }

    public static function mockPath(string $fileName): string
    {
        return self::directory('mock') . DIRECTORY_SEPARATOR . ltrim($fileName, '\\/');
    }

    public static function buildFilePaths(string $directory, string $fileName): array
    {
        self::ensureDirectories();

        $cleanName = preg_replace('/[^A-Za-z0-9_.-]/', '_', $fileName) ?: 'documento.dat';
        $absolutePath = self::directory($directory) . DIRECTORY_SEPARATOR . $cleanName;

        return [
            'absolute_path' => $absolutePath,
            'relative_path' => self::relativePath($absolutePath),
            'file_name' => $cleanName,
        ];
    }

    public static function writeJson(string $directory, string $fileName, array $payload): array
    {
        $paths = self::buildFilePaths($directory, $fileName);
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            throw new RuntimeException('No se pudo serializar el JSON DTE.');
        }

        if (file_put_contents($paths['absolute_path'], $json, LOCK_EX) === false) {
            throw new RuntimeException('No se pudo escribir el archivo JSON DTE.');
        }

        return $paths;
    }

    public static function writeContents(string $directory, string $fileName, string $contents): array
    {
        $paths = self::buildFilePaths($directory, $fileName);

        if (file_put_contents($paths['absolute_path'], $contents, LOCK_EX) === false) {
            throw new RuntimeException('No se pudo escribir el archivo DTE solicitado.');
        }

        return $paths;
    }

    public static function relativePath(string $absolutePath): string
    {
        $normalizedAbsolute = str_replace('\\', '/', $absolutePath);
        $normalizedRoot = str_replace('\\', '/', self::projectRoot());

        if (strpos($normalizedAbsolute, $normalizedRoot) === 0) {
            return ltrim(substr($normalizedAbsolute, strlen($normalizedRoot)), '/');
        }

        return ltrim($normalizedAbsolute, '/');
    }

    public static function resolveStoredFile(string $relativePath, array $allowedRoots = []): array
    {
        $relativePath = ltrim(str_replace('\\', '/', trim($relativePath)), '/');
        if ($relativePath === '') {
            return [
                'relative_path' => '',
                'absolute_path' => '',
                'exists' => false,
            ];
        }

        $absolutePath = realpath(self::projectRoot() . DIRECTORY_SEPARATOR . $relativePath);
        if ($absolutePath === false || !is_file($absolutePath)) {
            return [
                'relative_path' => $relativePath,
                'absolute_path' => '',
                'exists' => false,
            ];
        }

        $allowed = [];
        foreach ($allowedRoots as $rootPath) {
            $resolved = realpath($rootPath);
            if ($resolved !== false) {
                $allowed[] = str_replace('\\', '/', $resolved);
            }
        }

        if ($allowed === []) {
            $allowed[] = str_replace('\\', '/', self::root());
            $uploadsFacturas = realpath(self::projectRoot() . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'facturas');
            if ($uploadsFacturas !== false) {
                $allowed[] = str_replace('\\', '/', $uploadsFacturas);
            }
        }

        $normalizedAbsolute = str_replace('\\', '/', $absolutePath);
        $isAllowed = false;
        foreach ($allowed as $root) {
            if (strpos($normalizedAbsolute, $root) === 0) {
                $isAllowed = true;
                break;
            }
        }

        return [
            'relative_path' => $relativePath,
            'absolute_path' => $isAllowed ? $absolutePath : '',
            'exists' => $isAllowed,
        ];
    }

    public static function deleteIfExists(?string $absolutePath): void
    {
        if (!is_string($absolutePath) || trim($absolutePath) === '') {
            return;
        }

        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    public static function storeUploadedCertificate(array $file): ?array
    {
        self::ensureDirectories();

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No se pudo subir el certificado DTE.');
        }

        $originalName = (string) ($file['name'] ?? 'certificado.bin');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['p12', 'pfx', 'pem', 'crt', 'cer', 'key', 'json'];

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new RuntimeException('El certificado DTE debe ser un archivo .p12, .pfx, .pem, .crt, .cer, .key o .json.');
        }

        $fileName = 'certificado_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $paths = self::buildFilePaths('certificates', $fileName);

        if (!move_uploaded_file((string) ($file['tmp_name'] ?? ''), $paths['absolute_path'])) {
            throw new RuntimeException('No se pudo mover el certificado DTE al storage seguro.');
        }

        $paths['original_name'] = $originalName;

        return $paths;
    }
}
