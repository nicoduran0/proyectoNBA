<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Element;
use App\Entity\Rating;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Proyecto NBA Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Inicio', 'fa fa-home');
        yield MenuItem::section('Base de Datos');
        yield MenuItem::linkToCrud('Usuarios', 'fas fa-users', User::class);
        yield MenuItem::linkToCrud('Categorías', 'fas fa-list', Category::class);
        yield MenuItem::linkToCrud('Elementos (Jugadores)', 'fas fa-basketball-ball', Element::class);
        yield MenuItem::linkToCrud('Valoraciones', 'fas fa-star', Rating::class);
        yield MenuItem::section('Navegación');
        yield MenuItem::linkToRoute('Volver a la Web', 'fa fa-arrow-left', 'app_home');
    }
}
