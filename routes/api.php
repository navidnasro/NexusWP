<?php

use nexuswp\app\controllers\api\TokenController;
use nexuswp\app\controllers\api\UserController;
use nexuswp\core\route\Api;

// Api::register(verb, path, handler, requiresToken, capability)
//
// A handler is a [class-string, method] pair, so the controller is autoloaded
// on rest_api_init and only for REST requests.

// public: exchanges credentials for a bearer token
Api::register('POST', '/auth/token', [TokenController::class, 'generateToken'], false, 'read');

// protected: bearer token plus an explicit capability
Api::register('GET', '/users', [UserController::class, 'index'], true, 'list_users');
Api::register('GET', '/users/(?P<id>\d+)', [UserController::class, 'show'], true, 'list_users');
