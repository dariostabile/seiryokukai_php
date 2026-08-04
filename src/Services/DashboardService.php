<?php

declare(strict_types=1);

namespace App\Services;

final class DashboardService extends BaseService
{
    public function dashboardStats(): array
    {
        $pdo = db_connection();

        $totalClients = (int) $pdo->query('SELECT COUNT(*) FROM atleti WHERE cancellato = 0')->fetchColumn();
        $activeClients = (int) $pdo->query('SELECT COUNT(*) FROM atleti WHERE cancellato = 0 AND attivo = 1')->fetchColumn();
        $pausedClients = (int) $pdo->query('SELECT COUNT(*) FROM atleti WHERE cancellato = 0 AND attivo = 0')->fetchColumn();
        $todayAttendance = (int) $pdo->query('SELECT COUNT(*) FROM presenze WHERE data_corso = CURDATE() AND presente = 1')->fetchColumn();

        return [
            'totalClients' => $totalClients,
            'activeClients' => $activeClients,
            'pausedClients' => $pausedClients,
            'todayAttendance' => $todayAttendance,
        ];
    }
}
