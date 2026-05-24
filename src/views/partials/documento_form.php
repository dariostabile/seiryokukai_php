<?php
// Form documento atleta riutilizzabile per inserimento e modifica
// Variabili richieste:
// $formAction, $formId, $submitLabel, $hiddenFields (array di input hidden), $tipiDocumenti (array), $values (array), $isEdit (bool)
?>
<form method="post" action="<?= htmlspecialchars($formAction) ?>" class="row g-3" enctype="multipart/form-data" id="<?= htmlspecialchars($formId) ?>">
  <?php foreach (($hiddenFields ?? []) as $hidden): ?>
    <?= $hidden ?>
  <?php endforeach; ?>
  <div class="col-12 col-md-4">
    <label class="form-label">File documento<?= $isEdit ? ' (opzionale)' : '' ?></label>
    <input class="form-control" type="file" name="document_file" accept="application/pdf,image/jpeg,image/png,image/webp">
    <small class="text-muted">Formati supportati: PDF, JPG, PNG, WEBP. Max 8MB.</small>
  </div>
  <div class="col-12 col-md-4">
    <label class="form-label">Tipo documento</label>
    <select class="form-select" name="idtipo_documento" <?= $isEdit ? 'id="editDocumentoType"' : '' ?> required>
      <option value="">Seleziona</option>
      <?php foreach ($tipiDocumenti as $tipoDocumento): ?>
        <option value="<?= (int) ($tipoDocumento['id'] ?? 0) ?>" <?= ((string)($values['idtipo_documento'] ?? '')) === (string)($tipoDocumento['id'] ?? '') ? 'selected' : '' ?>><?= htmlspecialchars((string) ($tipoDocumento['type'] ?? '')) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-12 col-md-4">
    <label class="form-label">Descrizione</label>
    <input class="form-control" name="descrizione_documento" <?= $isEdit ? 'id="editDocumentoDescription"' : '' ?> value="<?= htmlspecialchars((string)($values['descrizione_documento'] ?? '')) ?>">
  </div>
  <div class="col-12 col-md-4">
    <label class="form-label">Data rilascio</label>
    <input type="text" class="form-control js-date-ita" placeholder="gg/mm/aaaa" name="data_documento" <?= $isEdit ? 'id="editDocumentoDate"' : '' ?> value="<?= htmlspecialchars((string)($values['data_documento'] ?? '')) ?>">
  </div>
  <div class="col-12 col-md-4">
    <label class="form-label">Scadenza</label>
    <input type="text" class="form-control js-date-ita" placeholder="gg/mm/aaaa" name="data_scadenza" <?= $isEdit ? 'id="editDocumentoExpiryDate"' : '' ?> value="<?= htmlspecialchars((string)($values['data_scadenza'] ?? '')) ?>">
  </div>
  <div class="col-12 d-flex justify-content-end">
    <button class="btn btn-<?= $isEdit ? 'primary' : 'outline-primary' ?>" type="submit"><?= htmlspecialchars($submitLabel) ?></button>
  </div>
</form>
