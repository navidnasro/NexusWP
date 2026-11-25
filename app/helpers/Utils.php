<?php

namespace nexuswp\app\helpers;

use nexuswp\app\models\User;
use nexuswp\core\Config;
use nexuswp\core\support\Jwt;
use nexuswp\Environment;

/**
 * Small plugin-side helpers. Framework-level helpers live in nexuswp\core.
 */
class Utils
{
    /**
     * returns a string translated with the plugin text domain
     */
    public static function withTranslation(string $data): string
    {
        return esc_html__($data, Environment::TextDomain);
    }

    public static function jwtEncode(array $payload, string $key): string
    {
        return Jwt::encode($payload, $key);
    }

    public static function jwtDecode(string $token, string $key): object|false
    {
        return Jwt::decode($token, $key);
    }

    public static function validateToken(string $token): bool
    {
        $payload = self::jwtDecode($token, Config::authKey());

        if (!$payload || empty($payload->user_id))
            return false;

        return User::find((int)$payload->user_id) !== null;
    }

    public static function convertToEnglishDigits(string $input): array|string
    {
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $arabic  = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        $english = ['0','1','2','3','4','5','6','7','8','9'];

        return str_replace($persian, $english, str_replace($arabic, $english, $input));
    }

    public static function getUserIpAddress(): string
    {
        $remoteAddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

        // proxy headers are only trusted when the request comes from a known proxy
        if (self::isTrustedProxy($remoteAddress))
        {
            if (!empty($_SERVER['HTTP_CF_CONNECTING_IP']))
                return sanitize_text_field($_SERVER['HTTP_CF_CONNECTING_IP']);

            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                return sanitize_text_field(trim($ip_list[0]));
            }
        }

        return sanitize_text_field($remoteAddress);
    }

    /**
     * checks the configured proxy allow list (define NEXUSWP_TRUSTED_PROXIES in wp-config.php)
     */
    private static function isTrustedProxy(string $ip): bool
    {
        if ($ip === '' || !defined('NEXUSWP_TRUSTED_PROXIES'))
            return false;

        $proxies = (array)NEXUSWP_TRUSTED_PROXIES;

        return in_array($ip, $proxies, true);
    }
}
