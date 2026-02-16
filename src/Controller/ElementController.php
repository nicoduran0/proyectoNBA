<?php

namespace App\Controller;

use App\Entity\Element;
use App\Entity\Rating;
use App\Form\RatingType;
use App\Repository\ElementRepository;
use App\Repository\RatingRepository;
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
    #[Route('/', name: 'app_element_index', methods: ['GET'])]
    public function index(ElementRepository $elementRepository): Response
    {
        return $this->render('element/index.html.twig', [
            'elements' => $elementRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_element_show', methods: ['GET', 'POST'])]
    public function show(
        Element $element,
        Request $request,
        EntityManagerInterface $entityManager,
        RatingRepository $ratingRepository
    ): Response
    {
        $user = $this->getUser();

        // 1. Buscar si ya existe un voto de este usuario para editarlo
        $rating = $ratingRepository->findOneBy([
            'owner' => $user,
            'element' => $element
        ]);

        // 2. Si no existe, preparamos uno nuevo
        if (!$rating) {
            $rating = new Rating();
            $rating->setElement($element);
            $rating->setOwner($user);
        }

        // 3. Crear y manejar el formulario
        $form = $this->createForm(RatingType::class, $rating);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Aseguramos las relaciones
            $rating->setOwner($user);
            $rating->setElement($element);

            $entityManager->persist($rating);
            $entityManager->flush();

            // Mensaje según si es nuevo o edición
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
