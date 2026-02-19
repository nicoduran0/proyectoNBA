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

            if (empty($elements)) continue;

            $userRankings = $rankingRepo->findBy([
                'owner' => $user,
                'category' => $category
            ], ['position' => 'ASC']);

            if ($userRankings) {
                $orderMap = [];
                foreach ($userRankings as $ur) {
                    $orderMap[$ur->getElement()->getId()] = $ur->getPosition();
                }

                usort($elements, function($a, $b) use ($orderMap) {
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

        $orderString = $request->request->get('order');
        $orderedIds = explode(',', $orderString);

        $oldRankings = $rankingRepo->findBy(['owner' => $user, 'category' => $category]);
        foreach ($oldRankings as $old) {
            $em->remove($old);
        }
        $em->flush();

        foreach ($orderedIds as $index => $elementId) {
            if (!$elementId) continue;

            $element = $elementRepo->find($elementId);
            if ($element) {
                $ranking = new UserRanking();
                $ranking->setOwner($user);
                $ranking->setCategory($category);
                $ranking->setElement($element);
                $ranking->setPosition($index + 1);

                $em->persist($ranking);
            }
        }

        $em->flush();

        $this->addFlash('success', '¡Ranking guardado correctamente!');
        return $this->redirectToRoute('app_ranking');
    }
}
