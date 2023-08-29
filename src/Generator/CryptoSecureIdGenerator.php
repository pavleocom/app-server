<?php

declare(strict_types=1);

namespace App\Generator;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;
use function bin2hex;
use function random_bytes;

class CryptoSecureIdGenerator extends AbstractIdGenerator
{
    public function generateId(EntityManagerInterface $em, $entity)
    {
        return bin2hex(random_bytes(16));
    }
}