<?php

namespace App\Presentation\Controllers;

use App\Application\Services\ChatService;

/**
 * Controller do Chat (LívIA).
 * Camada de Apresentação: Processa requisições JSON de conversa.
 */
class ChatController
{
    public function __construct(private ChatService $service) {}

    public function chat(string $message): array
    {
        return $this->service->askLivia($message);
    }
}
