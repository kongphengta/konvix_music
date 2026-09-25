<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
final class DashboardController extends AbstractDashboardController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard_redirect')]
    #[IsGranted('ROLE_ADMIN')]
    public function dashboardRedirect(): RedirectResponse
    {
        return $this->redirectToRoute('admin');
    }

    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'artist_count' => 0,
            'user_count' => 0,
            'pending_artists' => 0,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Konvix Music');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-house');
        yield MenuItem::linkTo(ArtistCrudController::class, 'Artistes', 'fa fa-music')->setAction('index');
        yield MenuItem::linkToRoute('Validation artistes', 'fa fa-check-double', 'app_admin_artist_validation');
        yield MenuItem::linkTo(UserAdminCrudController::class, 'Utilisateurs', 'fa fa-user')->setAction('index');
    }
}
