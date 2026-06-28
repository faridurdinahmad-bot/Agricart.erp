<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storefront Base URL
    |--------------------------------------------------------------------------
    |
    | Used to build system-generated category canonical URLs.
    |
    */

    'storefront_base_url' => env('CATALOG_STOREFRONT_BASE_URL', 'https://agricart.pk'),

    /*
    |--------------------------------------------------------------------------
    | Category URL Path Prefix
    |--------------------------------------------------------------------------
    |
    | Canonical pattern: {storefront_base_url}/{category_path_prefix}/{slug}/{slug}
    |
    */

    'category_path_prefix' => env('CATALOG_CATEGORY_PATH_PREFIX', 'category'),

];
