<?php

declare(strict_types=1);

/** @var array $sites */
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="m-0">Gestione Sedi</h5>
      <form method="post" action="/seiryokukai_php/public/api/sedi.php" class="d-flex gap-2">
        <input class="form-control" name="name" placeholder="Nome sede" required>
        <input class="form-control" name="code" placeholder="Codice sede (es. PALERMO)">
        <button class="btn btn-success">Aggiungi</button>
      </form>
    </div>

    <div class="table-responsive">
      <table class="table align-middle js-datatable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Sede</th>
            <th>Codice</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sites as $site): ?>
            <tr>
              <td><?= (int) ($site['id'] ?? 0) ?></td>
              <td><?= htmlspecialchars((string) ($site['name'] ?? '')) ?></td>
              <td>
                <span class="badge text-bg-primary">
                  <?= htmlspecialchars((string) ($site['code'] ?? '')) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
