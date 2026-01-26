<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Element;
use App\Entity\Ranking;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route; // <--- ESTO ES LO QUE HEMOS ARREGLADO

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Esta parte redirige a la gestión de Usuarios automáticamente
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Proyecto Integrado');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Inicio', 'fa fa-home');

        yield MenuItem::section('Base de Datos');
        yield MenuItem::linkToCrud('Usuarios', 'fas fa-users', User::class);
        yield MenuItem::linkToCrud('Categorías', 'fas fa-list', Category::class);
        yield MenuItem::linkToCrud('Elementos', 'fas fa-cube', Element::class);
        yield MenuItem::linkToCrud('Rankings', 'fas fa-trophy', Ranking::class);
    }
}
