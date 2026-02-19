<?php

namespace App\Controller\Admin;

use App\Entity\Element;
use App\Repository\ElementRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ElementCrudController extends AbstractCrudController
{
    public function __construct(
        private HttpClientInterface $client,
        private EntityManagerInterface $entityManager,
        private ElementRepository $elementRepository,
        private AdminUrlGenerator $adminUrlGenerator
    ) {}

    public static function getEntityFqcn(): string
    {
        return Element::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $importBtn = Action::new('importPlayers', 'Importar desde API', 'fa fa-download')
            ->linkToCrudAction('importFromApi')
            ->createAsGlobalAction()
            ->setCssClass('btn btn-primary');

        return $actions->add(Crud::PAGE_INDEX, $importBtn);
    }

    public function importFromApi(AdminContext $context): Response
    {
        try {
            $response = $this->client->request('GET', 'https://devsapihub.com/api-players');
            $data = $response->toArray();
            $players = $data['data'] ?? $data['member'] ?? $data;

            $contadorCreados = 0;
            $contadorActualizados = 0;

            foreach ($players as $playerData) {
                $apiId = $playerData['id'] ?? null;
                $name = $playerData['name'] ?? $playerData['nombre'] ?? 'Unknown Player';

                $element = null;
                if ($apiId) {
                    $element = $this->elementRepository->findOneBy(['apiId' => $apiId]);
                }
                if (!$element) {
                    $element = $this->elementRepository->findOneBy(['name' => $name]);
                }

                if (!$element) {
                    $element = new Element();
                    $element->setApiId($apiId);
                    $contadorCreados++;
                } else {
                    $contadorActualizados++;
                }

                $element->setName($name);
                $element->setTeam($playerData['team'] ?? $playerData['teamName'] ?? $playerData['equipo'] ?? 'Agente Libre');
                $element->setPosition($playerData['position'] ?? $playerData['posicion'] ?? 'N/A');
                $element->setImage($playerData['image'] ?? $playerData['imgSrc'] ?? $playerData['img'] ?? '');
                $element->setStats($playerData['stats'] ?? 'Altura: ' . ($playerData['height'] ?? 'N/A'));
                $element->setDescription($playerData['description'] ?? $playerData['info'] ?? '');
                $element->setNumber((string)($playerData['number'] ?? $playerData['dorsal'] ?? ''));

                $this->entityManager->persist($element);
            }

            $this->entityManager->flush();
            $this->addFlash('success', "¡Importación completada! Creados: $contadorCreados | Actualizados: $contadorActualizados");

        } catch (\Exception $e) {
            $this->addFlash('danger', 'Error al importar: ' . $e->getMessage());
        }

        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl());
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nombre');
        yield TextField::new('team', 'Equipo');
        yield TextField::new('position', 'Posición');
        yield IntegerField::new('number', 'Dorsal');

        yield AssociationField::new('categories', 'Categorías')
            ->setFormTypeOption('by_reference', false)
            ->autocomplete();

        yield ImageField::new('image', 'Foto')
            ->setBasePath('uploads/images')
            ->setUploadDir('public/uploads/images')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setRequired(false);

        yield TextEditorField::new('description', 'Descripción')->hideOnIndex();
    }
}
