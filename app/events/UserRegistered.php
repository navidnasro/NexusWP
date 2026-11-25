<?php

namespace nexuswp\app\events;

use nexuswp\app\models\Log;
use nexuswp\core\events\Event;

/**
 * Fired once a new user is persisted.
 *
 * Trigger it from a controller:
 *   EventListener::trigger(UserRegistered::NAME, $user->id);
 */
class UserRegistered extends Event
{
    public const NAME = 'user.registered';

    public function handle(...$args): void
    {
        [$userId] = $args;

        Log::create([
            'user_id' => (int)$userId,
            'message' => 'User registered',
            'ip'      => '',
            'type'    => 'event',
            'file'    => 'UserRegistered.php',
        ]);
    }
}
