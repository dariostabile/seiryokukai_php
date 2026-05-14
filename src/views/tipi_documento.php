<?php

declare(strict_types=1);

/** @var array $documentTypes */
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="m-0">Gestione Tipi Documento</h5>
      <form method="post" action="/seiryokukai_php/public/api/tipi_documento.php" class="d-flex gap-2">
        <input class="form-control" name="type" placeholder="Nuovo tipo documento" required>
        <button class="btn btn-success">Aggiungi</button>
      </form>
    </div>

    <div class="table-responsive">
      <table class="table align-middle js-datatable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Tipo Documento</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($documentTypes as $docType): ?>
            <tr>
              <td><?= (int) ($docType['id'] ?? 0) ?></td>
              <td><?= htmlspecialchars((string) ($docType['type'] ?? '')) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
