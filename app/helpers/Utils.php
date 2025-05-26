<?php

namespace jetstore\app\helpers;

use Exception;
use jetstore\app\models\Log;
use jetstore\app\models\User;
use jetstore\Environment;

class Utils
{
    /**
     * returns string with translatable textdomain
     *
     * @param string $data
     * @return string
     */
    public static function withTranslation(string $data): string
    {
        return esc_html__($data,Environment::TextDomain);
    }

    public static function jwtEncode(array $payload,string $key): string
    {
        return base64_encode(json_encode($payload)).'.'.hash_hmac('sha256',json_encode($payload),$key);
    }

    public static function jwtDecode(string $token, string $key)
    {
        list($payload, $signature) = explode('.', $token);
        $valid_signature = hash_hmac('sha256',base64_decode($payload),$key);

        if ($valid_signature !== $signature)
            return false;

        return json_decode(base64_decode($payload));
    }

    public static function validateToken(string $token): bool
    {
        $payload = self::jwtDecode($token,AUTH_KEY);

        if (!$payload)
            return false;

        $user = User::find($payload->id);

        if (!$user)
            return false;

        return true;
    }

    public static function sendSms(string $phoneNumber, int $messageId, array $args): bool
    {
        try {
            $data = [
                'username'  => '09129364742',
                'password'  => 'F$D#R',
                'text'      => implode(';', $args),
                'to'        => $phoneNumber,
                'bodyId'    => $messageId
            ];

            $postData = http_build_query($data);
            $handle = curl_init('https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber');

            curl_setopt($handle, CURLOPT_HTTPHEADER,
                [
                    'content-type' => 'application/x-www-form-urlencoded'
                ]
            );

            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($handle, CURLOPT_POST, true);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $postData);

            $response = curl_exec($handle);
            curl_close($handle);

            return (bool)$response;

        }catch (Exception $exception)
        {
            curl_close($handle);

            Log::create(
                [
                    'user_id'   => null,
                    'message'   => $exception->getMessage(),
                    'ip'        => Utils::getUserIpAddress(),
                    'type'      => 'Message send curl error to : '.$phoneNumber.' , message code : '.$messageId,
                    'file'      => 'Utils.php',
                ]
            );

            return false;
        }
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
        // If using Cloudflare and trust its IPs
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP']))
            return sanitize_text_field($_SERVER['HTTP_CF_CONNECTING_IP']);

        // Use X-Forwarded-For safely if you're behind a trusted proxy
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return sanitize_text_field(trim(end($ip_list)));
        }

        // Fallback: Direct IP
        return sanitize_text_field($_SERVER['REMOTE_ADDR']);
    }
}