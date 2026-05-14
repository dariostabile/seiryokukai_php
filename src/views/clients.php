<?php

declare(strict_types=1);

/** @var array $clients */
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="m-0">Anagrafica Atleti</h5>
      <form method="post" action="/seiryokukai_php/public/api/atleti.php" class="d-flex gap-2">
        <input class="form-control" name="name" placeholder="Nome e cognome atleta" required>
        <button class="btn btn-success">Aggiungi</button>
      </form>
    </div>

    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Atleta</th>
            <th>Email</th>
            <th>Telefono</th>
            <th>Stato</th>
            <th class="text-end">Azioni</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($clients as $client): ?>
            <?php $isActive = ($client['status'] ?? '') === 'Attivo'; ?>
            <tr>
              <td><?= (int) $client['id'] ?></td>
              <td><?= htmlspecialchars((string) $client['name']) ?></td>
              <td><?= htmlspecialchars((string) ($client['email'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($client['phone'] ?? '')) ?></td>
              <td>
                <span class="badge text-bg-<?= $isActive ? 'success' : 'secondary' ?>">
                  <?= htmlspecialchars((string) $client['status']) ?>
                </span>
              </td>
              <td>
                <div class="d-flex justify-content-end gap-2">
                  <form method="post" action="/seiryokukai_php/public/api/atleti.php">
                    <input type="hidden" name="action" value="status">
                    <input type="hidden" name="id" value="<?= (int) $client['id'] ?>">
                    <input type="hidden" name="status" value="<?= $isActive ? 'Sospeso' : 'Attivo' ?>">
                    <button class="btn btn-sm <?= $isActive ? 'btn-outline-warning' : 'btn-outline-success' ?>" type="submit">
                      <?= $isActive ? 'Sospendi' : 'Attiva' ?>
                    </button>
                  </form>
                  <form method="post" action="/seiryokukai_php/public/api/atleti.php" onsubmit="return confirm('Eliminare questo cliente?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $client['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" type="submit">Elimina</button>
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
