<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Factory\UserFactory;
use App\Tests\BaseApiTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserTest extends BaseApiTestCase
{
    use ResetDatabase, Factories;

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
                'email' => ['type' => 'string']
            ],
            'required' => [
                '@context',
                '@id',
                '@type',
                'id',
                'email'
            ]
        ]);
    }

    public function testCreateUserFailureConstraintViolations(): void
    {
        UserFactory::createOne(['email' => 'test1@example.com']);

        static::createClient()->request('POST', '/users', ['json' => [
            'email' => 'test1@example.com',
            'plainPassword' => '1Test',
        ]]);

        $this->assertResponseStatusCodeSame(expectedCode: 422);
        $this->assertJsonContains(['violations' => [
            [
                'propertyPath' => 'email',
                'message' => 'This value is already used.',
            ],
        ]]);
    }

    public function testGetUserSuccess(): void
    {
        UserFactory::createOne(['email' => 'test1@example.com']);

        static::createClient()->request('POST', '/users', ['json' => [
            'email' => 'test1@example.com',
            'plainPassword' => '1Test',
        ]]);

        $this->assertResponseStatusCodeSame(expectedCode: 422);
        $this->assertJsonContains(['violations' => [
            [
                'propertyPath' => 'email',
                'message' => 'This value is already used.',
            ],
        ]]);
    }

}
