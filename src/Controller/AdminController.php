<?php

namespace App\Controller;
use App\Repository\UserRepository;
use App\Repository\HouseholdRepository;
use App\Repository\TaskRepository;
use App\Repository\EventRepository;
use App\Repository\ExpenseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function dashboard(
        UserRepository $userRepository,
        HouseholdRepository $householdRepository,
        TaskRepository $taskRepository,
        EventRepository $eventRepository,
        ExpenseRepository $expenseRepository
    ): Response {

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $household = $user->getHousehold();

        $stats = [
            'totalUsers' => count($household->getUsers()),
            'totalTasks' => $taskRepository->count(['household' => $household]),
            'completedTasks' => $taskRepository->count(['household' => $household, 'completed' => true]),
            'totalEvents' => $eventRepository->count(['household' => $household]),
            'totalExpenses' => $expenseRepository->count(['household' => $household]),
            'unpaidExpenses' => $expenseRepository->count(['household' => $household, 'isPaid' => false]),
        ];

        return $this->render('admin/dashboard.html.twig', [
            'household' => $household,
            'stats' => $stats,
            'users' => $household->getUsers(),
        ]);
    }
}
