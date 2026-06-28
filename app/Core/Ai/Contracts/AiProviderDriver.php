<?php

namespace App\Core\Ai\Contracts;

use App\Core\Ai\Dto\AiChatCompletionResult;
use App\Core\Ai\Dto\AiTestResult;
use App\Models\Ai\AiConnection;

interface AiProviderDriver
{
    public function fetchModels(AiConnection $connection): array;

    public function testConnection(AiConnection $connection): AiTestResult;

    /**
     * @param  list<array{role: string, content: string}>  $messages
     */
    public function completeChat(
        AiConnection $connection,
        array $messages,
        ?int $maxTokens = null,
        bool $jsonResponse = false,
    ): AiChatCompletionResult;
}
