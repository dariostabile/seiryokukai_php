<?php

declare(strict_types=1);

function clients_file_path(): string
{
    return __DIR__ . '/../../storage/clients.json';
}

function read_clients(): array
{
    $file = clients_file_path();

    if (!is_file($file)) {
        $seed = [
            ['id' => 1, 'name' => 'Mario Rossi', 'plan' => 'Mensile', 'status' => 'Attivo'],
            ['id' => 2, 'name' => 'Laura Bianchi', 'plan' => 'Trimestrale', 'status' => 'Attivo'],
            ['id' => 3, 'name' => 'Giulia Verdi', 'plan' => 'Mensile', 'status' => 'Sospeso'],
        ];
        file_put_contents($file, json_encode($seed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    $raw = file_get_contents($file);
    $decoded = json_decode($raw ?: '[]', true);

    return is_array($decoded) ? $decoded : [];
}

function write_clients(array $clients): void
{
    file_put_contents(clients_file_path(), json_encode(array_values($clients), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function dashboard_stats(): array
{
    $clients = read_clients();

    $active = array_values(array_filter($clients, static fn ($c) => ($c['status'] ?? '') === 'Attivo'));
    $paused = array_values(array_filter($clients, static fn ($c) => ($c['status'] ?? '') === 'Sospeso'));

    return [
        'totalClients' => count($clients),
        'activeClients' => count($active),
        'pausedClients' => count($paused),
        'todayAttendance' => 18,
    ];
}

function add_client(string $name, string $plan): array
{
    $clients = read_clients();
    $nextId = empty($clients) ? 1 : (max(array_column($clients, 'id')) + 1);

    $newClient = [
        'id' => $nextId,
        'name' => $name,
        'plan' => $plan,
        'status' => 'Attivo',
    ];

    $clients[] = $newClient;
    write_clients($clients);

    return $newClient;
}
