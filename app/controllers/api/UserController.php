<?php

namespace nexuswp\app\controllers\api;

use nexuswp\app\models\User;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Example of a protected REST controller.
 *
 * Routes are declared in routes/api.php as [UserController::class, 'method'],
 * so this file is autoloaded only for requests that actually hit /users.
 */
class UserController
{
    public function index(WP_REST_Request $request): WP_REST_Response
    {
        $limit = (int)$request->get_param('limit');
        $limit = max(1, min($limit ?: 25, 100));

        $users = User::latest()->limit($limit)->get() ?: [];

        return new WP_REST_Response(
            ['data' => array_map(static fn(User $user): array => $user->toArray(), $users)],
            200
        );
    }

    public function show(WP_REST_Request $request): WP_REST_Response
    {
        $user = User::find((int)$request->get_param('id'));

        if (!$user)
            return new WP_REST_Response(['data' => ['message' => 'User not found.']], 404);

        return new WP_REST_Response(['data' => $user->toArray()], 200);
    }
}
