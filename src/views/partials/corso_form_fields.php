<?php
declare(strict_types=1);
// Variabili attese: $corsoFormValues, $corsoFormIsEdit, $corsoFieldIds, $dayLabels, $discipline, $users, $sedi
$corsoFormValues = is_array($corsoFormValues ?? null) ? $corsoFormValues : [];
$corsoFieldIds = is_array($corsoFieldIds ?? null) ? $corsoFieldIds : [];
$corsoFormIsEdit = (bool) ($corsoFormIsEdit ?? false);
// Calcolo iniziali per placeholder immagine
$corsoName = trim((string)($corsoFormValues['name'] ?? ''));
$corsoInitials = 'C';
if ($corsoName !== '') {
  $parts = preg_split('/\s+/', $corsoName) ?: [];
  $parts = array_values(array_filter($parts, static fn($p) => $p !== ''));
  if (count($parts) === 1) {
    $corsoInitials = strtoupper(substr($parts[0], 0, 2));
  } elseif (count($parts) >= 2) {
    $corsoInitials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
  }
}
$imgPath = (string)($corsoFormValues['image_path'] ?? '');
?>

<!-- FORM CORSO: immagine a sinistra, campi a destra -->
<div class="row g-3">
  <!-- Colonna immagine corso -->
  <div class="col-12 col-md-3">
    <div class="border rounded p-3 h-100">
      <div class="text-center mb-2">
        <img
          id="corsoImagePreview"
          src="<?= $imgPath ? '/seiryokukai_php/' . htmlspecialchars($imgPath) : '' ?>"
          data-initial-src="<?= $imgPath ? '/seiryokukai_php/' . htmlspecialchars($imgPath) : '' ?>"
          alt="Immagine corso"
          class="rounded-circle <?= $imgPath ? '' : 'd-none' ?>"
          style="width: 130px; height: 130px; object-fit: cover;"
        >
        <div id="corsoImagePlaceholder" class="rounded-circle border d-flex align-items-center justify-content-center mx-auto text-muted <?= $imgPath ? 'd-none' : '' ?>" style="width: 130px; height: 130px;">
          <?= htmlspecialchars($corsoInitials) ?>
        </div>
      </div>
      <label class="form-label" for="corsoImageInput">Immagine corso</label>
      <input class="form-control" type="file" id="corsoImageInput" name="immagine_corso" accept="image/jpeg,image/png">
      <input type="hidden" name="crop_image_base64" id="corsoCropImageBase64">
      <input type="hidden" name="current_immagine_corso" value="<?= htmlspecialchars($imgPath) ?>">
      <div id="corsoImageCropContainer" class="mt-3 d-none">
        <div class="border rounded p-2 bg-light">
          <img id="corsoImageCropSource" src="" alt="Ritaglio immagine corso" style="max-width: 100%; display: block;">
        </div>
        <div class="d-flex gap-2 mt-2">
          <button type="button" class="btn btn-sm btn-primary" id="corsoImageApplyCropBtn">Usa ritaglio</button>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="corsoImageCancelCropBtn">Annulla ritaglio</button>
        </div>
        <small class="text-muted">Trascina e zooma l'immagine, poi premi "Usa ritaglio".</small>
      </div>
      <small class="text-muted">Formati supportati: JPG, PNG (max 2MB)</small>
      <?php if ($corsoFormIsEdit): ?>
        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" name="remove_immagine_corso" id="removeImmagineCorsoCheckbox" value="1">
          <label class="form-check-label" for="removeImmagineCorsoCheckbox">Rimuovi immagine attuale</label>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Colonna campi corso -->
  <div class="col-12 col-md-9">
    <div class="row g-3">
      <div class="col-12 col-md-4">
        <label class="form-label">Stato</label>
        <select class="form-select" name="active" id="<?= htmlspecialchars((string) ($corsoFieldIds['active'] ?? '')) ?>">
          <option value="1" <?= (int) ($corsoFormValues['active'] ?? 1) === 1 ? 'selected' : '' ?>>Attivo</option>
          <option value="0" <?= (int) ($corsoFormValues['active'] ?? 1) === 0 ? 'selected' : '' ?>>Non attivo</option>
        </select>
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label">Data Inizio</label>
        <input class="form-control" type="date" name="start_date" id="<?= htmlspecialchars((string) ($corsoFieldIds['start_date'] ?? '')) ?>" value="<?= htmlspecialchars((string) ($corsoFormValues['start_date'] ?? '')) ?>">
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label">Data Fine</label>
        <input class="form-control" type="date" name="end_date" id="<?= htmlspecialchars((string) ($corsoFieldIds['end_date'] ?? '')) ?>" value="<?= htmlspecialchars((string) ($corsoFormValues['end_date'] ?? '')) ?>">
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label">Nome Corso</label>
        <input class="form-control" name="name" id="<?= htmlspecialchars((string) ($corsoFieldIds['name'] ?? '')) ?>" placeholder="Nome corso" required value="<?= htmlspecialchars((string) ($corsoFormValues['name'] ?? '')) ?>">
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label">Disciplina</label>
        <select class="form-select" name="disciplina_id" id="<?= htmlspecialchars((string) ($corsoFieldIds['disciplina_id'] ?? '')) ?>" required>
          <option value="">Seleziona disciplina</option>
          <?php foreach ($discipline as $disciplina): ?>
            <?php $disciplinaId = (int) ($disciplina['id'] ?? 0); ?>
            <option value="<?= $disciplinaId ?>" <?= $disciplinaId === (int) ($corsoFormValues['disciplina_id'] ?? 0) ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) ($disciplina['name'] ?? '')) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label">Istruttore</label>
        <select class="form-select" name="user_id" id="<?= htmlspecialchars((string) ($corsoFieldIds['user_id'] ?? '')) ?>" required>
          <option value="">Seleziona istruttore</option>
          <?php foreach ($users as $u): ?>
            <?php $userId = (int) ($u['id'] ?? 0); ?>
            <option value="<?= $userId ?>" <?= $userId === (int) ($corsoFormValues['user_id'] ?? 0) ? 'selected' : '' ?>>
              <?= htmlspecialchars((string) ($u['name'] ?? '')) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-3">
        <label class="form-label">Sede</label>
        <select class="form-select" name="sede_id" id="<?= htmlspecialchars((string) ($corsoFieldIds['sede_id'] ?? '')) ?>" required>
          <option value="">Seleziona sede</option>
          <?php foreach ($sedi as $sede): ?>
            <?php $sedeId = (int) ($sede['id'] ?? 0); ?>
            <?php $sedeAttiva = (int) ($sede['active'] ?? 1) === 1; ?>
            <option value="<?= $sedeId ?>" <?= $sedeId === (int) ($corsoFormValues['sede_id'] ?? 0) ? 'selected' : '' ?> <?= $sedeAttiva ? '' : 'disabled' ?>>
              <?= htmlspecialchars((string) ($sede['name'] ?? '')) ?><?= $sedeAttiva ? '' : ' (non attiva)' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-2">
        <label class="form-label">Quota Mensile</label>
        <input class="form-control" type="number" name="monthly_fee" id="<?= htmlspecialchars((string) ($corsoFieldIds['monthly_fee'] ?? '')) ?>" step="0.01" placeholder="Quota" value="<?= htmlspecialchars((string) ($corsoFormValues['monthly_fee'] ?? '')) ?>">
      </div>
    </div>

    <!-- Orari settimanali -->
    <div class="col-12 mt-3">
      <small class="text-muted">Orari settimanali:</small>
    </div>
    <?php foreach ($dayLabels as $dayKey => $dayLabel): ?>
      <div class="col-12 col-lg-8">
        <div class="row g-1 align-items-end">
          <div class="col-5 col-md-3">
            <label class="form-label mb-1"><?= htmlspecialchars($dayLabel) ?></label>
          </div>
          <div class="col-3 col-md-2">
            <input type="time" class="form-control" name="<?= htmlspecialchars($dayKey) ?>_inizio" id="<?= htmlspecialchars((string) ($corsoFieldIds[$dayKey . '_inizio'] ?? '')) ?>" value="<?= htmlspecialchars((string) ($corsoFormValues[$dayKey . '_inizio'] ?? '')) ?>">
          </div>
          <div class="col-3 col-md-2">
            <input type="time" class="form-control" name="<?= htmlspecialchars($dayKey) ?>_fine" id="<?= htmlspecialchars((string) ($corsoFieldIds[$dayKey . '_fine'] ?? '')) ?>" value="<?= htmlspecialchars((string) ($corsoFormValues[$dayKey . '_fine'] ?? '')) ?>">
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Pulsanti azione -->
<div class="col-12 d-flex justify-content-end gap-2 mt-3">
  <button class="btn btn-secondary" type="button" id="<?= $corsoFormIsEdit ? 'cancelEditCorsoBtn' : 'cancelAddCorsoBtn' ?>">Annulla</button>
  <button class="btn <?= $corsoFormIsEdit ? 'btn-primary' : 'btn-success' ?>" type="submit"><?= $corsoFormIsEdit ? 'Salva modifiche' : '+ Aggiungi Corso' ?></button>
</div>



<div class="col-12 d-flex justify-content-end gap-2">
  <button class="btn btn-secondary" type="button" id="<?= $corsoFormIsEdit ? 'cancelEditCorsoBtn' : 'cancelAddCorsoBtn' ?>">Annulla</button>
  <button class="btn <?= $corsoFormIsEdit ? 'btn-primary' : 'btn-success' ?>" type="submit"><?= $corsoFormIsEdit ? 'Salva modifiche' : '+ Aggiungi Corso' ?></button>
</div>
