<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\HouseholdRepository;
use App\Repository\TaskRepository;
use App\Repository\EventRepository;
use App\Repository\ExpenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function dashboard(
        TaskRepository $taskRepository,
        EventRepository $eventRepository,
        ExpenseRepository $expenseRepository
    ): Response {
        /** @var User $admin */
        $admin = $this->getUser();
        $household = $admin->getHousehold();

        $stats = [
            'totalUsers' => count($household->getUsers()),
            'totalTasks' => $taskRepository->count(['household' => $household]),
            'completedTasks' => $taskRepository->count([
                'household' => $household,
                'completed' => true
            ]),
            'totalEvents' => $eventRepository->count(['household' => $household]),
            'totalExpenses' => $expenseRepository->count(['household' => $household]),
            'unpaidExpenses' => $expenseRepository->count([
                'household' => $household,
                'isPaid' => false
            ]),
        ];

        return $this->render('admin/dashboard.html.twig', [
            'household' => $household,
            'stats' => $stats,
            'users' => $household->getUsers(),
        ]);
    }

    #[Route('/users', name: 'app_admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        /** @var User $admin */
        $admin = $this->getUser();
        $household = $admin->getHousehold();

        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findBy(['household' => $household]),
        ]);
    }

    #[Route('/users/new', name: 'app_admin_user_new')]
    public function newUser(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User $admin */
        $admin = $this->getUser();

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Assignar household AUTOMÀTICAMENT
            $user->setHousehold($admin->getHousehold());

            // Password
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $plainPassword)
                );
            }

            // Defaults
            $user->setJoinedAt(new \DateTime());
            $user->setIsActive(true);
            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Usuari creat correctament!');
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/user_form.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/users/{id}/edit', name: 'app_admin_user_edit')]
    public function editUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User $admin */
        $admin = $this->getUser();

        if ($user->getHousehold() !== $admin->getHousehold()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(UserType::class, $user, [
            'is_edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $plainPassword)
                );
            }

            $entityManager->flush();

            $this->addFlash('success', 'Usuari actualitzat correctament!');
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/user_form.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/users/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function deleteUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $admin */
        $admin = $this->getUser();

        if ($user->getHousehold() !== $admin->getHousehold()) {
            throw $this->createAccessDeniedException();
        }

        if ($user === $admin) {
            $this->addFlash('danger', 'No pots esborrar el teu propi compte!');
            return $this->redirectToRoute('app_admin_users');
        }

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'Usuari esborrat correctament!');
        }

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/users/{id}/toggle-active', name: 'app_admin_user_toggle_active', methods: ['POST'])]
    public function toggleActive(User $user, EntityManagerInterface $entityManager): Response
    {
        /** @var User $admin */
        $admin = $this->getUser();

        if ($user->getHousehold() !== $admin->getHousehold()) {
            throw $this->createAccessDeniedException();
        }

        $user->setIsActive(!$user->isActive());
        $entityManager->flush();

        $this->addFlash('success', 'L’usuari ha estat actualitzat correctament!');

        return $this->redirectToRoute('app_admin_users');
    }
}
