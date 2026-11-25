<?php

namespace nexuswp\app\controllers\api;

use nexuswp\app\helpers\Utils;
use nexuswp\Environment;
use WP_REST_Request;
use WP_REST_Response;

class TokenController
{
    /**
     * Generate a token for the user
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function generateToken(WP_REST_Request $request): WP_REST_Response
    {
        $username = sanitize_text_field($request->get_param('username'));
        $password = (string)$request->get_param('password');

        // simple throttle to slow down credential stuffing
        $attemptKey = 'nexuswp_login_attempts_'.md5($username.Utils::getUserIpAddress());
        $attempts = (int)get_transient($attemptKey);

        if ($attempts >= 5)
        {
            return new WP_REST_Response(
                ['data' => ['message' => 'Too many attempts, please try again later.']],
                429
            );
        }

        $user = wp_authenticate($username, $password);

        if (is_wp_error($user))
        {
            set_transient($attemptKey, $attempts + 1, 5 * MINUTE_IN_SECONDS);

            return new WP_REST_Response(
                ['data' => ['message' => 'Invalid credentials.']],
                401
            );
        }

        delete_transient($attemptKey);

        $payload = [
            'user_id' => $user->ID,
            'iat'     => time(),
            'exp'     => time() + Environment::TokenTtl,
        ];

        $token = Utils::jwtEncode($payload, AUTH_KEY);

        return new WP_REST_Response(
            [
                'data' => [
                    'token' => $token,
                ],
            ],
            200
        );
    }
}
