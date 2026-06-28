<?php

/**
 * Captures Phase 1.5 freeze screenshots (requires local server on :8003).
 * Run: php scripts/capture-freeze-screenshots.php
 */
$baseUrl = 'http://127.0.0.1:8003';
$outDir = __DIR__.'/../storage/app/phase1-screenshots';
$cookieFile = sys_get_temp_dir().'/agricart_screenshot_cookies.txt';

@mkdir($outDir, 0755, true);
@unlink($cookieFile);

function request(string $url, string $cookieFile, ?array $post = null): string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
    ]);

    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }

    $body = curl_exec($ch);
    curl_close($ch);

    return $body ?: '';
}

$login = request("{$baseUrl}/admin/login", $cookieFile);
preg_match('/name="_token" value="([^"]+)"/', $login, $match)
    || preg_match('/name="csrf-token" content="([^"]+)"/', $login, $match)
    || preg_match('/content="([^"]+)" name="csrf-token"/', $login, $match);
$token = $match[1] ?? null;

if (! $token) {
    fwrite(STDERR, 'Failed to login: no CSRF token (response length: '.strlen($login).")\n");
    exit(1);
}

request("{$baseUrl}/admin/login", $cookieFile, [
    'email' => 'admin@agricart.test',
    'password' => 'password',
    '_token' => $token,
]);

$cookieHeader = '';
foreach (file($cookieFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with($line, '#')) {
        continue;
    }
    $parts = explode("\t", $line);
    if (count($parts) >= 7) {
        $cookieHeader .= $parts[5].'='.$parts[6].'; ';
    }
}

$chrome = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
if (! file_exists($chrome)) {
    fwrite(STDERR, "Chrome not found\n");
    exit(1);
}

$shots = [
    ['file' => '01-dashboard-light-desktop.png', 'url' => '/admin', 'width' => 1440, 'height' => 900],
    ['file' => '02-settings-overview-light-desktop.png', 'url' => '/admin/settings/overview', 'width' => 1440, 'height' => 900],
    ['file' => '03-permission-light-desktop.png', 'url' => '/admin/settings/permission', 'width' => 1440, 'height' => 900],
    ['file' => '04-dashboard-light-tablet.png', 'url' => '/admin', 'width' => 768, 'height' => 1024],
    ['file' => '05-settings-overview-light-tablet.png', 'url' => '/admin/settings/overview', 'width' => 768, 'height' => 1024],
];

foreach ($shots as $shot) {
    $out = $outDir.DIRECTORY_SEPARATOR.$shot['file'];
    $url = $baseUrl.$shot['url'];

    $cmd = sprintf(
        '"%s" --headless=new --disable-gpu --no-sandbox --window-size=%d,%d --screenshot="%s" --header="Cookie: %s" "%s" 2>&1',
        $chrome,
        $shot['width'],
        $shot['height'],
        $out,
        rtrim($cookieHeader, '; '),
        $url
    );

    exec($cmd, $output, $code);
    echo ($code === 0 && file_exists($out) ? 'OK' : 'FAIL')." {$shot['file']}\n";
}

echo "Saved to {$outDir}\n";
