<?php

namespace App\Controller;

use App\Entity\Element;
use App\Entity\Rating;
use App\Form\RatingType;
use App\Repository\ElementRepository;
use App\Repository\RatingRepository; // <--- IMPORTANTE: Añadir esto
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/players')]
#[IsGranted('ROLE_USER')]
final class ElementController extends AbstractController
{
    #[Route(name: 'app_element_index', methods: ['GET'])]
    public function index(ElementRepository $elementRepository): Response
    {
        return $this->render('element/index.html.twig', [
            'elements' => $elementRepository->findAll(),
        ]);
    }

    #[Route('/ranking/top', name: 'app_ranking_index', methods: ['GET'])]
    public function ranking(ElementRepository $elementRepository): Response
    {
        $players = $elementRepository->findAll();

        usort($players, function ($a, $b) {
            return $b->getCalculatedAverage() <=> $a->getCalculatedAverage();
        });

        return $this->render('element/ranking.html.twig', [
            'top_players' => $players,
        ]);
    }

    #[Route('/{id}', name: 'app_element_show', methods: ['GET', 'POST'])]
    public function show(
        Element $element,
        Request $request,
        EntityManagerInterface $entityManager,
        RatingRepository $ratingRepository // <--- NECESARIO para buscar si ya votó
    ): Response
    {
        $user = $this->getUser();

        // 1. BUSCAR SI YA EXISTE UN VOTO DE ESTE USUARIO
        $rating = $ratingRepository->findOneBy([
            'owner' => $user,
            'element' => $element
        ]);

        // 2. SI NO EXISTE, PREPARAMOS UNO NUEVO
        if (!$rating) {
            $rating = new Rating();
            $rating->setElement($element);
            $rating->setOwner($user);
        }

        // 3. CREAR EL FORMULARIO (Cargará los datos si ya existían o estará vacío si es nuevo)
        $form = $this->createForm(RatingType::class, $rating);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Nos aseguramos de que las relaciones están bien puestas
            $rating->setOwner($user);
            $rating->setElement($element);

            $entityManager->persist($rating);
            $entityManager->flush();

            // Mensaje personalizado
            $mensaje = $rating->getId() ? '¡Tu valoración se ha actualizado!' : '¡Gracias por tu voto!';
            $this->addFlash('success', $mensaje);

            return $this->redirectToRoute('app_element_show', ['id' => $element->getId()]);
        }

        return $this->render('element/show.html.twig', [
            'element' => $element,
            'form' => $form->createView(),
        ]);
    }
}
