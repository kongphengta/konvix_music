<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
final class DashboardController extends AbstractDashboardController
{
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
        yield MenuItem::linkToCrud('Artistes', 'fa fa-music', Artist::class);
        yield MenuItem::linkToRoute('Validation artistes', 'fa fa-check-double', 'app_admin_artist_validation');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-user', User::class);
    }
}
