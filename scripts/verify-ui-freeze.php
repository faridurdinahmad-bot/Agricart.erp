<?php

/**
 * Phase 1.5 UI freeze verification (read-only HTTP checks).
 * Run: php scripts/verify-ui-freeze.php [base_url]
 */
$baseUrl = $argv[1] ?? 'http://agricart.test:8002';

$routes = [
    '/admin',
    '/admin/settings/overview',
    '/admin/settings/store-setting',
    '/admin/settings/tax-system',
    '/admin/settings/purchase-pricing',
    '/admin/settings/ai-settings',
    '/admin/settings/ai-logs',
    '/admin/settings/printing',
    '/admin/settings/system',
    '/admin/settings/permission',
    '/admin/settings/users',
    '/admin/settings/backups',
];

$cookieFile = sys_get_temp_dir().'/agricart_verify_cookies.txt';
@unlink($cookieFile);

function request(string $url, string $cookieFile, ?array $post = null): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 30,
    ]);

    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['status' => $status, 'body' => $response ?: ''];
}

// Login
$loginPage = request("{$baseUrl}/admin/login", $cookieFile);
preg_match('/name="_token" value="([^"]+)"/', $loginPage['body'], $tokenMatch)
    || preg_match('/name="csrf-token" content="([^"]+)"/', $loginPage['body'], $tokenMatch)
    || preg_match('/content="([^"]+)" name="csrf-token"/', $loginPage['body'], $tokenMatch);
$token = $tokenMatch[1] ?? null;

if (! $token) {
    echo "FAIL: Could not extract CSRF token\n";
    exit(1);
}

request("{$baseUrl}/admin/login", $cookieFile, [
    'email' => 'admin@agricart.test',
    'password' => 'password',
    '_token' => $token,
]);

$results = [];
$failures = 0;

foreach ($routes as $route) {
    $response = request("{$baseUrl}{$route}", $cookieFile);
    $ok = $response['status'] === 200;
    $hasComingSoon = str_contains($response['body'], 'Coming Soon') || str_contains($response['body'], 'agricart-coming-soon');
    $hasWidgets = str_contains($response['body'], 'fi-wi-widget');
    $hasLayout = str_contains($response['body'], 'fi-sidebar')
        && (str_contains($response['body'], 'fi-topbar') || str_contains($response['body'], 'fi-topbar-ctn'));
    $hasSubNav = ! str_starts_with($route, '/admin/settings') || str_contains($response['body'], 'fi-tabs');
    $hasInter = stripos($response['body'], 'Inter Variable') !== false
        || stripos($response['body'], 'Instrument Sans') !== false
        || str_contains($response['body'], 'fonts.bunny.net');
    $hasArial = stripos($response['body'], 'Arial') !== false;

    if (! $ok || ! $hasLayout) {
        $failures++;
    }

    $results[] = [
        'route' => $route,
        'status' => $response['status'],
        'ok' => $ok,
        'layout' => $hasLayout,
        'sub_nav' => $hasSubNav,
        'placeholder' => $hasComingSoon || ($route === '/admin' && $hasWidgets),
        'no_external_fonts' => ! $hasInter,
        'has_arial' => $hasArial,
    ];
}

$loginCheck = request("{$baseUrl}/admin/login", $cookieFile);

echo json_encode([
    'base_url' => $baseUrl,
    'routes_tested' => count($routes),
    'failures' => $failures,
    'login_no_bunny' => ! str_contains($loginCheck['body'], 'fonts.bunny.net'),
    'login_no_inter' => stripos($loginCheck['body'], 'Inter Variable') === false,
    'results' => $results,
], JSON_PRETTY_PRINT).PHP_EOL;

exit($failures > 0 ? 1 : 0);
