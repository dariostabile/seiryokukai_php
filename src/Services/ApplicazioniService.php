<?php

declare(strict_types=1);

namespace App\Services;

final class ApplicazioniService extends BaseService
{
    public function readApplicationsCatalog(): array
    {
        $pdo = db_connection();
        $stmt = $pdo->query(
            "SELECT
                a.idapplicazione AS id,
                COALESCE(a.applicazione, '') AS name,
                COALESCE(a.url_applicazione, '') AS url,
                COALESCE(a.icona_applicazione, '') AS icon,
                COALESCE(a.ordine_applicazione, 0) AS app_order,
                COALESCE(ga.idgruppo_applicazioni, 0) AS group_id,
                COALESCE(ga.gruppo_applicazioni, 'Applicazioni') AS group_name,
                COALESCE(ga.icona_gruppo, '') AS group_icon,
                COALESCE(ga.ordine_gruppo, 0) AS group_order
             FROM applicazioni a
             INNER JOIN gruppi_applicazioni ga ON ga.idgruppo_applicazioni = a.idgruppo_applicazioni
             ORDER BY ga.ordine_gruppo ASC, a.ordine_applicazione ASC, a.idapplicazione ASC"
        );

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function readProfiles(): array
    {
        $pdo = db_connection();
        $stmt = $pdo->query(
            "SELECT
                idprofilo AS id,
                COALESCE(profilo, '') AS name
             FROM profili
             ORDER BY ordine_profilo ASC, idprofilo ASC"
        );

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }
}
