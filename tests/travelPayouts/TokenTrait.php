<?php

declare(strict_types=1);

namespace Tests\TravelPayouts;

use Exception;

trait TokenTrait
{
    /** @throws Exception */
    protected static function getToken(): string
    {
        $tokenPath = __DIR__ . '/../../.test.token';
        $token = file_get_contents($tokenPath);
        if (!$token) {
            throw new Exception('Please create the `.test.token` file in the root directory of the project');
        }
        return $token;
    }
}
