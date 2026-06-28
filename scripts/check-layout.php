<?php

$cf = sys_get_temp_dir().'/ag_layout_check.txt';
@unlink($cf);

function req(string $url, string $cf, ?array $post = null): string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR => $cf,
        CURLOPT_COOKIEFILE => $cf,
    ]);
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $b = curl_exec($ch);
    curl_close($ch);

    return $b ?: '';
}

$login = req('http://127.0.0.1:8003/admin/login', $cf);
preg_match('/content="([^"]+)" name="csrf-token"/', $login, $m);
req('http://127.0.0.1:8003/admin/login', $cf, [
    'email' => 'admin@agricart.test',
    'password' => 'password',
    '_token' => $m[1],
]);

$dashboard = req('http://127.0.0.1:8003/admin', $cf);
$settings = req('http://127.0.0.1:8003/admin/settings/overview', $cf);

$checks = [
    'dashboard_sidebar' => str_contains($dashboard, 'fi-sidebar'),
    'dashboard_topbar' => str_contains($dashboard, 'fi-topbar'),
    'dashboard_widgets' => str_contains($dashboard, 'fi-wi'),
    'settings_sidebar' => str_contains($settings, 'fi-sidebar'),
    'settings_topbar' => str_contains($settings, 'fi-topbar'),
    'settings_tabs' => str_contains($settings, 'fi-tabs'),
    'settings_breadcrumbs' => str_contains($settings, 'fi-breadcrumbs'),
    'settings_coming_soon' => str_contains($settings, 'Coming Soon'),
    'theme_switcher' => str_contains($settings, 'fi-theme-switcher'),
    'dark_mode_script' => str_contains($settings, 'loadDarkMode'),
    'module_dashboard_nav' => str_contains($dashboard, 'Dashboard'),
    'module_settings_nav' => str_contains($dashboard, 'Settings'),
];

echo json_encode($checks, JSON_PRETTY_PRINT).PHP_EOL;
