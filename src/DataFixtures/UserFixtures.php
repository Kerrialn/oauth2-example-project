<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('auseremail@example.com');
        $user->setRoles(['ROLE_USER']);
        $password = $this->passwordHasher->hashPassword($user, '12345678');
        $user->setPassword($password);

        $manager->persist($user);
        $manager->flush();
    }
}
