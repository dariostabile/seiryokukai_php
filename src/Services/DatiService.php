<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Facade service che delega ai service specifici per entità.
 * Mantiene retrocompatibilità con il codice esistente.
 */
final class DatiService
{
    private AtletiService $atletiService;
    private UtentiService $utentiService;
    private SediService $sediService;
    private TipiDocumentoService $tipiDocumentoService;
    private DisciplineService $disciplinaService;
    private CorsiService $corsiService;
    private ApplicazioniService $applicazioniService;
    private DashboardService $dashboardService;

    public function __construct()
    {
        $this->atletiService = new AtletiService();
        $this->utentiService = new UtentiService();
        $this->sediService = new SediService();
        $this->tipiDocumentoService = new TipiDocumentoService();
        $this->disciplinaService = new DisciplineService();
        $this->corsiService = new CorsiService();
        $this->applicazioniService = new ApplicazioniService();
        $this->dashboardService = new DashboardService();
    }

    // ============ ATLETI ============

    public function readAtleti(): array
    {
        return $this->atletiService->readAtleti();
    }

    public function readAtletiPage(int $start, int $length, string $search, string $orderColumn, string $orderDir): array
    {
        return $this->atletiService->readAtletiPage($start, $length, $search, $orderColumn, $orderDir);
    }

    public function addAtleta(string $name, string $plan = ''): array
    {
        return $this->atletiService->addAtleta($name, $plan);
    }

    public function findAtletaById(int $id): ?array
    {
        return $this->atletiService->findAtletaById($id);
    }

    public function updateAtletaStatus(int $id, string $status): bool
    {
        return $this->atletiService->updateAtletaStatus($id, $status);
    }

    public function deleteAtleta(int $id): bool
    {
        return $this->atletiService->deleteAtleta($id);
    }

    // ============ UTENTI ============

    public function readUsers(): array
    {
        return $this->utentiService->readUsers();
    }

    public function readActiveInstructors(): array
    {
        return $this->utentiService->readActiveInstructors();
    }

    public function readUsersPage(int $start, int $length, string $search, string $orderColumn, string $orderDir): array
    {
        return $this->utentiService->readUsersPage($start, $length, $search, $orderColumn, $orderDir);
    }

    public function findUserById(int $id): ?array
    {
        return $this->utentiService->findUserById($id);
    }

    public function addUser(
        string $nome,
        string $cognome,
        string $username,
        string $password,
        string $email = '',
        string $phone1 = '',
        string $phone2 = '',
        string $email2 = '',
        array $profileIds = [],
        bool $attivo = true,
        string $accountExpiryDate = '',
        array $applicationIds = []
    ): array {
        return $this->utentiService->addUser(
            $nome,
            $cognome,
            $username,
            $password,
            $email,
            $phone1,
            $phone2,
            $email2,
            $profileIds,
            $attivo,
            $accountExpiryDate,
            $applicationIds
        );
    }

    public function updateUserStatus(int $id, string $status): bool
    {
        return $this->utentiService->updateUserStatus($id, $status);
    }

    public function updateUserImage(int $id, ?string $imagePath): bool
    {
        return $this->utentiService->updateUserImage($id, $imagePath);
    }

    public function updateUser(
        int $id,
        string $nome,
        string $cognome,
        string $username,
        string $email = '',
        string $phone1 = '',
        string $phone2 = '',
        string $email2 = '',
        array $profileIds = [],
        bool $attivo = true,
        ?string $imagePath = null,
        ?string $newPassword = null,
        ?array $applicationIds = null,
        string $accountExpiryDate = ''
    ): bool {
        return $this->utentiService->updateUser(
            $id,
            $nome,
            $cognome,
            $username,
            $email,
            $phone1,
            $phone2,
            $email2,
            $profileIds,
            $attivo,
            $imagePath,
            $newPassword,
            $applicationIds,
            $accountExpiryDate
        );
    }

    public function deleteUser(int $id): bool
    {
        return $this->utentiService->deleteUser($id);
    }

    // ============ APPLICAZIONI ============

    public function readApplicationsCatalog(): array
    {
        return $this->applicazioniService->readApplicationsCatalog();
    }

    public function readProfiles(): array
    {
        return $this->applicazioniService->readProfiles();
    }

    // ============ SEDI ============

    public function readSedi(): array
    {
        return $this->sediService->readSedi();
    }

    public function addSede(string $name, string $code, int $active = 1): array
    {
        return $this->sediService->addSede($name, $code, $active);
    }

    // ============ TIPI DOCUMENTO ============

    public function readTipiDocumenti(): array
    {
        return $this->tipiDocumentoService->readTipiDocumenti();
    }

    public function addTipoDocumento(string $type): array
    {
        return $this->tipiDocumentoService->addTipoDocumento($type);
    }

    // ============ DISCIPLINE ============

    public function readDiscipline(): array
    {
        return $this->disciplinaService->readDiscipline();
    }

    public function addDisciplina(string $name, string $notes = ''): array
    {
        return $this->disciplinaService->addDisciplina($name, $notes);
    }

    // ============ CORSI ============

    public function readCorsi(): array
    {
        return $this->corsiService->readCorsi();
    }

    public function readCorsiPage(int $start, int $length, string $search, string $orderColumn, string $orderDir, bool $activeOnly = false): array
    {
        return $this->corsiService->readCorsiPage($start, $length, $search, $orderColumn, $orderDir, $activeOnly);
    }

    public function addCorso(
        int $sedeId,
        int $disciplinaId,
        int $userId,
        string $name,
        ?string $startDate = null,
        ?string $endDate = null,
        ?float $monthlyFee = null,
        int $active = 1,
        array $orari = []
    ): array {
        return $this->corsiService->addCorso($sedeId, $disciplinaId, $userId, $name, $startDate, $endDate, $monthlyFee, $active, $orari);
    }

    public function readCorsoById(int $id): ?array
    {
        return $this->corsiService->readCorsoById($id);
    }

    public function updateCorso(
        int $id,
        int $sedeId,
        int $disciplinaId,
        int $userId,
        string $name,
        ?string $startDate = null,
        ?string $endDate = null,
        ?float $monthlyFee = null,
        int $active = 1,
        array $orari = []
    ): bool {
        return $this->corsiService->updateCorso($id, $sedeId, $disciplinaId, $userId, $name, $startDate, $endDate, $monthlyFee, $active, $orari);
    }

    public function deleteCorso(int $id): bool
    {
        return $this->corsiService->deleteCorso($id);
    }

    // ============ DASHBOARD ============

    public function dashboardStats(): array
    {
        return $this->dashboardService->dashboardStats();
    }
}
