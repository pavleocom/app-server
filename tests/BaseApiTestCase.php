<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;

abstract class BaseApiTestCase extends ApiTestCase
{
    private ?string $token = null;

    public function setUp(): void
    {
        parent::setUp();
    }

    protected function createAuthenticatedClient(): Client
    {
        if (null === $this->token) {
            throw new Exception('You must login before using this client. Use login method.');
        }

        return static::createClient([], ['headers' => ['authorization' => 'Bearer '.$this->token]]);
    }

    protected function login(string $email = 'test1@example.com', string $password = '1Password'): string
    {
        if ($this->token) {
            return $this->token;
        }

        $response = static::createClient()->request('POST', '/auth', ['json' => [
            'email' => $email,
            'password' => $password,
        ]]);

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->token = $data['token'];
        return $data['token'];
    }

    protected function logout(): void
    {
        $this->token = null;
    }
}