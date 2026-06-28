<?php

namespace Tests\Unit;

use App\Core\Ai\Dto\AiFieldDefinition;
use App\Core\Ai\Services\AiService;
use App\Core\Ai\Support\AiResponseParser;
use App\Modules\Catalog\Services\CategoryAiContentService;
use App\Modules\Catalog\Support\CategoryAiContentSchema;
use PHPUnit\Framework\TestCase;

class AiResponseParserTest extends TestCase
{
    public function test_it_parses_valid_json_object(): void
    {
        $parser = new AiResponseParser;

        $result = $parser->parse(
            '{"short_description_en":"English summary","short_description_ur":"اردو"}',
            [
                new AiFieldDefinition('short_description_en'),
                new AiFieldDefinition('short_description_ur'),
            ],
        );

        $this->assertTrue($result->success);
        $this->assertSame('English summary', $result->data['short_description_en']);
        $this->assertSame('اردو', $result->data['short_description_ur']);
    }

    public function test_it_extracts_json_from_markdown_fence(): void
    {
        $parser = new AiResponseParser;

        $result = $parser->parse(
            "```json\n{\"seo_title\":\"Seeds Category\"}\n```",
            [new AiFieldDefinition('seo_title')],
        );

        $this->assertTrue($result->success);
        $this->assertSame('Seeds Category', $result->data['seo_title']);
    }

    public function test_it_fails_when_required_field_missing(): void
    {
        $parser = new AiResponseParser;

        $result = $parser->parse(
            '{"short_description_en":"Only English"}',
            [
                new AiFieldDefinition('short_description_en'),
                new AiFieldDefinition('short_description_ur'),
            ],
        );

        $this->assertFalse($result->success);
        $this->assertStringContainsString('short_description_ur', $result->errorSummary());
    }

    public function test_it_maps_name_ur_to_form_field(): void
    {
        $service = new CategoryAiContentService(
            app(AiService::class),
            new AiResponseParser,
        );

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('mapToFormFields');
        $method->setAccessible(true);

        /** @var array<string, string> $mapped */
        $mapped = $method->invoke($service, [
            'name_ur' => 'بیج',
            'seo_title' => 'Seeds',
        ]);

        $this->assertSame('بیج', $mapped['urdu_name']);
        $this->assertSame('Seeds', $mapped['seo_title']);
    }

    public function test_it_requires_name_ur_in_category_schema(): void
    {
        $parser = new AiResponseParser;

        $result = $parser->parse(
            '{"short_description_en":"English","short_description_ur":"اردو","long_description_en":"L","long_description_ur":"L","usage_en":"U","usage_ur":"U","benefits_en":"B","benefits_ur":"B","seo_title":"T","seo_focus_keyword_en":"K","seo_focus_keyword_ur":"K","meta_description":"D","url_slug":"seeds"}',
            CategoryAiContentSchema::fields(),
        );

        $this->assertFalse($result->success);
        $this->assertStringContainsString('name_ur', $result->errorSummary());
    }
}
