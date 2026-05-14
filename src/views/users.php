<?php

declare(strict_types=1);

/** @var array $users */
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="m-0">Gestione Utenti</h5>
    </div>

    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Username</th>
            <th>Email</th>
            <th>Profilo</th>
            <th>Stato</th>
            <th class="text-end">Azioni</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <?php $isActive = ($u['status'] ?? '') === 'Attivo'; ?>
            <tr>
              <td><?= (int) ($u['id'] ?? 0) ?></td>
              <td><?= htmlspecialchars((string) ($u['name'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($u['username'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($u['email'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($u['role'] ?? '')) ?></td>
              <td>
                <span class="badge text-bg-<?= $isActive ? 'success' : 'secondary' ?>">
                  <?= htmlspecialchars((string) ($u['status'] ?? '')) ?>
                </span>
              </td>
              <td>
                <div class="d-flex justify-content-end gap-2">
                  <form method="post" action="/seiryokukai_php/public/api/utenti.php">
                    <input type="hidden" name="action" value="status">
                    <input type="hidden" name="id" value="<?= (int) ($u['id'] ?? 0) ?>">
                    <input type="hidden" name="status" value="<?= $isActive ? 'Sospeso' : 'Attivo' ?>">
                    <button class="btn btn-sm <?= $isActive ? 'btn-outline-warning' : 'btn-outline-success' ?>" type="submit">
                      <?= $isActive ? 'Sospendi' : 'Attiva' ?>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
