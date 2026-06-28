<?php

namespace App\Core\Ai\Support;

use App\Core\Ai\Dto\AiResolvedPrompt;
use App\Core\Ai\Dto\AiTaskRequest;
use App\Core\Ai\Enums\AiPromptOutputFormat;
use App\Core\Ai\Enums\AiTaskType;
use App\Core\Ai\Services\AiPromptTemplateManager;
use App\Models\Ai\AiPromptTemplate;
use InvalidArgumentException;

final class AiTaskPromptRegistry
{
    public static function build(AiTaskRequest $request): AiResolvedPrompt
    {
        return match ($request->taskType) {
            AiTaskType::ConnectionTest => self::connectionTestPrompt(),
            AiTaskType::CustomPrompt => self::customPrompt($request),
            default => self::fromTemplate($request),
        };
    }

    protected static function fromTemplate(AiTaskRequest $request): AiResolvedPrompt
    {
        $template = AiPromptTemplateManager::resolve($request->taskType, $request->targetModule);

        if (! $template) {
            throw new InvalidArgumentException(sprintf(
                'No active prompt template is configured for task [%s] in module [%s]. Configure one under Settings → AI → Prompt Templates.',
                $request->taskType->label(),
                $request->targetModule->label(),
            ));
        }

        return self::buildFromTemplate($template, $request);
    }

    protected static function buildFromTemplate(AiPromptTemplate $template, AiTaskRequest $request): AiResolvedPrompt
    {
        $variables = AiPromptVariableRegistry::enrichContext(
            $request->taskType,
            $request->context,
        );

        $system = AiPromptTemplateInterpolator::interpolate($template->system_prompt, $variables);
        $user = AiPromptTemplateInterpolator::interpolate($template->user_prompt_template, $variables);

        $jsonResponse = $template->output_format === AiPromptOutputFormat::Json
            || $request->taskType->requiresJsonResponse();

        return new AiResolvedPrompt(
            system: $system,
            user: $user,
            temperature: $template->temperature,
            maxOutputTokens: $template->max_output_tokens,
            jsonResponse: $jsonResponse,
        );
    }

    protected static function customPrompt(AiTaskRequest $request): AiResolvedPrompt
    {
        $prompt = trim((string) $request->customPrompt);

        if ($prompt === '') {
            $prompt = trim((string) ($request->context['prompt'] ?? ''));
        }

        if ($prompt === '') {
            throw new InvalidArgumentException('Custom prompt tasks require a customPrompt or context.prompt value.');
        }

        $maxLength = AiConfig::customPromptMaxLength();

        if (mb_strlen($prompt) > $maxLength) {
            throw new InvalidArgumentException("Custom prompt exceeds the maximum length of {$maxLength} characters.");
        }

        $template = AiPromptTemplateManager::resolve(
            AiTaskType::CustomPrompt,
            $request->targetModule,
        );

        if ($template) {
            $variables = AiPromptVariableRegistry::enrichContext(
                AiTaskType::CustomPrompt,
                array_merge($request->context, ['prompt' => $prompt]),
            );

            return new AiResolvedPrompt(
                system: AiPromptTemplateInterpolator::interpolate($template->system_prompt, $variables),
                user: AiPromptTemplateInterpolator::interpolate($template->user_prompt_template, $variables),
                temperature: $template->temperature,
                maxOutputTokens: $template->max_output_tokens,
                jsonResponse: $template->output_format === AiPromptOutputFormat::Json,
            );
        }

        $system = trim((string) ($request->context['system_prompt'] ?? 'You are a helpful assistant for Agricart ERP.'));

        return new AiResolvedPrompt(
            system: $system,
            user: $prompt,
        );
    }

    protected static function connectionTestPrompt(): AiResolvedPrompt
    {
        return new AiResolvedPrompt(
            system: 'You are a connectivity test assistant.',
            user: 'Reply with exactly: OK',
        );
    }
}
