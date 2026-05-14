<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';

final class NavigationService
{
    public function mapApplicationToPage(string $urlApplicazione): ?string
    {
        return match ($urlApplicazione) {
            'dashboard' => 'dashboard',
            'atleti' => 'atleti',
            'utenti' => 'utenti',
            'sedi' => 'sedi',
            'tipi_documento' => 'tipi_documento',
            'disciplina' => 'disciplina',
            'corsi' => 'corsi',
            default => null,
        };
    }

    public function navigationMenuForUser(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'SELECT
                ga.gruppo_applicazioni,
                ga.icona_gruppo,
                ga.ordine_gruppo,
                a.applicazione,
                a.url_applicazione,
                a.ordine_applicazione,
                a.icona_applicazione
             FROM utenti_has_applicazioni ua
             INNER JOIN applicazioni a ON a.idapplicazione = ua.idapplicazione
             INNER JOIN gruppi_applicazioni ga ON ga.idgruppo_applicazioni = a.idgruppo_applicazioni
             WHERE ua.idutente = :idutente
             ORDER BY ga.ordine_gruppo ASC, a.ordine_applicazione ASC, a.idapplicazione ASC'
        );
        $stmt->execute(['idutente' => $userId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!is_array($rows) || $rows === []) {
            return [];
        }

        $groups = [];

        foreach ($rows as $row) {
            $groupName = trim((string) ($row['gruppo_applicazioni'] ?? 'Applicazioni'));
            $appName = trim((string) ($row['applicazione'] ?? 'Modulo'));
            $appUrl = trim((string) ($row['url_applicazione'] ?? ''));
            $appIcon = trim((string) ($row['icona_applicazione'] ?? 'fa-solid fa-circle'));
            $page = $this->mapApplicationToPage($appUrl);

            if (!isset($groups[$groupName])) {
                $groups[$groupName] = [
                    'label' => $groupName,
                    'icon' => trim((string) ($row['icona_gruppo'] ?? 'fa-solid fa-layer-group')),
                    'items' => [],
                ];
            }

            $groups[$groupName]['items'][] = [
                'label' => $appName,
                'icon' => $appIcon !== '' ? $appIcon : 'fa-solid fa-circle',
                'page' => $page,
                'enabled' => $page !== null,
            ];
        }

        return array_values($groups);
    }
}

function navigation_service(): NavigationService
{
    static $service = null;

    if (!$service instanceof NavigationService) {
        $service = new NavigationService();
    }

    return $service;
}
