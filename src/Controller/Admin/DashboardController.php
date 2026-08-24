<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Entity\ArtistProfile;
use App\Entity\Category;
use App\Entity\Song;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;


#[AdminDashboard(routePath: '/admin/dashboard', routeName: 'admin_dashboard')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Konvix Music Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('Gestion du site');

        yield MenuItem::linkTo(ArtistCrudController::class, 'Artistes', 'fa fa-microphone');
        yield MenuItem::linkTo(AlbumCrudController::class, 'Albums', 'fa fa-compact-disc');
        yield MenuItem::linkTo(SongCrudController::class, 'Chansons', 'fa fa-music');
        yield MenuItem::linkTo(CategoryCrudController::class, 'Catégories', 'fa fa-list');

        yield MenuItem::section('Navigation');
        yield MenuItem::linkToDashboard('Retour au Dashboard', 'fa fa-home');
        yield MenuItem::linkToRoute('Retour au site', 'fa fa-globe', 'app_home');
    }


}
