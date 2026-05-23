<?php

declare(strict_types=1);

/**
 * Variabili attese dal parent view:
 * - $athleteFormId: string
 * - $athleteFormValues: array
 * - $athleteTabPaneClasses: array{anagrafica:string,contatti:string,misure:string}
 * - $athletePaneIds: array{anagrafica:string,contatti:string,misure:string}
 * - $athleteShowIntroAlert: bool
 * - $athleteShowTabSaveButtons: bool
 * - $athleteImageInputId: string
 * - $athleteImagePreviewWrapId: string
 * - $athleteImagePreviewId: string
 * - $athleteImagePreviewWrapClass: string
 * - $athleteImageRemoveCheckboxId: string
 * - $athleteImagePlaceholderId: string
 * - $athleteImageCropContainerId: string
 * - $athleteImageCropSourceId: string
 * - $athleteImageApplyCropButtonId: string
 * - $athleteImageCancelCropButtonId: string
 * - $athleteBirthCitySelectId: string
 * - $athleteBirthProvinceInputId: string
 * - $athleteBirthCountryInputId: string
 * - $athleteResidenceCitySelectId: string
 * - $athleteResidenceProvinceInputId: string
 * - $athleteResidenceCapInputId: string
 * - $athleteResidenceCountryInputId: string
 */

$athleteFormValues = is_array($athleteFormValues ?? null) ? $athleteFormValues : [];
$athleteTabPaneClasses = is_array($athleteTabPaneClasses ?? null) ? $athleteTabPaneClasses : [];
$athletePaneIds = is_array($athletePaneIds ?? null) ? $athletePaneIds : [];

$formId = (string) ($athleteFormId ?? '');
$status = (string) ($athleteFormValues['status'] ?? 'Attivo');
$sesso = (string) ($athleteFormValues['sesso'] ?? '');
$birthCity = (string) ($athleteFormValues['citta_nascita'] ?? '');
$resCity = (string) ($athleteFormValues['citta_residenza'] ?? '');
$imageUrl = (string) ($athleteFormValues['image_url'] ?? '');

$showIntroAlert = (bool) ($athleteShowIntroAlert ?? false);
$showTabSaveButtons = (bool) ($athleteShowTabSaveButtons ?? false);
$imagePreviewWrapClass = trim((string) ($athleteImagePreviewWrapClass ?? 'd-none'));
$showRemoveImage = trim((string) ($athleteImageRemoveCheckboxId ?? '')) !== '';
$imagePlaceholderId = (string) ($athleteImagePlaceholderId ?? '');
$imageCropContainerId = (string) ($athleteImageCropContainerId ?? '');
$imageCropSourceId = (string) ($athleteImageCropSourceId ?? '');
$imageApplyCropButtonId = (string) ($athleteImageApplyCropButtonId ?? '');
$imageCancelCropButtonId = (string) ($athleteImageCancelCropButtonId ?? '');

$fullName = trim((string) ($athleteFormValues['nome'] ?? '') . ' ' . (string) ($athleteFormValues['cognome'] ?? ''));
$initials = 'A';
if ($fullName !== '') {
  $nameParts = preg_split('/\s+/', $fullName) ?: [];
  $nameParts = array_values(array_filter($nameParts, static fn (string $part): bool => $part !== ''));
  if (count($nameParts) === 1) {
    $initials = strtoupper(substr($nameParts[0], 0, 2));
  } elseif (count($nameParts) >= 2) {
    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
  }
}

$tabClass = static function (string $name) use ($athleteTabPaneClasses): string {
    return (string) ($athleteTabPaneClasses[$name] ?? 'tab-pane fade');
};
$tabId = static function (string $name) use ($athletePaneIds): string {
    return (string) ($athletePaneIds[$name] ?? '');
};
?>
<div class="<?= htmlspecialchars($tabClass('anagrafica')) ?>" id="<?= htmlspecialchars($tabId('anagrafica')) ?>" role="tabpanel">
  <?php if ($showIntroAlert): ?>
    <div class="alert alert-info py-2 mb-3" role="alert">
      I tab Documenti/Certificati, Iscrizioni e Pagamenti diventano disponibili nella scheda atleta dopo il primo salvataggio.
    </div>
  <?php endif; ?>

  <div class="row g-3">
    <div class="col-12 col-md-3">
      <div class="border rounded p-3 h-100">
        <div class="text-center mb-2">
          <img
            id="<?= htmlspecialchars((string) ($athleteImagePreviewId ?? '')) ?>"
            src="<?= htmlspecialchars($imageUrl) ?>"
            data-initial-src="<?= htmlspecialchars($imageUrl) ?>"
            alt="Immagine atleta"
            class="rounded-circle <?= htmlspecialchars($imagePreviewWrapClass) ?>"
            style="width: 130px; height: 130px; object-fit: cover;"
          >
          <div id="<?= htmlspecialchars($imagePlaceholderId) ?>" class="rounded-circle border d-flex align-items-center justify-content-center mx-auto text-muted <?= $imageUrl !== '' ? 'd-none' : '' ?>" style="width: 130px; height: 130px;">
            <?= htmlspecialchars($initials) ?>
          </div>
        </div>

        <label class="form-label" for="<?= htmlspecialchars((string) ($athleteImageInputId ?? '')) ?>">Immagine atleta</label>
        <input class="form-control" type="file" id="<?= htmlspecialchars((string) ($athleteImageInputId ?? '')) ?>" name="image" form="<?= htmlspecialchars($formId) ?>" accept="image/jpeg,image/png,image/webp,image/gif">

        <div id="<?= htmlspecialchars($imageCropContainerId) ?>" class="mt-3 d-none">
          <div class="border rounded p-2 bg-light">
            <img id="<?= htmlspecialchars($imageCropSourceId) ?>" src="" alt="Ritaglio avatar atleta" style="max-width: 100%; display: block;">
          </div>
          <div class="d-flex gap-2 mt-2">
            <button type="button" class="btn btn-sm btn-primary" id="<?= htmlspecialchars($imageApplyCropButtonId) ?>">Usa ritaglio</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="<?= htmlspecialchars($imageCancelCropButtonId) ?>">Annulla ritaglio</button>
          </div>
          <small class="text-muted">Trascina e zooma l'immagine, poi premi "Usa ritaglio".</small>
        </div>

        <small class="text-muted">Formati supportati: JPG, PNG, WEBP, GIF (max 5MB)</small>
      </div>
      <?php if ($showRemoveImage): ?>
        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" id="<?= htmlspecialchars((string) $athleteImageRemoveCheckboxId) ?>" form="<?= htmlspecialchars($formId) ?>">
          <label class="form-check-label" for="<?= htmlspecialchars((string) $athleteImageRemoveCheckboxId) ?>">Rimuovi immagine attuale</label>
        </div>
      <?php endif; ?>
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label">Stato</label>
      <select class="form-select" name="status" form="<?= htmlspecialchars($formId) ?>">
        <option value="Attivo" <?= $status === 'Attivo' ? 'selected' : '' ?>>Attivo</option>
        <option value="Sospeso" <?= $status === 'Sospeso' ? 'selected' : '' ?>>Sospeso</option>
      </select>
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label">Data scadenza account</label>
      <input type="date" class="form-control" name="data_scadenza_account" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['data_scadenza_account'] ?? '')) ?>">
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-md-3">
      <label class="form-label">Sesso</label>
      <select class="form-select" name="sesso" form="<?= htmlspecialchars($formId) ?>">
        <option value="">Seleziona</option>
        <option value="M" <?= $sesso === 'M' ? 'selected' : '' ?>>Maschio</option>
        <option value="F" <?= $sesso === 'F' ? 'selected' : '' ?>>Femmina</option>
      </select>
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label">Cognome</label>
      <input class="form-control" name="cognome" required form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['cognome'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label">Nome</label>
      <input class="form-control" name="nome" required form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['nome'] ?? '')) ?>">
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-md-3">
      <label class="form-label">Data nascita</label>
      <input type="date" class="form-control" name="data_nascita" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['data_nascita'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label">Città nascita</label>
      <select class="form-select js-comune-select" name="citta_nascita" id="<?= htmlspecialchars((string) ($athleteBirthCitySelectId ?? '')) ?>" data-province-target="<?= htmlspecialchars((string) ($athleteBirthProvinceInputId ?? '')) ?>" data-country-target="<?= htmlspecialchars((string) ($athleteBirthCountryInputId ?? '')) ?>" form="<?= htmlspecialchars($formId) ?>">
        <option value="<?= htmlspecialchars($birthCity) ?>" <?= $birthCity === '' ? '' : 'selected' ?>><?= htmlspecialchars($birthCity) ?></option>
      </select>
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label">Provincia nascita</label>
      <input class="form-control" name="provincia_nascita" id="<?= htmlspecialchars((string) ($athleteBirthProvinceInputId ?? '')) ?>" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['provincia_nascita'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label">Stato nascita</label>
      <input class="form-control" name="stato_nascita" id="<?= htmlspecialchars((string) ($athleteBirthCountryInputId ?? '')) ?>" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['stato_nascita'] ?? '')) ?>">
    </div>

    <div class="col-12 col-md-4">
      <label class="form-label">Codice fiscale</label>
      <div class="input-group">
        <input class="form-control" name="codice_fiscale" maxlength="16" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['codice_fiscale'] ?? '')) ?>">
        <button class="btn btn-outline-secondary js-cf-calc-btn" type="button" data-form-id="<?= htmlspecialchars($formId) ?>">Calcola</button>
      </div>
    </div>
    <div class="col-12 col-md-4" style="display: none;">
      <label class="form-label">P.IVA</label>
      <input class="form-control" name="piva" maxlength="11" pattern="\d{11}" title="Inserisci 11 cifre" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['piva'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-4" style="display: none;">
      <label class="form-label">Codice univoco fatturazione</label>
      <input class="form-control" name="codice_univoco_fatturazione" maxlength="7" pattern="[A-Za-z0-9]{6,7}" title="Inserisci 6-7 caratteri alfanumerici" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['codice_univoco_fatturazione'] ?? '')) ?>">
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12">
      <label class="form-label">Note atleta</label>
      <textarea class="form-control" name="note_atleta" rows="3" form="<?= htmlspecialchars($formId) ?>"><?= htmlspecialchars((string) ($athleteFormValues['note_atleta'] ?? '')) ?></textarea>
    </div>
    <?php if ($showTabSaveButtons): ?>
      <div class="col-12 d-flex justify-content-end">
        <button class="btn btn-primary" type="submit" form="<?= htmlspecialchars($formId) ?>">Salva anagrafica</button>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="<?= htmlspecialchars($tabClass('contatti')) ?>" id="<?= htmlspecialchars($tabId('contatti')) ?>" role="tabpanel">
  <div class="row g-3">
    <div class="col-12">
      <label class="form-label">Indirizzo residenza</label>
      <input class="form-control" name="indirizzo_residenza" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['indirizzo_residenza'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-4">
      <label class="form-label">Città residenza</label>
      <select class="form-select js-comune-select" name="citta_residenza" id="<?= htmlspecialchars((string) ($athleteResidenceCitySelectId ?? '')) ?>" data-province-target="<?= htmlspecialchars((string) ($athleteResidenceProvinceInputId ?? '')) ?>" data-country-target="<?= htmlspecialchars((string) ($athleteResidenceCountryInputId ?? '')) ?>" data-cap-target="<?= htmlspecialchars((string) ($athleteResidenceCapInputId ?? '')) ?>" form="<?= htmlspecialchars($formId) ?>">
        <option value="<?= htmlspecialchars($resCity) ?>" <?= $resCity === '' ? '' : 'selected' ?>><?= htmlspecialchars($resCity) ?></option>
      </select>
    </div>
    <div class="col-12 col-md-2">
      <label class="form-label">Provincia</label>
      <input class="form-control" name="provincia_residenza" id="<?= htmlspecialchars((string) ($athleteResidenceProvinceInputId ?? '')) ?>" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['provincia_residenza'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-2">
      <label class="form-label">CAP</label>
      <input class="form-control" name="cap_residenza" id="<?= htmlspecialchars((string) ($athleteResidenceCapInputId ?? '')) ?>" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['cap_residenza'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-4">
      <label class="form-label">Stato residenza</label>
      <input class="form-control" name="stato_residenza" id="<?= htmlspecialchars((string) ($athleteResidenceCountryInputId ?? '')) ?>" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['stato_residenza'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-4">
      <label class="form-label">Telefono 1</label>
      <input class="form-control" name="telefono_1" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['telefono_1'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-4">
      <label class="form-label">Telefono 2</label>
      <input class="form-control" name="telefono_2" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['telefono_2'] ?? '')) ?>">
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-md-4">
      <label class="form-label">Email 1</label>
      <input type="email" class="form-control" name="email_1" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['email_1'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-4">
      <label class="form-label">Email 2</label>
      <input type="email" class="form-control" name="email_2" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['email_2'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-4">
      <label class="form-label">PEC</label>
      <input type="email" class="form-control" name="pec" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['pec'] ?? '')) ?>">
    </div>
    <?php if ($showTabSaveButtons): ?>
      <div class="col-12 d-flex justify-content-end">
        <button class="btn btn-primary" type="submit" form="<?= htmlspecialchars($formId) ?>">Salva contatti</button>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="<?= htmlspecialchars($tabClass('misure')) ?>" id="<?= htmlspecialchars($tabId('misure')) ?>" role="tabpanel">
  <div class="row g-3">
    <div class="col-12 col-md-3">
      <label class="form-label">Altezza (cm)</label>
      <input type="number" min="0" class="form-control" name="altezza" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['altezza'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label">Peso (kg)</label>
      <input type="number" step="0.01" min="0" class="form-control" name="peso" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['peso'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-2">
      <label class="form-label">Misura</label>
      <input class="form-control" name="misura" maxlength="3" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['misura'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-2">
      <label class="form-label">Misura maglia</label>
      <input class="form-control" name="misura_maglia" maxlength="3" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['misura_maglia'] ?? '')) ?>">
    </div>
    <div class="col-12 col-md-2">
      <label class="form-label">Misura pantaloni</label>
      <input class="form-control" name="misura_pantaloni" maxlength="3" form="<?= htmlspecialchars($formId) ?>" value="<?= htmlspecialchars((string) ($athleteFormValues['misura_pantaloni'] ?? '')) ?>">
    </div>
    <?php if ($showTabSaveButtons): ?>
      <div class="col-12 d-flex justify-content-end">
        <button class="btn btn-primary" type="submit" form="<?= htmlspecialchars($formId) ?>">Salva misure</button>
      </div>
    <?php endif; ?>
  </div>
</div>
