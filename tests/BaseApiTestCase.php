<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Exception;

abstract class BaseApiTestCase extends ApiTestCase
{
    private ?string $token = null;
    private ?string $userId = null;

    public function setUp(): void
    {
        parent::setUp();
    }

    protected function createAuthenticatedClient(): Client
    {
        if (null === $this->token || null === $this->userId) {
            throw new Exception('You must login before using this client. Use login method.');
        }

        return static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]]);
    }

    protected function login(string $email = 'test1@example.com', string $password = '1Password'): string
    {
        if ($this->token && $this->userId) {
            return $this->userId;
        }

        $response = static::createClient()->request('POST', '/auth', ['json' => [
            'email' => $email,
            'password' => $password,
        ]]);

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->token = $data['token'];
        $this->userId = $data['userId'];
        return $data['userId'];
    }

    protected function logout(): void
    {
        $this->token = null;
        $this->userId = null;
    }
}