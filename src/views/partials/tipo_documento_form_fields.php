<?php

declare(strict_types=1);

/**
 * Variabili attese:
 * - $tipoDocumentoFormValue: string
 * - $tipoDocumentoFormIsEdit: bool
 * - $tipoDocumentoFieldId: string
 * - $tipoDocumentoCancelButtonId: string|null
 * - $tipoDocumentoSubmitLabel: string|null
 * - $tipoDocumentoSubmitClass: string|null
 */

$tipoDocumentoFormValue = (string) ($tipoDocumentoFormValue ?? '');
$tipoDocumentoFieldId = (string) ($tipoDocumentoFieldId ?? '');
$tipoDocumentoFormIsEdit = (bool) ($tipoDocumentoFormIsEdit ?? false);
$tipoDocumentoCancelButtonId = (string) ($tipoDocumentoCancelButtonId
  ?? ($tipoDocumentoFormIsEdit ? 'cancelEditTipoDocumentoBtn' : 'cancelAddTipoDocumentoBtn'));
$tipoDocumentoSubmitLabel = (string) ($tipoDocumentoSubmitLabel
  ?? ($tipoDocumentoFormIsEdit ? 'Salva modifiche' : '+ Aggiungi Tipo'));
$tipoDocumentoSubmitClass = (string) ($tipoDocumentoSubmitClass
  ?? ($tipoDocumentoFormIsEdit ? 'btn-warning' : 'btn-success'));
?>
<div class="col-12">
  <label class="form-label">Tipo Documento</label>
  <input class="form-control" name="type" id="<?= htmlspecialchars($tipoDocumentoFieldId) ?>" placeholder="<?= $tipoDocumentoFormIsEdit ? 'Tipo documento' : 'Nuovo tipo documento' ?>" required value="<?= htmlspecialchars($tipoDocumentoFormValue) ?>">
</div>

<div class="col-12 d-flex justify-content-end gap-2">
  <button class="btn btn-secondary" type="button" id="<?= htmlspecialchars($tipoDocumentoCancelButtonId) ?>">Annulla</button>
  <button class="btn <?= htmlspecialchars($tipoDocumentoSubmitClass) ?>" type="submit"><?= htmlspecialchars($tipoDocumentoSubmitLabel) ?></button>
</div>
