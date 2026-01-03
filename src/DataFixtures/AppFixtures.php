<?php

namespace App\DataFixtures;

use App\Entity\Household;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Crear una llar
        $household = new Household();
        $household->setName('Pis Compartit Barcelona');
        $manager->persist($household);

        // Crear un administrador
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setHousehold($household);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'admin123')
        );
        $manager->persist($admin);

        // Crear un usuari normal
        $user = new User();
        $user->setEmail('user@test.com');
        $user->setRoles(['ROLE_USER']);
        $user->setHousehold($household);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, 'user123')
        );
        $manager->persist($user);

        // Crear tasques d'exemple
        $tasques = [
            'Treure la brossa',
            'Netejar la cuina',
            'Passar l\'aspiradora',
            'Rentar els plats',
            'Comprar pa'
        ];

        foreach ($tasques as $titol) {
            $task = new Task();
            $task->setTitle($titol);
            $task->setDescription('Descripció de: ' . $titol);
            $task->setCompleted(false);
            $task->setCreatedAt(new \DateTime());
            $task->setHousehold($household);
            $task->setAssignedTo($user);
            $manager->persist($task);
        }

        // Guardar tot a la base de dades
        $manager->flush();
    }
}
