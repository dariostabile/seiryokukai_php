<?php

declare(strict_types=1);

/**
 * Variabili attese:
 * - $disciplinaFormValues: array{name:string,notes:string}
 * - $disciplinaFormIsEdit: bool
 * - $disciplinaFieldIds: array{name:string,notes:string}
 * - $disciplinaCancelButtonId: string|null
 * - $disciplinaSubmitLabel: string|null
 * - $disciplinaSubmitClass: string|null
 */

$disciplinaFormValues = is_array($disciplinaFormValues ?? null) ? $disciplinaFormValues : [];
$disciplinaFieldIds = is_array($disciplinaFieldIds ?? null) ? $disciplinaFieldIds : [];
$disciplinaFormIsEdit = (bool) ($disciplinaFormIsEdit ?? false);
$disciplinaCancelButtonId = (string) ($disciplinaCancelButtonId ?? ($disciplinaFormIsEdit ? 'cancelEditDisciplinaBtn' : 'cancelAddDisciplinaBtn'));
$disciplinaSubmitLabel = (string) ($disciplinaSubmitLabel ?? ($disciplinaFormIsEdit ? 'Salva modifiche' : '+ Aggiungi Disciplina'));
$disciplinaSubmitClass = (string) ($disciplinaSubmitClass ?? ($disciplinaFormIsEdit ? 'btn-warning' : 'btn-success'));
?>
<div class="col-12">
  <label class="form-label">Nome Disciplina</label>
  <input class="form-control" name="name" id="<?= htmlspecialchars((string) ($disciplinaFieldIds['name'] ?? '')) ?>" placeholder="Nome della disciplina" required value="<?= htmlspecialchars((string) ($disciplinaFormValues['name'] ?? '')) ?>">
</div>

<div class="col-12">
  <label class="form-label">Note</label>
  <textarea class="form-control" name="notes" id="<?= htmlspecialchars((string) ($disciplinaFieldIds['notes'] ?? '')) ?>" placeholder="Note (opzionale)" rows="3"><?= htmlspecialchars((string) ($disciplinaFormValues['notes'] ?? '')) ?></textarea>
</div>

<div class="col-12 d-flex justify-content-end gap-2">
  <button class="btn btn-secondary" type="button" id="<?= htmlspecialchars($disciplinaCancelButtonId) ?>">Annulla</button>
  <button class="btn <?= htmlspecialchars($disciplinaSubmitClass) ?>" type="submit"><?= htmlspecialchars($disciplinaSubmitLabel) ?></button>
</div>
