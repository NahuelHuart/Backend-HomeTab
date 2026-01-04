<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Expense;
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
        // 1️⃣ Crear llars
        $householdNames = [
            'Casa Home',
            'Pis Compartit a Girona',
            'Casa a Puigcerdà',
            'Club de Lectura'
        ];

        $households = [];

        foreach ($householdNames as $name) {
            $household = new Household();
            $household->setName($name);
            $manager->persist($household);

            $households[] = $household;
        }

        // Per exemple, agafem la primera household per assignar els usuaris i tasques
        $mainHousehold = $households[0];

        // 2️⃣ Crear usuaris
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setFirstName('Maria');
        $admin->setLastName('García');
        $admin->setPhoneNumber('+34 666 777 888');
        $admin->setBio('Administradora del pis');
        $admin->setHousehold($mainHousehold);
        $admin->setJoinedAt((new \DateTime())->modify('-6 months'));
        $admin->setIsActive(true);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'admin123')
        );
        $manager->persist($admin);

        $user1 = new User();
        $user1->setEmail('user@test.com');
        $user1->setRoles(['ROLE_USER']);
        $user1->setFirstName('Joan');
        $user1->setLastName('Martínez');
        $user1->setPhoneNumber('+34 677 888 999');
        $user1->setBio('Estudiant d\'enginyeria');
        $user1->setHousehold($mainHousehold);
        $user1->setJoinedAt((new \DateTime())->modify('-3 months'));
        $user1->setIsActive(true);
        $user1->setPassword(
            $this->passwordHasher->hashPassword($user1, 'user123')
        );
        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail('anna@test.com');
        $user2->setRoles(['ROLE_USER']);
        $user2->setFirstName('Anna');
        $user2->setLastName('López');
        $user2->setPhoneNumber('+34 688 999 000');
        $user2->setBio('Dissenyadora gràfica');
        $user2->setHousehold($mainHousehold);
        $user2->setJoinedAt((new \DateTime())->modify('-4 months'));
        $user2->setIsActive(true);
        $user2->setPassword(
            $this->passwordHasher->hashPassword($user2, 'anna123')
        );
        $manager->persist($user2);

        // 3️⃣ Crear tasques
        $tasques = [
            ['Treure la brossa', 'Cuina', 'Mitja'],
            ['Netejar la cuina', 'Cuina', 'Alta'],
            ['Passar l\'aspiradora', 'Sala', 'Baixa'],
            ['Rentar els plats', 'Cuina', 'Alta'],
            ['Comprar pa', 'Altres', 'Mitja'],
            ['Treure el gos', 'Animals', 'Baixa'],
            ['Donar de menjar al gat', 'Animals', 'Mitja']
        ];

        foreach ($tasques as [$titol, $categoria, $prioritat]) {
            $task = new Task();
            $task->setTitle($titol);
            $task->setDescription('Descripció de: ' . $titol);
            $task->setCompleted(false);
            $task->setCreatedAt(new \DateTime());
            $task->setDueDate(new \DateTime('+3 days'));
            $task->setPriority($prioritat);
            $task->setCategory($categoria);
            $task->setHousehold($mainHousehold);
            $task->setAssignedTo($user1);
            $manager->persist($task);
        }

        // 4️⃣ Crear events
        $event1 = new Event();
        $event1->setTitle('Sopar de pis');
        $event1->setDescription('Sopar mensual per parlar de temes de la llar');
        $event1->setStartDate(new \DateTime('+7 days 20:00'));
        $event1->setEndDate(new \DateTime('+7 days 23:00'));
        $event1->setLocation('Casa');
        $event1->setIsAllDay(false);
        $event1->setColor('#FF5733');
        $event1->setHousehold($mainHousehold);
        $event1->setCreatedBy($admin);
        $event1->addParticipant($admin);
        $event1->addParticipant($user1);
        $event1->addParticipant($user2);
        $manager->persist($event1);

        $event2 = new Event();
        $event2->setTitle('Neteja general');
        $event2->setDescription('Neteja profunda de tot el pis');
        $event2->setStartDate(new \DateTime('next saturday 10:00'));
        $event2->setEndDate(new \DateTime('next saturday 14:00'));
        $event2->setLocation('Tot el pis');
        $event2->setIsAllDay(false);
        $event2->setColor('#33FF57');
        $event2->setHousehold($mainHousehold);
        $event2->setCreatedBy($user1);
        $event2->addParticipant($admin);
        $event2->addParticipant($user1);
        $event2->addParticipant($user2);
        $manager->persist($event2);

        // 5️⃣ Crear expenses
        $expense1 = new Expense();
        $expense1->setTitle('Lloger');
        $expense1->setDescription('Lloger de gener');
        $expense1->setAmount('900.00');
        $expense1->setCategory('Lloger');
        $expense1->setPaidBy($admin);
        $expense1->setPaidAt(new \DateTime('-5 days'));
        $expense1->setHousehold($mainHousehold);
        $expense1->addSplitBetween($admin);
        $expense1->addSplitBetween($user1);
        $expense1->addSplitBetween($user2);
        $expense1->setIsPaid(true);
        $expense1->setNotes('Pagat el dia 1 del mes');
        $manager->persist($expense1);

        $expense2 = new Expense();
        $expense2->setTitle('Factura');
        $expense2->setDescription('Factura de la llum');
        $expense2->setAmount('75.50');
        $expense2->setCategory('Factures');
        $expense2->setPaidBy($user1);
        $expense2->setPaidAt(new \DateTime('-2 days'));
        $expense2->setHousehold($mainHousehold);
        $expense2->addSplitBetween($admin);
        $expense2->addSplitBetween($user1);
        $expense2->addSplitBetween($user2);
        $expense2->setIsPaid(false);
        $manager->persist($expense2);

        $expense3 = new Expense();
        $expense3->setTitle('Compra super');
        $expense3->setDescription('Compra del supermercat');
        $expense3->setAmount('125.75');
        $expense3->setCategory('Menjar');
        $expense3->setPaidBy($user2);
        $expense3->setPaidAt(new \DateTime('2026-01-03 12:00:00'));
        $expense3->setHousehold($mainHousehold);
        $expense3->addSplitBetween($admin);
        $expense3->addSplitBetween($user1);
        $expense3->addSplitBetween($user2);
        $expense3->setIsPaid(false);
        $manager->persist($expense3);

        // 6️⃣ Guardar tot
        $manager->flush();
    }
}
