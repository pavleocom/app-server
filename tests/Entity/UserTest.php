<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class UserTest extends ApiTestCase
{
    public function testCreateUserSuccess(): void
    {
        $response = static::createClient()->request('POST', '/users', ['json' => [
            'email' => 'test1@example.com',
            'plainPassword' => '1Testtest',
        ]]);

        $this->assertResponseStatusCodeSame(expectedCode: 201);
    }
}
