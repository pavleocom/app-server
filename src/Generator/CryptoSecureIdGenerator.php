<?php

declare(strict_types=1);

namespace App\Generator;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;

class CryptoSecureIdGenerator extends AbstractIdGenerator
{
    public function generateId(EntityManagerInterface $em, $entity)
    {
        return \bin2hex(\random_bytes(16));
    }
}
