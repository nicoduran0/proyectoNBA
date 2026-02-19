<?php

namespace App\Controller;

use App\Entity\Element;
use App\Repository\ElementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/jugadores', name: 'app_player_list')]
    public function list(ElementRepository $elementRepository): Response
    {
        return $this->render('home/list.html.twig', [
            'elements' => $elementRepository->findAll(),
        ]);
    }

    #[Route('/jugador/{id}', name: 'app_player_show')]
    public function show(Element $element): Response
    {
        return $this->render('home/show.html.twig', [
            'element' => $element,
        ]);
    }
}
