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
    private DisciplineService $disciplineService;
    private CorsiService $corsiService;
    private ApplicazioniService $applicazioniService;
    private DashboardService $dashboardService;

    public function __construct()
    {
        $this->atletiService = new AtletiService();
        $this->utentiService = new UtentiService();
        $this->sediService = new SediService();
        $this->tipiDocumentoService = new TipiDocumentoService();
        $this->disciplineService = new DisciplineService();
        $this->corsiService = new CorsiService();
        $this->applicazioniService = new ApplicazioniService();
        $this->dashboardService = new DashboardService();
    }

    // ============ ATLETI ============

    public function readClients(): array
    {
        return $this->atletiService->readClients();
    }

    public function readClientsPage(int $start, int $length, string $search, string $orderColumn, string $orderDir): array
    {
        return $this->atletiService->readClientsPage($start, $length, $search, $orderColumn, $orderDir);
    }

    public function addClient(string $name, string $plan = ''): array
    {
        return $this->atletiService->addClient($name, $plan);
    }

    public function findClientById(int $id): ?array
    {
        return $this->atletiService->findClientById($id);
    }

    public function updateClientStatus(int $id, string $status): bool
    {
        return $this->atletiService->updateClientStatus($id, $status);
    }

    public function deleteClient(int $id): bool
    {
        return $this->atletiService->deleteClient($id);
    }

    // ============ UTENTI ============

    public function readUsers(): array
    {
        return $this->utentiService->readUsers();
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

    public function readSites(): array
    {
        return $this->sediService->readSites();
    }

    public function addSite(string $name, string $code, int $active = 1): array
    {
        return $this->sediService->addSite($name, $code, $active);
    }

    // ============ TIPI DOCUMENTO ============

    public function readDocumentTypes(): array
    {
        return $this->tipiDocumentoService->readDocumentTypes();
    }

    public function addDocumentType(string $type): array
    {
        return $this->tipiDocumentoService->addDocumentType($type);
    }

    // ============ DISCIPLINE ============

    public function readDisciplines(): array
    {
        return $this->disciplineService->readDisciplines();
    }

    public function addDiscipline(string $name, string $notes = ''): array
    {
        return $this->disciplineService->addDiscipline($name, $notes);
    }

    // ============ CORSI ============

    public function readCourses(): array
    {
        return $this->corsiService->readCourses();
    }

    public function readCoursesPage(int $start, int $length, string $search, string $orderColumn, string $orderDir, bool $activeOnly = false): array
    {
        return $this->corsiService->readCoursesPage($start, $length, $search, $orderColumn, $orderDir, $activeOnly);
    }

    public function addCourse(
        int $siteId,
        int $disciplineId,
        int $userId,
        string $name,
        ?string $startDate = null,
        ?string $endDate = null,
        ?float $monthlyFee = null,
        int $active = 1,
        array $orari = []
    ): array {
        return $this->corsiService->addCourse($siteId, $disciplineId, $userId, $name, $startDate, $endDate, $monthlyFee, $active, $orari);
    }

    public function readCourseById(int $id): ?array
    {
        return $this->corsiService->readCourseById($id);
    }

    public function updateCourse(
        int $id,
        int $siteId,
        int $disciplineId,
        int $userId,
        string $name,
        ?string $startDate = null,
        ?string $endDate = null,
        ?float $monthlyFee = null,
        int $active = 1,
        array $orari = []
    ): bool {
        return $this->corsiService->updateCourse($id, $siteId, $disciplineId, $userId, $name, $startDate, $endDate, $monthlyFee, $active, $orari);
    }

    public function deleteCourse(int $id): bool
    {
        return $this->corsiService->deleteCourse($id);
    }

    // ============ DASHBOARD ============

    public function dashboardStats(): array
    {
        return $this->dashboardService->dashboardStats();
    }
}
