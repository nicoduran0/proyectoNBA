<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Element;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:import-players',
    description: 'Importa jugadores de la NBA desde la API externa',
)]
class ImportPlayersCommand extends Command
{
    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->client = $client;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Iniciando importación de jugadores de la NBA...');

        // 1. Crear o buscar la Categoría
        $categoryRepo = $this->entityManager->getRepository(Category::class);
        $category = $categoryRepo->findOneBy(['name' => 'NBA Players']);

        if (!$category) {
            $category = new Category();
            $category->setName('NBA Players');
            $this->entityManager->persist($category);
            $io->note('Categoría "NBA Players" creada.');
        }

        // 2. Conectar a la API
        try {
            $response = $this->client->request('GET', 'https://devsapihub.com/api-players');
            $players = $response->toArray();
        } catch (\Exception $e) {
            $io->error('Error al conectar con la API: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $io->progressStart(count($players));
        $contador = 0;

        // 3. Recorrer cada jugador
        foreach ($players as $playerData) {

            // A. EXTRACCIÓN SEGURA DEL ID (Aquí estaba el fallo)
            // Usamos '?? null' para decir: si no existe id, usa null.
            $apiId = $playerData['id'] ?? null;
            $name = $playerData['name'];

            // B. VERIFICAR DUPLICADOS
            $elementRepo = $this->entityManager->getRepository(Element::class);

            // Si tiene ID, buscamos por ID. Si no, buscamos por Nombre.
            if ($apiId) {
                $existingElement = $elementRepo->findOneBy(['apiId' => $apiId]);
            } else {
                $existingElement = $elementRepo->findOneBy(['name' => $name]);
            }

            if ($existingElement) {
                $io->progressAdvance();
                continue; // Ya existe, saltamos al siguiente
            }

            // C. CREAR EL JUGADOR
            $element = new Element();
            $element->setApiId($apiId); // Puede ser null, y está bien
            $element->setName($name);

            // Usamos ?? '' por si acaso algún otro campo viniera vacío en el futuro
            $element->setTeam($playerData['teamName'] ?? 'Unknown Team');
            $element->setPosition($playerData['position'] ?? 'Unknown Position');
            $element->setImage($playerData['imgSrc'] ?? '');
            $element->setStats($playerData['stats'] ?? '');
            $element->setDescription($playerData['info'] ?? '');
            $element->setNumber($playerData['number'] ?? '');

            $element->setCategory($category);

            $this->entityManager->persist($element);
            $contador++;
            $io->progressAdvance();
        }

        // 4. Guardar cambios
        $this->entityManager->flush();

        $io->progressFinish();
        $io->success("¡Éxito! Se han importado $contador jugadores.");

        return Command::SUCCESS;
    }
}
