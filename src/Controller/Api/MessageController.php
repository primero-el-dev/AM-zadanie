<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Finder\Finder;

#[Route('/api/message')]
class MessageController extends AbstractController
{
    #[Route('/', name: 'app_message_index', methods: 'GET')]
    public function index(Request $request): JsonResponse
    {
        $finder = new Finder();
        $finder->files()->in($_ENV['NOTES_DIR'])->exclude('.*');

        $sortBy = $request->query->all()['sort-by'] ?? null;
        if ($sortBy === 'date') {
            $finder->sortByChangedTime();
        } elseif ($sortBy === 'id') {
            $finder->sortByName();
        }

        $messages = [];
        foreach ($finder as $file) {
            $messages[] = $this->createMessageFromFile($file);
        }

        return $this->json($messages);
    }
    
    #[Route('/', name: 'api_message_create', methods: 'POST')]
    public function create(Request $request): JsonResponse
    {
        $uuid = Uuid::v4();
        $data = json_decode($request->getContent(), true);

        file_put_contents($_ENV['NOTES_DIR'] . '/' . $uuid, $data['content']);

        return $this->json($uuid);
    }
    
    #[Route('/{id}', name: 'app_message_show', methods: 'GET')]
    public function show(string $id): JsonResponse
    {
        $finder = new Finder();
        $finder->files()->in($_ENV['NOTES_DIR'])->name($id);
        if (!$finder->hasResults()) {
            throw new \Exception('Message not found.');
        }

        foreach ($finder as $file) {
            return $this->json($this->createMessageFromFile($file));
        }
    }

    private function createMessageFromFile(\SplFileInfo $fileInfo): array
    {
        return [
            'id' => $fileInfo->getFileName(), 
            'created_at' => date_timestamp_set(date_create(), $fileInfo->getCTime())->format('Y-m-d H:i:s'),
            'content' => file_get_contents($fileInfo->getPathname()),
        ];
    }
}
