<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StatsController extends AbstractController
{
    #[Route('/stats', name: 'app_stats')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        $statsData = [];

        foreach ($categories as $category) {
            // Obtenemos los jugadores y convertimos la colección a Array para poder ordenar
            $players = $category->getElements()->toArray();

            // ORDENAR: De MENOR a MAYOR media (Ascendente)
            // Lógica: La posición 1.0 es mejor que la 5.0
            usort($players, function ($a, $b) use ($category) {
                $rankA = $a->getAverageRank($category);
                $rankB = $b->getAverageRank($category);

                // 1. DESEMPATE: Si tienen exactamente la misma media
                if ($rankA == $rankB) {
                    // Ordenamos alfabéticamente por nombre para que la lista sea estable
                    return strcmp($a->getName(), $b->getName());
                }

                // 2. ORDEN PRINCIPAL: El número MENOR va antes (-1)
                // Si rankA es 1.5 y rankB es 3.0 -> A gana (se pone antes)
                return ($rankA < $rankB) ? -1 : 1;
            });

            $statsData[] = [
                'category' => $category,
                'players' => $players
            ];
        }

        return $this->render('stats/index.html.twig', [
            'statsData' => $statsData,
        ]);
    }
}
