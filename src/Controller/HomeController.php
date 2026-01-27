<?php

namespace App\Controller;

use App\Entity\Element;
use App\Repository\ElementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    // 1. PORTADA (Dashboard con botones de colores)
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Solo renderizamos la vista del menú principal.
        // No necesitamos cargar jugadores aquí porque solo hay botones.
        return $this->render('home/index.html.twig');
    }

    // 2. LISTA DE JUGADORES (Al hacer clic en el botón Azul)
    #[Route('/jugadores', name: 'app_player_list')]
    public function list(ElementRepository $elementRepository): Response
    {
        // Aquí buscamos TODOS los jugadores para mostrarlos en el grid
        return $this->render('home/list.html.twig', [
            'elements' => $elementRepository->findAll(),
        ]);
    }

    // 3. FICHA DETALLADA (Al hacer clic en "Ver Ficha" de un jugador)
    #[Route('/jugador/{id}', name: 'app_player_show')]
    public function show(Element $element): Response
    {
        // Symfony busca automáticamente el jugador por su ID
        return $this->render('home/show.html.twig', [
            'element' => $element,
        ]);
    }
}
