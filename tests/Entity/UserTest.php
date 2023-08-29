<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Factory\UserFactory;
use App\Tests\BaseApiTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserTest extends BaseApiTestCase
{
    use ResetDatabase;
    use Factories;

    public function testCreateUserSuccess(): void
    {
        static::createClient()->request('POST', '/users', ['json' => [
            'email' => 'test1@example.com',
            'plainPassword' => '1Testtest',
        ]]);

        $this->assertResponseStatusCodeSame(expectedCode: 201);

        $this->assertMatchesJsonSchema([
            'type' => 'object',
            'properties' => [
                '@context' => ['type' => 'string'],
                '@id' => ['type' => 'string'],
                '@type' => ['type' => 'string'],
                'id' => ['type' => 'string'],
                'email' => ['type' => 'string'],
            ],
            'required' => [
                '@context',
                '@id',
                '@type',
                'id',
                'email',
            ],
            'additionalProperties' => false,
        ]);

        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@type' => 'User',
            'email' => 'test1@example.com',
        ]);
    }

    public function testCreateUserFailureConstraintViolations(): void
    {
        UserFactory::createOne(['email' => 'test1@example.com']);

        $response = static::createClient()->request('POST', '/users', ['json' => [
            'email' => 'test1@example.com',
            'plainPassword' => '1Test',
        ]]);

        $this->assertResponseStatusCodeSame(expectedCode: 422);

        $data = $response->toArray(throw: false);

        /** @var array<int, array> $violations */
        $violations = $data['violations'];

        $this->assertCount(expectedCount: 2, haystack: $violations);

        // sort alphabetically, then assert
        usort($violations, fn ($a, $b) => $a['propertyPath'] <=> $b['propertyPath']);
        $this->assertArraySubset(subset: [
            [
                'propertyPath' => 'email',
                'message' => 'This value is already used.',
            ],
            [
                'propertyPath' => 'plainPassword',
                'message' => 'This value is too short. It should have 8 characters or more.',
            ],
        ], array: $violations);
    }

    public function testGetUserSuccess(): void
    {
        UserFactory::createOne(['email' => 'test1@example.com']);

        $userId = $this->login();

        $this->createAuthenticatedClient()->request('GET', "/users/{$userId}");

        $this->assertResponseStatusCodeSame(expectedCode: 200);
        $this->assertMatchesJsonSchema([
            'type' => 'object',
            'properties' => [
                '@context' => ['type' => 'string'],
                '@id' => ['type' => 'string'],
                '@type' => ['type' => 'string'],
                'id' => ['type' => 'string'],
                'email' => ['type' => 'string'],
            ],
            'required' => [
                '@context',
                '@id',
                '@type',
                'id',
                'email',
            ],
            'additionalProperties' => false,
        ]);

        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@id' => "/users/{$userId}",
            '@type' => 'User',
            'id' => $userId,
            'email' => 'test1@example.com',
        ]);
    }

    public function testGetUserFailureAccessDeniedToOtherUser(): void
    {
        UserFactory::createOne(['email' => 'test1@example.com']);
        UserFactory::createOne(['email' => 'test2@example.com']);

        $test1UserId = $this->login();
        $this->logout();

        $this->login(email: 'test2@example.com');

        $this->createAuthenticatedClient()->request('GET', "/users/{$test1UserId}");

        $this->assertResponseStatusCodeSame(expectedCode: 403);
    }
}
