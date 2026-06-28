<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default connection parameters
    |--------------------------------------------------------------------------
    |
    | Used when creating connections and as fallbacks in the admin form.
    |
    */
    'defaults' => [
        'context_window' => (int) env('AI_DEFAULT_CONTEXT_WINDOW', 128000),
        'max_output_tokens' => (int) env('AI_DEFAULT_MAX_OUTPUT_TOKENS', 4096),
        'temperature' => (float) env('AI_DEFAULT_TEMPERATURE', 0.7),
        'timeout' => (int) env('AI_DEFAULT_TIMEOUT', 60),
        'retry_count' => (int) env('AI_DEFAULT_RETRY_COUNT', 2),
        'connection_test_max_tokens' => (int) env('AI_CONNECTION_TEST_MAX_TOKENS', 16),
    ],

    /*
    |--------------------------------------------------------------------------
    | Model catalogue limits
    |--------------------------------------------------------------------------
    */
    'models' => [
        'max_stored' => (int) env('AI_MAX_STORED_MODELS', 500),
        'picker_preview_limit' => (int) env('AI_PICKER_PREVIEW_LIMIT', 80),
    ],

    /*
    |--------------------------------------------------------------------------
    | Task safety limits
    |--------------------------------------------------------------------------
    */
    'tasks' => [
        'custom_prompt_max_length' => (int) env('AI_CUSTOM_PROMPT_MAX_LENGTH', 8000),
        'context_snapshot_max_string_length' => (int) env('AI_CONTEXT_SNAPSHOT_MAX_LENGTH', 500),
    ],

    'prompts' => [
        'system_max_length' => (int) env('AI_PROMPT_SYSTEM_MAX_LENGTH', 12000),
        'user_max_length' => (int) env('AI_PROMPT_USER_MAX_LENGTH', 24000),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP client
    |--------------------------------------------------------------------------
    */
    'http' => [
        'retry_backoff_ms' => (int) env('AI_HTTP_RETRY_BACKOFF_MS', 500),
        'retry_on_status' => [429, 500, 502, 503, 504],
    ],

    /*
    |--------------------------------------------------------------------------
    | Endpoint security
    |--------------------------------------------------------------------------
    |
    | When true, base URLs pointing to private/reserved networks are rejected.
    |
    */
    'block_private_endpoints' => (bool) env('AI_BLOCK_PRIVATE_ENDPOINTS', true),

];
