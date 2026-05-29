<?php
declare(strict_types=1);
/**
 * Variabili attese:
 * - $corsoFormValues: array
 * - $corsoFormIsEdit: bool
 * - $corsoFieldIds: array
 * - $dayLabels: array<string,string>
 * - $discipline: array
 * - $users: array
 * - $sedi: array
 */
$corsoFormValues = is_array($corsoFormValues ?? null) ? $corsoFormValues : [];
$corsoFieldIds = is_array($corsoFieldIds ?? null) ? $corsoFieldIds : [];
$corsoFormIsEdit = (bool) ($corsoFormIsEdit ?? false);
?>
<div class="col-12 col-md-4">
  <label class="form-label">Immagine corso (JPG/PNG, max 2MB)</label>
  <input class="form-control" type="file" name="immagine_corso" accept="image/jpeg,image/png">
  <?php if ($corsoFormIsEdit && !empty($corsoFormValues['immagine_corso'])): ?>
    <div class="mt-2">
      <span class="text-muted small">Immagine attuale:</span><br>
      <img src="<?= htmlspecialchars($corsoFormValues['immagine_corso']) ?>" alt="Immagine corso" style="max-width:120px;max-height:120px;object-fit:contain;border:1px solid #ccc;">
    </div>
  <?php endif; ?>
</div>
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

<div class="col-12">
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

<div class="col-12 d-flex justify-content-end gap-2">
  <button class="btn btn-secondary" type="button" id="<?= $corsoFormIsEdit ? 'cancelEditCorsoBtn' : 'cancelAddCorsoBtn' ?>">Annulla</button>
  <button class="btn <?= $corsoFormIsEdit ? 'btn-primary' : 'btn-success' ?>" type="submit"><?= $corsoFormIsEdit ? 'Salva modifiche' : '+ Aggiungi Corso' ?></button>
</div>
