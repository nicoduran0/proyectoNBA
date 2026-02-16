<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\UserRanking;
use App\Repository\CategoryRepository;
use App\Repository\ElementRepository;
use App\Repository\UserRankingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class RankingController extends AbstractController
{
    #[Route('/ranking', name: 'app_ranking')]
    public function index(CategoryRepository $categoryRepository, UserRankingRepository $rankingRepo): Response
    {
        $user = $this->getUser();
        $categories = $categoryRepository->findAll();
        $tierLists = [];

        foreach ($categories as $category) {
            $elements = $category->getElements()->toArray();

            // Si la categoría está vacía, saltamos
            if (empty($elements)) continue;

            // 1. Buscamos si el usuario ya tiene un orden guardado
            $userRankings = $rankingRepo->findBy([
                'owner' => $user,
                'category' => $category
            ], ['position' => 'ASC']);

            // 2. Si tiene orden, reordenamos los elementos
            if ($userRankings) {
                $orderMap = [];
                foreach ($userRankings as $ur) {
                    // Mapeamos ID_Elemento => Posición
                    $orderMap[$ur->getElement()->getId()] = $ur->getPosition();
                }

                usort($elements, function($a, $b) use ($orderMap) {
                    // Los que no tengan posición (nuevos) van al final (9999)
                    $posA = $orderMap[$a->getId()] ?? 9999;
                    $posB = $orderMap[$b->getId()] ?? 9999;
                    return $posA <=> $posB;
                });
            }

            $tierLists[] = [
                'category' => $category,
                'elements' => $elements
            ];
        }

        return $this->render('ranking/index.html.twig', [
            'tier_lists' => $tierLists,
        ]);
    }

    #[Route('/ranking/save/{id}', name: 'app_ranking_save', methods: ['POST'])]
    public function save(
        Category $category,
        Request $request,
        EntityManagerInterface $em,
        UserRankingRepository $rankingRepo,
        ElementRepository $elementRepo
    ): Response
    {
        $user = $this->getUser();

        // Recibimos los IDs ordenados (ej: "10,2,5,8")
        $orderString = $request->request->get('order');
        $orderedIds = explode(',', $orderString);

        // 1. Borrar orden anterior de ESTE usuario en ESTA categoría
        // (Para evitar duplicados y conflictos)
        $oldRankings = $rankingRepo->findBy(['owner' => $user, 'category' => $category]);
        foreach ($oldRankings as $old) {
            $em->remove($old);
        }
        $em->flush();

        // 2. Guardar el nuevo orden
        foreach ($orderedIds as $index => $elementId) {
            if (!$elementId) continue;

            $element = $elementRepo->find($elementId);
            if ($element) {
                $ranking = new UserRanking();
                $ranking->setOwner($user);
                $ranking->setCategory($category);
                $ranking->setElement($element);
                $ranking->setPosition($index + 1); // Posición 1, 2, 3...

                $em->persist($ranking);
            }
        }

        $em->flush();

        $this->addFlash('success', '¡Ranking guardado correctamente!');
        return $this->redirectToRoute('app_ranking');
    }
}
