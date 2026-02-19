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
            $players = $category->getElements()->toArray();

            usort($players, function ($a, $b) use ($category) {
                $rankA = $a->getAverageRank($category);
                $rankB = $b->getAverageRank($category);

                if ($rankA == $rankB) {
                    return strcmp($a->getName(), $b->getName());
                }
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
