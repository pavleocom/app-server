<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\PasswordReset;
use App\Factory\UserFactory;
use App\Tests\BaseApiTestCase;
use Carbon\Carbon;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class PasswordResetTest extends BaseApiTestCase
{
    use ResetDatabase;
    use Factories;

    public function testCreatePasswordResetSuccess(): void
    {
        UserFactory::createOne(['email' => 'test1@example.com']);

        $response = static::createClient()->request('POST', '/password-resets', ['json' => ['email' => 'test1@example.com']]);
        $this->assertResponseStatusCodeSame(expectedCode: 204);
        $this->assertEmpty($response->getContent());

        $em = $this->getEntityManager(PasswordReset::class);
        $passwordResetCollection = $em->getRepository(PasswordReset::class)->findAll();
        $this->assertCount(expectedCount: 1, haystack: $passwordResetCollection);

        /** @var PasswordReset $passwordReset */
        $passwordReset = $passwordResetCollection[0];
        $expiresAt = Carbon::createFromImmutable($passwordReset->expiresAt);
        $diff = $expiresAt->diff(Carbon::now());

        $this->assertSame(expected: 'test1@example.com', actual: $passwordReset->user->email);
        $this->assertTrue($expiresAt->isFuture());
        $this->assertTrue(23 <= $diff->h); // expires in 23h or more from now
        $this->assertTrue(24 >= $diff->h); // expires in 24h or less from now
    }
}
