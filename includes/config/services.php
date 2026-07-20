<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/env.php';
require_once dirname(__DIR__) . '/config.php';

final class AppConfig
{
    private static ?array $localValues = null;

    private function __construct() {}

    public static function value(string $name, string $default = '', array $aliases = []): string
    {
        cargarEntornoAtenea();
        $names = array_values(array_unique(array_merge([$name], $aliases)));
        foreach ($names as $candidate) {
            $value = getenv($candidate);
            if ($value !== false && trim((string) $value) !== '') return trim((string) $value);
        }
        $local = self::localValues();
        foreach ($names as $candidate) {
            if (isset($local[$candidate]) && trim((string) $local[$candidate]) !== '') return trim((string) $local[$candidate]);
        }
        return $default;
    }

    public static function required(string $name, array $aliases = []): string
    {
        $value = self::value($name, '', $aliases);
        if ($value === '') throw new RuntimeException('La configuración requerida del servicio no está disponible.');
        return $value;
    }

    public static function isLocal(): bool
    {
        $environment = strtolower(self::value('APP_ENV', '', ['ATENEA_ENV']));
        if ($environment !== '') {
            return in_array($environment, ['dev', 'development', 'local'], true);
        }
        $host = explode(':', strtolower((string) ($_SERVER['HTTP_HOST'] ?? '')), 2)[0];
        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    private static function localValues(): array
    {
        if (self::$localValues !== null) return self::$localValues;
        $legacy = [];
        self::map($legacy, self::loadArray(ATENEA_ROOT . '/config/google.local.php'), [
            'GOOGLE_CLIENT_ID' => 'client_id',
            'GOOGLE_CLIENT_SECRET' => 'client_secret',
        ]);
        self::map($legacy, self::loadArray(dirname(__DIR__) . '/config/mail.php'), [
            'SMTP_HOST' => 'host', 'SMTP_PORT' => 'port', 'SMTP_USERNAME' => 'smtp_user',
            'SMTP_PASSWORD' => 'smtp_app_password', 'SMTP_ENCRYPTION' => 'encryption',
            'SMTP_FROM_EMAIL' => 'from_email', 'SMTP_FROM_NAME' => 'from_name',
            'CONTACT_RECIPIENT' => 'recipient', 'RECAPTCHA_SITE_KEY' => 'recaptcha_site_key',
            'RECAPTCHA_SECRET_KEY' => 'recaptcha_secret_key',
        ]);
        self::$localValues = array_replace($legacy, self::loadArray(__DIR__ . '/services.local.php'));
        return self::$localValues;
    }

    private static function loadArray(string $file): array
    {
        if (!is_file($file) || !is_readable($file)) return [];
        $values = require $file;
        return is_array($values) ? $values : [];
    }

    private static function map(array &$target, array $source, array $mapping): void
    {
        foreach ($mapping as $canonical => $legacy) {
            if (array_key_exists($legacy, $source)) $target[$canonical] = $source[$legacy];
        }
    }
}

final class GoogleConfig
{
    private const CALLBACK_PATH = 'src/auth/google-callback.php';

    private function __construct() {}
    public static function clientId(): string { return AppConfig::value('GOOGLE_CLIENT_ID'); }
    public static function clientSecret(): string { return AppConfig::value('GOOGLE_CLIENT_SECRET'); }
    public static function appUrl(): string
    {
        return atenea_app_url_configurada();
    }
    public static function environment(): string
    {
        return AppConfig::value('APP_ENV', 'production', ['ATENEA_ENV']);
    }
    public static function redirectUri(): string
    {
        $base = self::appUrl();
        return $base === '' ? '' : $base . '/' . self::CALLBACK_PATH;
    }
    public static function callbackPath(): string { return self::CALLBACK_PATH; }
    public static function javascriptOrigin(): string
    {
        $parts = parse_url(self::appUrl());
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) return '';
        return strtolower((string) $parts['scheme']) . '://' . (string) $parts['host']
            . (isset($parts['port']) ? ':' . (int) $parts['port'] : '');
    }
    public static function authorizationUri(): string { return AppConfig::value('GOOGLE_AUTH_URI', 'https://accounts.google.com/o/oauth2/v2/auth'); }
    public static function tokenUri(): string { return AppConfig::value('GOOGLE_TOKEN_URI', 'https://oauth2.googleapis.com/token'); }
    public static function tokenInfoUri(): string { return AppConfig::value('GOOGLE_TOKENINFO_URI', 'https://oauth2.googleapis.com/tokeninfo'); }
    public static function userInfoUri(): string { return AppConfig::value('GOOGLE_USERINFO_URI', 'https://www.googleapis.com/oauth2/v2/userinfo'); }
    public static function scopes(): string { return AppConfig::value('GOOGLE_SCOPES', 'openid email profile'); }
    public static function isConfigured(): bool { return self::missing() === []; }

    public static function missing(): array
    {
        $missing = [];
        if (self::clientId() === '') $missing[] = 'GOOGLE_CLIENT_ID';
        if (self::clientSecret() === '') $missing[] = 'GOOGLE_CLIENT_SECRET';
        if (!self::validBaseUrl(self::appUrl())) {
            $local = in_array(strtolower(self::environment()), ['dev', 'development', 'local'], true);
            $missing[] = $local ? 'APP_URL_LOCAL (o APP_URL)' : 'APP_URL_PRODUCTION (o APP_URL)';
        }
        if (strtolower(self::environment()) === 'production'
            && strtolower((string) parse_url(self::appUrl(), PHP_URL_SCHEME)) !== 'https') {
            $missing[] = 'APP_URL_PRODUCTION debe usar HTTPS';
        }
        return $missing;
    }

    public static function toArray(): array
    {
        return ['client_id' => self::clientId(), 'client_secret' => self::clientSecret(),
            'redirect_uri' => self::redirectUri(), 'authorization_uri' => self::authorizationUri(),
            'token_uri' => self::tokenUri(), 'tokeninfo_uri' => self::tokenInfoUri(),
            'userinfo_uri' => self::userInfoUri(), 'scopes' => self::scopes(),
            'app_url' => self::appUrl(), 'app_env' => self::environment(),
            'callback_path' => self::callbackPath(), 'javascript_origin' => self::javascriptOrigin()];
    }

    private static function validBaseUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) return false;
        $parts = parse_url($url);
        return is_array($parts)
            && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
            && !empty($parts['host'])
            && !isset($parts['query'], $parts['fragment'], $parts['user'], $parts['pass']);
    }
}

final class RecaptchaConfig
{
    private function __construct() {}
    public static function siteKey(): string { return AppConfig::value('RECAPTCHA_SITE_KEY'); }
    public static function secretKey(): string { return AppConfig::value('RECAPTCHA_SECRET_KEY'); }
    public static function verifyUri(): string { return AppConfig::value('RECAPTCHA_VERIFY_URI', 'https://www.google.com/recaptcha/api/siteverify'); }
    public static function isConfigured(): bool { return self::siteKey() !== '' && self::secretKey() !== ''; }
    public static function missing(): array
    {
        $missing = [];
        if (self::siteKey() === '') $missing[] = 'RECAPTCHA_SITE_KEY';
        if (self::secretKey() === '') $missing[] = 'RECAPTCHA_SECRET_KEY';
        return $missing;
    }
}

final class StripeConfig
{
    private function __construct() {}
    public static function publicKey(): string { return AppConfig::value('STRIPE_PUBLIC_KEY', '', ['STRIPE_PUBLISHABLE_KEY']); }
    public static function secretKey(): string { return AppConfig::value('STRIPE_SECRET_KEY'); }
    public static function webhookSecret(): string { return AppConfig::value('STRIPE_WEBHOOK_SECRET'); }
    public static function currency(): string
    {
        $currency = strtolower(AppConfig::value('STRIPE_CURRENCY', 'usd'));
        return preg_match('/^[a-z]{3}$/', $currency) === 1 ? $currency : '';
    }
    public static function isConfigured(): bool
    {
        return str_starts_with(self::publicKey(), 'pk_') && str_starts_with(self::secretKey(), 'sk_')
            && str_starts_with(self::webhookSecret(), 'whsec_') && self::currency() !== '';
    }
    public static function missing(): array
    {
        $missing = [];
        if (!str_starts_with(self::publicKey(), 'pk_')) $missing[] = 'STRIPE_PUBLIC_KEY';
        if (!str_starts_with(self::secretKey(), 'sk_')) $missing[] = 'STRIPE_SECRET_KEY';
        if (!str_starts_with(self::webhookSecret(), 'whsec_')) $missing[] = 'STRIPE_WEBHOOK_SECRET';
        if (self::currency() === '') $missing[] = 'STRIPE_CURRENCY';
        return $missing;
    }
    public static function toArray(): array
    {
        return ['publishable_key' => self::publicKey(), 'secret_key' => self::secretKey(),
            'webhook_secret' => self::webhookSecret(), 'currency' => self::currency()];
    }
}

final class MailConfig
{
    private function __construct() {}
    public static function host(): string { return AppConfig::value('SMTP_HOST', 'smtp.gmail.com'); }
    public static function port(): int
    {
        $port = filter_var(AppConfig::value('SMTP_PORT', '587'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
        return $port === false ? 0 : (int) $port;
    }
    public static function username(): string { return AppConfig::value('SMTP_USERNAME', '', ['GMAIL_SMTP_USER']); }
    public static function password(): string { return AppConfig::value('SMTP_PASSWORD', '', ['GMAIL_SMTP_APP_PASSWORD']); }
    public static function encryption(): string
    {
        return match (strtolower(AppConfig::value('SMTP_ENCRYPTION', 'tls'))) {
            'tls', 'starttls' => 'tls', 'ssl', 'smtps' => 'ssl', 'none', '' => 'none', default => '',
        };
    }
    public static function fromEmail(): string { return AppConfig::value('SMTP_FROM_EMAIL', self::username()); }
    public static function fromName(): string { return AppConfig::value('SMTP_FROM_NAME', 'Atenea'); }
    public static function contactRecipient(): string { return AppConfig::value('CONTACT_RECIPIENT', self::fromEmail()); }
    public static function isConfigured(): bool { return self::missing() === []; }
    public static function isContactConfigured(): bool
    {
        return self::isConfigured() && filter_var(self::contactRecipient(), FILTER_VALIDATE_EMAIL) !== false
            && RecaptchaConfig::isConfigured();
    }
    public static function missing(): array
    {
        $missing = [];
        if (self::host() === '') $missing[] = 'SMTP_HOST';
        if (self::port() === 0) $missing[] = 'SMTP_PORT';
        if (self::username() === '') $missing[] = 'SMTP_USERNAME';
        if (self::password() === '') $missing[] = 'SMTP_PASSWORD';
        if (self::encryption() === '') $missing[] = 'SMTP_ENCRYPTION';
        if (filter_var(self::fromEmail(), FILTER_VALIDATE_EMAIL) === false) $missing[] = 'SMTP_FROM_EMAIL';
        if (self::fromName() === '') $missing[] = 'SMTP_FROM_NAME';
        return $missing;
    }
    public static function toArray(): array
    {
        return ['host' => self::host(), 'port' => self::port(), 'encryption' => self::encryption(),
            'smtp_user' => self::username(), 'smtp_app_password' => self::password(),
            'from_email' => self::fromEmail(), 'from_name' => self::fromName(),
            'recipient' => self::contactRecipient(), 'recaptcha_site_key' => RecaptchaConfig::siteKey(),
            'recaptcha_secret_key' => RecaptchaConfig::secretKey(), 'recaptcha_verify_uri' => RecaptchaConfig::verifyUri()];
    }
}

final class ImapConfig
{
    private function __construct() {}
    public static function host(): string { return AppConfig::value('IMAP_HOST'); }
    public static function port(): int
    {
        $port = filter_var(AppConfig::value('IMAP_PORT', '993'), FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>65535]]);
        return $port === false ? 0 : (int)$port;
    }
    public static function encryption(): string
    {
        return match(strtolower(AppConfig::value('IMAP_ENCRYPTION','ssl'))) {
            'ssl','imaps' => 'ssl', 'tls','starttls' => 'tls', 'none','' => 'none', default => '',
        };
    }
    public static function username(): string { return AppConfig::value('IMAP_USERNAME'); }
    public static function password(): string { return AppConfig::value('IMAP_PASSWORD'); }
    public static function folder(): string
    {
        $folder = AppConfig::value('IMAP_FOLDER','INBOX');
        return preg_match('/^[A-Za-z0-9 _.-]{1,120}$/',$folder) ? $folder : '';
    }
    public static function extensionAvailable(): bool { return extension_loaded('imap') && function_exists('imap_open'); }
    public static function missing(): array
    {
        $missing=[];
        if(self::host()==='')$missing[]='IMAP_HOST';
        if(self::port()===0)$missing[]='IMAP_PORT';
        if(self::encryption()==='')$missing[]='IMAP_ENCRYPTION';
        if(self::username()==='')$missing[]='IMAP_USERNAME';
        if(self::password()==='')$missing[]='IMAP_PASSWORD';
        if(self::folder()==='')$missing[]='IMAP_FOLDER';
        return $missing;
    }
    public static function isConfigured(): bool { return self::missing()===[]; }
    public static function isAvailable(): bool { return self::extensionAvailable() && self::isConfigured(); }
    public static function toArray(): array
    {
        return ['host'=>self::host(),'port'=>self::port(),'encryption'=>self::encryption(),'user'=>self::username(),'password'=>self::password(),'folder'=>self::folder()];
    }
}
