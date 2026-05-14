<?php

declare(strict_types=1);

/** @var array $disciplines */
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="m-0">Gestione Discipline</h5>
      <form method="post" action="/seiryokukai_php/public/api/disciplina.php" class="d-flex gap-2">
        <input class="form-control" name="name" placeholder="Nome disciplina" required>
        <input class="form-control" name="notes" placeholder="Note (opzionale)">
        <button class="btn btn-success">Aggiungi</button>
      </form>
    </div>

    <div class="table-responsive">
      <table class="table align-middle js-datatable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Disciplina</th>
            <th>Note</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($disciplines as $discipline): ?>
            <tr>
              <td><?= (int) ($discipline['id'] ?? 0) ?></td>
              <td><?= htmlspecialchars((string) ($discipline['name'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($discipline['notes'] ?? '')) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
