<?php

namespace App\Controller;

use App\Entity\Element; // <--- ¡IMPORTANTE! Hemos añadido esto
use App\Repository\ElementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ElementRepository $elementRepository): Response
    {
        // Buscamos todos los jugadores
        $elements = $elementRepository->findAll();

        return $this->render('home/index.html.twig', [
            'elements' => $elements,
        ]);
    }

    #[Route('/jugador/{id}', name: 'app_player_show')]
    public function show(Element $element): Response
    {
        // Symfony busca automáticamente el jugador por su ID gracias a (Element $element)
        return $this->render('home/show.html.twig', [
            'element' => $element,
        ]);
    }
}
