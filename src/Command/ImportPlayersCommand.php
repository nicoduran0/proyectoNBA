<?php

namespace App\Command;

use App\Entity\Element;
use App\Repository\ElementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:import-players',
    description: 'Importa jugadores de la NBA desde la API externa (sin asignar categoría)',
)]
class ImportPlayersCommand extends Command
{
    // Hemos quitado CategoryRepository porque ya no lo vamos a usar
    public function __construct(
        private HttpClientInterface $client,
        private EntityManagerInterface $entityManager,
        private ElementRepository $elementRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Iniciando importación de jugadores...');

        // 1. Conectar a la API
        try {
            $response = $this->client->request('GET', 'https://devsapihub.com/api-players');
            $data = $response->toArray();
            $players = $data['data'] ?? $data['member'] ?? $data;
        } catch (\Exception $e) {
            $io->error('Error al conectar con la API: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $io->progressStart(count($players));
        $contadorCreados = 0;
        $contadorActualizados = 0;

        // 2. Recorrer cada jugador
        foreach ($players as $playerData) {

            $apiId = $playerData['id'] ?? null;
            $name = $playerData['name'] ?? $playerData['nombre'] ?? 'Unknown Player';

            // Buscar si ya existe
            $element = null;
            if ($apiId) {
                $element = $this->elementRepository->findOneBy(['apiId' => $apiId]);
            }
            if (!$element) {
                $element = $this->elementRepository->findOneBy(['name' => $name]);
            }

            // Crear o Actualizar
            if (!$element) {
                $element = new Element();
                $element->setApiId($apiId);
                $contadorCreados++;
            } else {
                $contadorActualizados++;
            }

            // Asignar datos básicos
            $element->setName($name);
            $element->setTeam($playerData['team'] ?? $playerData['teamName'] ?? $playerData['equipo'] ?? 'Agente Libre');
            $element->setPosition($playerData['position'] ?? $playerData['posicion'] ?? 'N/A');
            $element->setImage($playerData['image'] ?? $playerData['imgSrc'] ?? $playerData['img'] ?? '');

            $element->setStats($playerData['stats'] ?? 'Altura: ' . ($playerData['height'] ?? 'N/A'));
            $element->setDescription($playerData['description'] ?? $playerData['info'] ?? '');
            $element->setNumber((string)($playerData['number'] ?? $playerData['dorsal'] ?? ''));
            $this->entityManager->persist($element);
            $io->progressAdvance();
        }

        $this->entityManager->flush();

        $io->progressFinish();
        $io->success("¡Listo! Creados: $contadorCreados | Actualizados: $contadorActualizados");

        return Command::SUCCESS;
    }
}
