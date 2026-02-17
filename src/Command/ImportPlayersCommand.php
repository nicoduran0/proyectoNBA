<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Element;
use App\Repository\CategoryRepository; // Añadido para inyección limpia
use App\Repository\ElementRepository;  // Añadido para inyección limpia
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:import-players',
    description: 'Importa y actualiza jugadores de la NBA desde la API externa',
)]
class ImportPlayersCommand extends Command
{
    // Usamos la promoción de propiedades del constructor (PHP 8+) para un código más limpio
    public function __construct(
        private HttpClientInterface $client,
        private EntityManagerInterface $entityManager,
        private ElementRepository $elementRepository,
        private CategoryRepository $categoryRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Iniciando importación de jugadores de la NBA...');

        // 1. Crear o buscar la Categoría
        // Usamos el repositorio inyectado
        $category = $this->categoryRepository->findOneBy(['name' => 'NBA Players']);

        if (!$category) {
            $category = new Category();
            $category->setName('NBA Players');
            $this->entityManager->persist($category);
            // Hacemos flush aquí para asegurar que la categoría tiene ID si es nueva
            $this->entityManager->flush();
            $io->note('Categoría "NBA Players" creada.');
        }

        // 2. Conectar a la API
        try {
            // Nota: Si la API real cambia, verifica esta URL
            $response = $this->client->request('GET', 'https://devsapihub.com/api-players');
            $data = $response->toArray();

            // A veces las APIs devuelven { data: [...] } o directamente [...].
            // Esto asegura que cogemos la lista correcta.
            $players = $data['data'] ?? $data['member'] ?? $data;

        } catch (\Exception $e) {
            $io->error('Error al conectar con la API: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $io->progressStart(count($players));
        $contadorCreados = 0;
        $contadorActualizados = 0;

        // 3. Recorrer cada jugador
        foreach ($players as $playerData) {

            // A. EXTRACCIÓN SEGURA DEL ID y NOMBRE
            $apiId = $playerData['id'] ?? null;
            // Buscamos 'name' o 'nombre' por si la API es en español/ingles
            $name = $playerData['name'] ?? $playerData['nombre'] ?? 'Unknown Player';

            // B. BUSCAR SI YA EXISTE (Para actualizar o crear)
            $element = null;

            if ($apiId) {
                $element = $this->elementRepository->findOneBy(['apiId' => $apiId]);
            }

            // Si no tiene ID de API o no lo encontramos por ID, intentamos por nombre
            if (!$element) {
                $element = $this->elementRepository->findOneBy(['name' => $name]);
            }

            // Si sigue sin existir, es NUEVO
            if (!$element) {
                $element = new Element();
                $element->setApiId($apiId);
                $contadorCreados++;
            } else {
                $contadorActualizados++;
            }

            // C. SETTEAR DATOS (Actualizamos siempre, por si han cambiado stats o equipos)
            $element->setName($name);

            // Usamos operador de fusión de null (??) probando varias claves comunes de APIs
            $element->setTeam($playerData['team'] ?? $playerData['teamName'] ?? $playerData['equipo'] ?? 'Agente Libre');
            $element->setPosition($playerData['position'] ?? $playerData['posicion'] ?? 'N/A');
            $element->setImage($playerData['image'] ?? $playerData['imgSrc'] ?? $playerData['img'] ?? '');

            // Stats y Descripción
            $element->setStats($playerData['stats'] ?? 'Altura: ' . ($playerData['height'] ?? 'N/A'));
            $element->setDescription($playerData['description'] ?? $playerData['info'] ?? '');
            $element->setNumber((string)($playerData['number'] ?? $playerData['dorsal'] ?? ''));

            // --- CORRECCIÓN CRÍTICA: RELACIÓN MANY-TO-MANY ---
            // No existe setCategory, existe addCategory
            $element->addCategory($category);

            $this->entityManager->persist($element);
            $io->progressAdvance();
        }

        // 4. Guardar cambios
        $this->entityManager->flush();

        $io->progressFinish();
        $io->success("¡Proceso finalizado! Creados: $contadorCreados | Actualizados: $contadorActualizados");

        return Command::SUCCESS;
    }
}
