<?php

declare(strict_types=1);

function env_value(string $key, ?string $default = null): ?string
{
    static $loaded = false;

    if (!$loaded) {
        $envPath = __DIR__ . '/.env';
        if (is_file($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $line, 2);
                $_ENV[trim($k)] = trim($v);
            }
        }
        $loaded = true;
    }

    return $_ENV[$key] ?? getenv($key) ?: $default;
}

function db_connection(): \PDO
{
    static $pdo = null;

    if ($pdo instanceof \PDO) {
        return $pdo;
    }

    $host = env_value('DB_HOST', '127.0.0.1');
    $port = env_value('DB_PORT', '8889');
    $name = env_value('DB_NAME', 'seiryokukai');
    $user = env_value('DB_USER', 'root');
    $pass = env_value('DB_PASS', 'root');

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $name);

    try {
        $pdo = new \PDO($dsn, (string) $user, (string) $pass, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);
    } catch (\PDOException $e) {
        throw new RuntimeException('Connessione database fallita: ' . $e->getMessage(), 0, $e);
    }

    return $pdo;
}

function frontend_asset_urls(): array
{
    static $urls = null;

    if (is_array($urls)) {
        return $urls;
    }

    $urls = [
        'font_preconnect_api' => 'https://fonts.googleapis.com',
        'font_preconnect_static' => 'https://fonts.gstatic.com',
        'font_stylesheet' => 'https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;700&display=swap',
        'bootstrap_css' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
        'bootstrap_js' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
        'datatables_css_bootstrap' => 'https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css',
        'datatables_js_core' => 'https://cdn.datatables.net/2.0.8/js/dataTables.min.js',
        'datatables_js_bootstrap' => 'https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js',
        'datatables_i18n_it' => 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/it-IT.json',
        'jquery_js' => 'https://code.jquery.com/jquery-3.7.1.min.js',
        'fontawesome_css' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
    ];

    return $urls;
}

function frontend_api_urls(): array
{
    static $urls = null;

    if (is_array($urls)) {
        return $urls;
    }

    $base = '/seiryokukai_php/public/api';
    $urls = [
        'atleti' => $base . '/atleti.php',
        'corsi' => $base . '/corsi.php',
        'discipline' => $base . '/disciplina.php',
        'login' => $base . '/login.php',
        'sedi' => $base . '/sedi.php',
        'tipi_documento' => $base . '/tipi_documento.php',
        'utenti' => $base . '/utenti.php',
    ];

    return $urls;
}
