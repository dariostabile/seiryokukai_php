<?php

declare(strict_types=1);

/** @var array $clients */
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="m-0">Anagrafica Clienti</h5>
      <form method="post" action="/seiryokukai_php/public/api/clients.php" class="d-flex gap-2">
        <input class="form-control" name="name" placeholder="Nome cliente" required>
        <select name="plan" class="form-select" required>
          <option value="Mensile">Mensile</option>
          <option value="Trimestrale">Trimestrale</option>
          <option value="Annuale">Annuale</option>
        </select>
        <button class="btn btn-success">Aggiungi</button>
      </form>
    </div>

    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Piano</th>
            <th>Stato</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($clients as $client): ?>
            <tr>
              <td><?= (int) $client['id'] ?></td>
              <td><?= htmlspecialchars((string) $client['name']) ?></td>
              <td><?= htmlspecialchars((string) $client['plan']) ?></td>
              <td>
                <span class="badge text-bg-<?= ($client['status'] ?? '') === 'Attivo' ? 'success' : 'secondary' ?>">
                  <?= htmlspecialchars((string) $client['status']) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
