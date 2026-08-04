<?php

declare(strict_types=1);

/**
 * Variabili attese:
 * - $sedeFormValues: array{name:string,code:string,active:int}
 * - $sedeFormIsEdit: bool
 * - $sedeFieldIds: array{name:string,code:string,active:string}
 * - $sedeCancelButtonId: string|null
 * - $sedeSubmitLabel: string|null
 * - $sedeSubmitClass: string|null
 */

$sedeFormValues = is_array($sedeFormValues ?? null) ? $sedeFormValues : [];
$sedeFieldIds = is_array($sedeFieldIds ?? null) ? $sedeFieldIds : [];
$sedeFormIsEdit = (bool) ($sedeFormIsEdit ?? false);
$sedeCancelButtonId = (string) ($sedeCancelButtonId ?? ($sedeFormIsEdit ? 'cancelEditSiteBtn' : 'cancelAddSiteBtn'));
$sedeSubmitLabel = (string) ($sedeSubmitLabel ?? ($sedeFormIsEdit ? 'Salva modifiche' : '+ Aggiungi Sede'));
$sedeSubmitClass = (string) ($sedeSubmitClass ?? ($sedeFormIsEdit ? 'btn-warning' : 'btn-success'));
?>
<div class="col-12 col-md-6">
  <label class="form-label">Nome Sede</label>
  <input class="form-control" name="name" id="<?= htmlspecialchars((string) ($sedeFieldIds['name'] ?? '')) ?>" placeholder="Nome della sede" required value="<?= htmlspecialchars((string) ($sedeFormValues['name'] ?? '')) ?>">
</div>

<div class="col-12 col-md-6">
  <label class="form-label">Codice Sede</label>
  <input class="form-control" name="code" id="<?= htmlspecialchars((string) ($sedeFieldIds['code'] ?? '')) ?>" placeholder="Codice (es. PALERMO)" value="<?= htmlspecialchars((string) ($sedeFormValues['code'] ?? '')) ?>">
  <small class="text-muted">Se lasciato vuoto, verra generato dal nome.</small>
</div>

<div class="col-12 col-md-6">
  <label class="form-label">Stato</label>
  <select class="form-select" name="active" id="<?= htmlspecialchars((string) ($sedeFieldIds['active'] ?? '')) ?>">
    <option value="1" <?= (int) ($sedeFormValues['active'] ?? 1) === 1 ? 'selected' : '' ?>>Attiva</option>
    <option value="0" <?= (int) ($sedeFormValues['active'] ?? 1) === 0 ? 'selected' : '' ?>>Non attiva</option>
  </select>
</div>

<div class="col-12 d-flex justify-content-end gap-2">
  <button class="btn btn-secondary" type="button" id="<?= htmlspecialchars($sedeCancelButtonId) ?>">Annulla</button>
  <button class="btn <?= htmlspecialchars($sedeSubmitClass) ?>" type="submit"><?= htmlspecialchars($sedeSubmitLabel) ?></button>
</div>
