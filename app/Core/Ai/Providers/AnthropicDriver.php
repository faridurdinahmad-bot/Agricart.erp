<?php

namespace App\Core\Ai\Providers;

use App\Core\Ai\Dto\AiChatCompletionResult;
use App\Core\Ai\Dto\AiTestResult;
use App\Models\Ai\AiConnection;

final class AnthropicDriver extends AbstractAiProviderDriver
{
    public function fetchModels(AiConnection $connection): array
    {
        throw new \RuntimeException('Anthropic model fetching is not implemented yet.');
    }

    public function testConnection(AiConnection $connection): AiTestResult
    {
        return $this->unsupported($connection);
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     */
    public function completeChat(
        AiConnection $connection,
        array $messages,
        ?int $maxTokens = null,
        bool $jsonResponse = false,
    ): AiChatCompletionResult {
        return $this->unsupportedChat($connection, $messages);
    }
}
