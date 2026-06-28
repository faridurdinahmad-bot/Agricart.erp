<?php

namespace Database\Seeders;

use App\Core\Ai\Enums\AiPromptOutputFormat;
use App\Core\Ai\Enums\AiTargetModule;
use App\Core\Ai\Enums\AiTaskType;
use App\Core\Ai\Support\AiPromptVariableRegistry;
use App\Models\Ai\AiPromptTemplate;
use Illuminate\Database\Seeder;

class AiPromptTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $template) {
            AiPromptTemplate::query()->updateOrCreate(
                [
                    'task_type' => $template['task_type'],
                    'target_module' => $template['target_module'],
                    'name' => $template['name'],
                ],
                $template,
            );
        }

        AiPromptTemplate::query()
            ->where('task_type', AiTaskType::BrandContent)
            ->where('target_module', AiTargetModule::Brands)
            ->update(['is_active' => false]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function templates(): array
    {
        return [
            [
                'name' => 'Category Content',
                'target_module' => AiTargetModule::Catalog,
                'task_type' => AiTaskType::CategoryContent,
                'output_format' => AiPromptOutputFormat::Json,
                'temperature' => 0.70,
                'max_output_tokens' => 4096,
                'is_active' => true,
                'system_prompt' => 'You are an expert agricultural catalog copywriter for Agricart ERP. '
                    .'You MUST respond with valid JSON only. Never wrap JSON in markdown. '
                    .'Always include name_ur as the proper Urdu category title.',
                'user_prompt_template' => "Generate bilingual category content for Agricart ERP.\n"
                    ."Category name (English): {{english_name}}\n"
                    ."HS Code: {{hs_code}}\n"
                    ."Additional instructions: {{ai_prompt_override}}\n\n"
                    ."Return ONLY a single JSON object. No markdown, no commentary, no code fences.\n"
                    ."Required keys: {{required_keys}}.\n"
                    ."The name_ur field MUST be the full category name written in Urdu script (not English transliteration).\n"
                    ."Optional keys (include when relevant): {{optional_keys}}.\n"
                    .'All values must be plain strings.',
                'available_variables' => AiPromptVariableRegistry::forTaskType(AiTaskType::CategoryContent),
            ],
            [
                'name' => 'Product Content',
                'target_module' => AiTargetModule::Products,
                'task_type' => AiTaskType::ProductContent,
                'output_format' => AiPromptOutputFormat::Json,
                'temperature' => 0.70,
                'max_output_tokens' => 4096,
                'is_active' => true,
                'system_prompt' => 'You are an expert product copywriter for Agricart ERP. '
                    .'Respond with valid JSON only. Never wrap JSON in markdown.',
                'user_prompt_template' => "Generate bilingual product content for Agricart ERP.\n"
                    ."Product name (English): {{english_name}}\n"
                    ."Category: {{category}}\n"
                    ."Brand: {{brand}}\n"
                    ."Attributes: {{attributes}}\n\n"
                    .'Return structured JSON suitable for catalog product records.',
                'available_variables' => AiPromptVariableRegistry::forTaskType(AiTaskType::ProductContent),
            ],
            [
                'name' => 'Brand Content',
                'target_module' => AiTargetModule::Catalog,
                'task_type' => AiTaskType::BrandContent,
                'output_format' => AiPromptOutputFormat::Json,
                'temperature' => 0.70,
                'max_output_tokens' => 4096,
                'is_active' => true,
                'system_prompt' => 'You are an expert brand copywriter for the Agricart ERP product catalog. '
                    .'Agricart ERP is the software platform — it is NEVER the brand you are writing about unless english_name explicitly says Agricart. '
                    .'You MUST respond with valid JSON only. Never wrap JSON in markdown. '
                    .'The english_name field is the ONLY authoritative brand identity. Never invent, translate, or substitute a different company name. '
                    .'Only include country and website when you are highly confident — otherwise omit them or leave empty.',
                'user_prompt_template' => "Write catalog content for this brand record.\n\n"
                    ."AUTHORITATIVE brand name (English): {{english_name}}\n"
                    ."Short note / extra context (does NOT replace the brand name): {{short_note}}\n"
                    ."Assigned categories: {{assigned_categories}}\n\n"
                    ."CRITICAL RULES:\n"
                    ."- Every field must refer to the brand \"{{english_name}}\" only.\n"
                    ."- NEVER use Agricart, Agri Cart, Agricart ERP, or ایگری کارڈ unless english_name is exactly that brand.\n"
                    ."- short_note describes products or positioning — it does NOT change the brand name.\n"
                    ."- name_ur must represent the SAME brand as english_name. For acronyms and short Latin names (e.g. AK, NSK), write Urdu-script letter names separated by spaces (e.g. AK → \"اے کے\").\n"
                    ."- For established international brands with known Urdu spellings (e.g. Kubota → کوبوتا), use the conventional Urdu form.\n\n"
                    ."Return ONLY a single JSON object. No markdown, no commentary, no code fences.\n"
                    ."Required keys: {{required_keys}}.\n"
                    ."Optional keys (include when relevant): {{optional_keys}}.\n"
                    ."For country and website: only populate when confidently identifiable from the brand name and context. If unsure, omit or use empty string.\n"
                    .'All values must be plain strings.',
                'available_variables' => AiPromptVariableRegistry::forTaskType(AiTaskType::BrandContent),
            ],
            [
                'name' => 'Attribute Content',
                'target_module' => AiTargetModule::Attributes,
                'task_type' => AiTaskType::AttributeContent,
                'output_format' => AiPromptOutputFormat::Json,
                'temperature' => 0.70,
                'max_output_tokens' => 2048,
                'is_active' => true,
                'system_prompt' => 'You are an expert catalog attribute copywriter for Agricart ERP. '
                    .'Respond with valid JSON only. Never wrap JSON in markdown.',
                'user_prompt_template' => "Generate bilingual attribute content for Agricart ERP.\n"
                    ."Attribute name (English): {{english_name}}\n"
                    ."Category: {{category}}\n"
                    ."Related attributes: {{attributes}}\n\n"
                    .'Return structured JSON suitable for attribute records.',
                'available_variables' => AiPromptVariableRegistry::forTaskType(AiTaskType::AttributeContent),
            ],
            [
                'name' => 'Translation',
                'target_module' => AiTargetModule::System,
                'task_type' => AiTaskType::Translate,
                'output_format' => AiPromptOutputFormat::Text,
                'temperature' => 0.30,
                'max_output_tokens' => 2048,
                'is_active' => true,
                'system_prompt' => 'You are a professional translator for Agricart ERP. Return only the translated text without commentary.',
                'user_prompt_template' => "Translate the following text from {{source_language}} to {{target_language}}:\n\n{{source_text}}",
                'available_variables' => AiPromptVariableRegistry::forTaskType(AiTaskType::Translate),
            ],
            [
                'name' => 'SEO Generation',
                'target_module' => AiTargetModule::System,
                'task_type' => AiTaskType::SeoGeneration,
                'output_format' => AiPromptOutputFormat::Json,
                'temperature' => 0.50,
                'max_output_tokens' => 1024,
                'is_active' => true,
                'system_prompt' => 'You are an SEO specialist for Agricart ERP. Return concise SEO metadata only as JSON.',
                'user_prompt_template' => "Generate SEO title, meta description, focus keyword, and URL slug in {{language}} for:\n\n{{subject}}",
                'available_variables' => AiPromptVariableRegistry::forTaskType(AiTaskType::SeoGeneration),
            ],
            [
                'name' => 'Custom Prompt',
                'target_module' => AiTargetModule::System,
                'task_type' => AiTaskType::CustomPrompt,
                'output_format' => AiPromptOutputFormat::Text,
                'temperature' => null,
                'max_output_tokens' => null,
                'is_active' => true,
                'system_prompt' => 'You are a helpful assistant for Agricart ERP.',
                'user_prompt_template' => '{{prompt}}',
                'available_variables' => AiPromptVariableRegistry::forTaskType(AiTaskType::CustomPrompt),
            ],
        ];
    }
}
