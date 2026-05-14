<?php

declare(strict_types=1);

namespace App\Services;

final class AutenticazioneService
{
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user']);
    }

    public function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public function attemptLogin(string $username, string $password): bool
    {
        $pdo = db_connection();
        $stmt = $pdo->prepare(
            'SELECT u.idutente, u.username, u.nome, u.cognome, u.password, u.superadmin, p.profilo
             FROM utenti u
             LEFT JOIN utenti_has_profili up ON up.idutente = u.idutente
             LEFT JOIN profili p ON p.idprofilo = up.idprofilo
             WHERE u.username = :username
                 AND u.attivo = 1
                 AND u.cancellato = 0
             ORDER BY up.idprofilo ASC
             LIMIT 1'
        );
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!is_array($row)) {
            return false;
        }

        $storedHash = (string) ($row['password'] ?? '');
        $sha256Hex = hash('sha256', $password);
        $isBcrypt = str_starts_with($storedHash, '$2y$') || str_starts_with($storedHash, '$2a$') || str_starts_with($storedHash, '$2b$');
        $passwordValid = $isBcrypt
            ? password_verify($password, $storedHash)
            : hash_equals($storedHash, $sha256Hex);

        if (!$passwordValid) {
            return false;
        }

        $fullName = trim((string) (($row['nome'] ?? '') . ' ' . ($row['cognome'] ?? '')));
        $role = (string) ($row['profilo'] ?? 'Utente');

        if ((int) ($row['superadmin'] ?? 0) === 1) {
            $role = 'Superadmin';
        }

        $_SESSION['user'] = [
            'id' => (int) ($row['idutente'] ?? 0),
            'username' => (string) ($row['username'] ?? ''),
            'name' => $fullName !== '' ? $fullName : (string) ($row['username'] ?? 'Utente'),
            'role' => $role,
        ];

        return true;
    }

    public function logoutUser(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }
}
