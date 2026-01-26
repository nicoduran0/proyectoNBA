<?php

namespace App\Command;

use App\Entity\Element;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:import-data',
    description: 'Importa jugadores de la API de NBA rellenando todos los campos',
)]
class ImportDataCommand extends Command
{
    public function __construct(
        private HttpClientInterface $client,
        private EntityManagerInterface $entityManager,
        private CategoryRepository $categoryRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Iniciando importación completa de la NBA...');

        // 1. Buscamos la Categoría "Baloncesto"
        $category = $this->categoryRepository->findOneBy(['name' => 'Baloncesto']);

        if (!$category) {
            $io->error('No encuentro la categoría "Baloncesto".');
            return Command::FAILURE;
        }

        // 2. Descargamos los datos
        $response = $this->client->request('GET', 'https://devsapihub.com/api-players');
        $players = $response->toArray();

        foreach ($players as $playerData) {
            // Comprobar si ya existe para no duplicar
            $existingElement = $this->entityManager->getRepository(Element::class)->findOneBy(['name' => $playerData['name']]);

            if ($existingElement) {
                // Si ya existe, no hacemos nada y pasamos al siguiente
                continue;
            }

            $element = new Element();

            // --- ASIGNACIÓN DE DATOS ---
            $element->setName($playerData['name']);
            $element->setImage($playerData['imgSrc']);

            $teamNameClean = str_replace(['[', ']'], '', $playerData['teamName']);
            $element->setTeam($teamNameClean);

            $element->setPosition($playerData['position']);
            $element->setStats($playerData['stats']);
            $element->setNumber($playerData['number']);

            // --- CORRECCIÓN AQUÍ ---
            // Usamos '?? null' para decir: "Si no existe 'id', usa null"
            $element->setApiId($playerData['id'] ?? null);

            $element->setDescription($playerData['info']);
            $element->setCategory($category);

            $this->entityManager->persist($element);
        }

        $this->entityManager->flush();

        $io->success('¡Base de datos actualizada correctamente (incluso los jugadores sin ID)!');

        return Command::SUCCESS;
    }
}
