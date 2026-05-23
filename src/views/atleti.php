<?php

declare(strict_types=1);

/** @var array $atleti */
/** @var array $tipiDocumenti */
/** @var array $corsi */
/** @var array|null $selectedAtleta */

$frontendApi = frontend_api_urls();
$atletiApiUrl = (string) ($frontendApi['atleti'] ?? '');
$appPaths = app_paths();
$indexPath = (string) ($appPaths['index'] ?? '/seiryokukai_php/public/index.php');
$atletiPageUrl = $indexPath . '?page=atleti';

$okMessage = trim((string) ($_GET['ok'] ?? ''));
$errMessage = trim((string) ($_GET['err'] ?? ''));
$openAddPanel = ((string) ($_GET['open_add'] ?? '0')) === '1';
$activeTab = trim((string) ($_GET['athlete_tab'] ?? 'anagrafica'));
$allowedTabs = ['anagrafica', 'contatti', 'documenti', 'iscrizioni', 'pagamenti'];
if (!in_array($activeTab, $allowedTabs, true)) {
    $activeTab = 'anagrafica';
}

$defaultAccountExpiryDate = (new \DateTimeImmutable('last day of december next year'))->format('Y-m-d');

$addPrefill = [
    'nome' => trim((string) ($_GET['add_nome'] ?? '')),
    'cognome' => trim((string) ($_GET['add_cognome'] ?? '')),
  'piva' => trim((string) ($_GET['add_piva'] ?? '')),
  'codice_univoco_fatturazione' => trim((string) ($_GET['add_codice_univoco_fatturazione'] ?? '')),
    'email_1' => trim((string) ($_GET['add_email_1'] ?? '')),
    'email_2' => trim((string) ($_GET['add_email_2'] ?? '')),
    'pec' => trim((string) ($_GET['add_pec'] ?? '')),
    'telefono_1' => trim((string) ($_GET['add_telefono_1'] ?? '')),
    'data_scadenza_account' => trim((string) ($_GET['add_data_scadenza_account'] ?? $defaultAccountExpiryDate)),
];

if (!$openAddPanel) {
    $openAddPanel = $addPrefill['nome'] !== ''
        || $addPrefill['cognome'] !== ''
        || $addPrefill['email_1'] !== ''
        || $addPrefill['telefono_1'] !== '';
}

$hasSelectedAtleta = is_array($selectedAtleta ?? null);
$openEditPanel = $hasSelectedAtleta && (((string) ($_GET['open_edit'] ?? '0')) === '1' || (int) ($_GET['edit_id'] ?? 0) > 0);

$tabButtonClass = static function (string $tabName) use ($activeTab): string {
    return $activeTab === $tabName ? 'nav-link active' : 'nav-link';
};
$tabPaneClass = static function (string $tabName) use ($activeTab): string {
    return $activeTab === $tabName ? 'tab-pane fade show active' : 'tab-pane fade';
};

$selectedDocumenti = $hasSelectedAtleta && isset($selectedAtleta['documenti']) && is_array($selectedAtleta['documenti'])
    ? $selectedAtleta['documenti']
    : [];
$selectedIscrizioni = $hasSelectedAtleta && isset($selectedAtleta['iscrizioni']) && is_array($selectedAtleta['iscrizioni'])
    ? $selectedAtleta['iscrizioni']
    : [];
$selectedPagamenti = $hasSelectedAtleta && isset($selectedAtleta['pagamenti']) && is_array($selectedAtleta['pagamenti'])
    ? $selectedAtleta['pagamenti']
    : [];
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
      <div>
        <h5 class="m-0">Gestione Atleti</h5>
        <small class="text-muted">Scheda atleta con tab anagrafica, contatti, documenti, iscrizioni e pagamenti.</small>
      </div>
      <button class="btn btn-success" type="button" id="openAddAtletaPanelBtn">+ Nuovo atleta</button>
    </div>

    <?php if ($okMessage !== ''): ?>
      <div class="alert alert-success" role="alert">
        <?= htmlspecialchars($okMessage) ?>
      </div>
    <?php endif; ?>
    <?php if ($errMessage !== ''): ?>
      <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($errMessage) ?>
      </div>
    <?php endif; ?>

    <div class="table-responsive">
      <table id="atleti-table" class="table align-middle js-datatable table-hover" data-server-side="1">
        <thead>
          <tr>
            <th>ID</th>
            <th>Atleta</th>
            <th>Email</th>
            <th>Telefono</th>
            <th>Stato</th>
            <th class="text-end">Azioni</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <div id="addAtletaPanel" class="card border mt-4 <?= $openAddPanel ? '' : 'd-none' ?>">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Nuovo atleta</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeAddAtletaPanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($atletiApiUrl) ?>" class="row g-3" id="addAthleteForm" enctype="multipart/form-data">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="athlete_tab" id="addAthleteTabInput" value="anagrafica">

          <ul class="nav nav-tabs customtab col-12" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active js-athlete-add-tab-trigger" data-bs-toggle="tab" data-bs-target="#add-athlete-anagrafica" type="button" role="tab">Anagrafica</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link js-athlete-add-tab-trigger" data-bs-toggle="tab" data-bs-target="#add-athlete-contatti" type="button" role="tab">Contatti</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link disabled" type="button" role="tab" aria-disabled="true" tabindex="-1">Documenti</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link disabled" type="button" role="tab" aria-disabled="true" tabindex="-1">Iscrizioni</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link disabled" type="button" role="tab" aria-disabled="true" tabindex="-1">Pagamenti</button>
            </li>
          </ul>

          <div class="tab-content border border-top-0 rounded-bottom p-3 col-12">
            <div class="tab-pane fade show active" id="add-athlete-anagrafica" role="tabpanel">
              <div class="alert alert-info py-2 mb-3" role="alert">
                I tab Documenti, Iscrizioni e Pagamenti diventano disponibili nella scheda atleta dopo il primo salvataggio.
              </div>
              <div class="row g-3">
                <div class="col-12 col-md-3">
                  <label class="form-label">Immagine atleta</label>
                  <input class="form-control" type="file" id="addAthleteImageInput" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                  <small class="text-muted">Formati: JPG, PNG, WEBP, GIF. Max 5MB.</small>
                  <div id="addAthleteImagePreviewWrap" class="mt-2 d-none">
                    <img id="addAthleteImagePreview" src="" alt="Anteprima immagine atleta" class="img-thumbnail" style="max-width: 140px; max-height: 140px; object-fit: cover;">
                  </div>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Stato</label>
                  <select class="form-select" name="status">
                    <option value="Attivo">Attivo</option>
                    <option value="Sospeso">Sospeso</option>
                  </select>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Data scadenza account</label>
                  <input type="date" class="form-control" name="data_scadenza_account" value="<?= htmlspecialchars($addPrefill['data_scadenza_account']) ?>">
                </div>
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-3">
                  <label class="form-label">Sesso</label>
                  <select class="form-select" name="sesso">
                    <option value="">Seleziona</option>
                    <option value="M">Maschio</option>
                    <option value="F">Femmina</option>
                  </select>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Cognome</label>
                  <input class="form-control" name="cognome" required value="<?= htmlspecialchars($addPrefill['cognome']) ?>">
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Nome</label>
                  <input class="form-control" name="nome" required value="<?= htmlspecialchars($addPrefill['nome']) ?>">
                </div>
                
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-3">
                  <label class="form-label">Data nascita</label>
                  <input type="date" class="form-control" name="data_nascita">
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Città nascita</label>
                  <select class="form-select js-comune-select" name="citta_nascita" id="add_citta_nascita" data-province-target="add_provincia_nascita" data-country-target="add_nazione_nascita">
                    <option value=""></option>
                  </select>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Provincia nascita</label>
                  <input class="form-control" name="provincia_nascita" id="add_provincia_nascita">
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Stato nascita</label>
                  <input class="form-control" name="stato_nascita" id="add_nazione_nascita">
                </div>

                <div class="col-12 col-md-4">
                  <label class="form-label">Codice fiscale</label>
                  <div class="input-group">
                    <input class="form-control" name="codice_fiscale" maxlength="16">
                    <button class="btn btn-outline-secondary js-cf-calc-btn" type="button" data-form-id="addAthleteForm">Calcola</button>
                  </div>
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">P.IVA</label>
                  <input class="form-control" name="piva" maxlength="11" pattern="\d{11}" title="Inserisci 11 cifre" value="<?= htmlspecialchars($addPrefill['piva']) ?>">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Codice univoco fatturazione</label>
                  <input class="form-control" name="codice_univoco_fatturazione" maxlength="7" pattern="[A-Za-z0-9]{6,7}" title="Inserisci 6-7 caratteri alfanumerici" value="<?= htmlspecialchars($addPrefill['codice_univoco_fatturazione']) ?>">
                </div>
    </div>
    <div class="row g-3">
                
                <div class="col-12 col-md-2">
                  <label class="form-label">Altezza (cm)</label>
                  <input type="number" min="0" class="form-control" name="altezza">
                </div>
                <div class="col-12 col-md-2">
                  <label class="form-label">Peso (kg)</label>
                  <input type="number" step="0.01" min="0" class="form-control" name="peso">
                </div>
                </div>
                <div class="row g-3">
                <div class="col-12 col-md-3">
                  <label class="form-label">Misura</label>
                  <input class="form-control" name="misura" maxlength="3">
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Misura maglia</label>
                  <input class="form-control" name="misura_maglia" maxlength="3">
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Misura pantaloni</label>
                  <input class="form-control" name="misura_pantaloni" maxlength="3">
                </div>
                <div class="col-12">
                  <label class="form-label">Note atleta</label>
                  <textarea class="form-control" name="note_atleta" rows="3"></textarea>
                </div>
              </div>
            </div>

            <div class="tab-pane fade" id="add-athlete-contatti" role="tabpanel">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">Indirizzo residenza</label>
                  <input class="form-control" name="indirizzo_residenza">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Città residenza</label>
                  <select class="form-select js-comune-select" name="citta_residenza" id="add_citta_residenza" data-province-target="add_provincia_residenza" data-country-target="add_stato_residenza" data-cap-target="add_cap_residenza">
                    <option value=""></option>
                  </select>
                </div>
                <div class="col-12 col-md-2">
                  <label class="form-label">Provincia</label>
                  <input class="form-control" name="provincia_residenza" id="add_provincia_residenza">
                </div>
                <div class="col-12 col-md-2">
                  <label class="form-label">CAP</label>
                  <input class="form-control" name="cap_residenza" id="add_cap_residenza">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Stato residenza</label>
                  <input class="form-control" name="stato_residenza" id="add_stato_residenza">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Telefono 1</label>
                  <input class="form-control" name="telefono_1" value="<?= htmlspecialchars($addPrefill['telefono_1']) ?>">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Telefono 2</label>
                  <input class="form-control" name="telefono_2">
                </div>
              </div>
              <div class="row g-3">
                <div class="col-12 col-md-4">
                  <label class="form-label">Email 1</label>
                  <input type="email" class="form-control" name="email_1" value="<?= htmlspecialchars($addPrefill['email_1']) ?>">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Email 2</label>
                  <input type="email" class="form-control" name="email_2" value="<?= htmlspecialchars($addPrefill['email_2']) ?>">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">PEC</label>
                  <input type="email" class="form-control" name="pec" value="<?= htmlspecialchars($addPrefill['pec']) ?>">
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 d-flex justify-content-end gap-2">
            <button class="btn btn-secondary" type="button" id="cancelAddAtletaBtn">Annulla</button>
            <button class="btn btn-success" type="submit">Salva atleta</button>
          </div>
        </form>
      </div>
    </div>

    <?php if ($hasSelectedAtleta): ?>
      <div id="editAtletaPanel" class="card border mt-4 <?= $openEditPanel ? '' : 'd-none' ?>">
        <div class="card-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
          <div>
            <h6 class="m-0">Scheda atleta #<?= (int) ($selectedAtleta['id'] ?? 0) ?> - <?= htmlspecialchars((string) ($selectedAtleta['name'] ?? '')) ?></h6>
            <div class="d-flex gap-2 mt-2 flex-wrap align-items-center">
              <span class="badge text-bg-<?= (($selectedAtleta['status'] ?? '') === 'Attivo') ? 'success' : 'secondary' ?>"><?= htmlspecialchars((string) ($selectedAtleta['status'] ?? '')) ?></span>
              <?php if (($selectedAtleta['email'] ?? '') !== ''): ?>
                <span class="text-muted small"><?= htmlspecialchars((string) $selectedAtleta['email']) ?></span>
              <?php endif; ?>
              <?php if (($selectedAtleta['phone'] ?? '') !== ''): ?>
                <span class="text-muted small"><?= htmlspecialchars((string) $selectedAtleta['phone']) ?></span>
              <?php endif; ?>
            </div>
          </div>
          <a class="btn btn-sm btn-outline-secondary" href="<?= htmlspecialchars($atletiPageUrl) ?>">Chiudi</a>
        </div>
        <div class="card-body">
          <form id="editAthleteProfileForm" method="post" action="<?= htmlspecialchars($atletiApiUrl) ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= (int) ($selectedAtleta['id'] ?? 0) ?>">
            <input type="hidden" name="athlete_tab" id="editAthleteTabInput" value="<?= htmlspecialchars($activeTab) ?>">
            <input type="hidden" name="current_image_path" value="<?= htmlspecialchars((string) ($selectedAtleta['image_path'] ?? '')) ?>">
            <input type="hidden" name="remove_image" id="editAthleteRemoveImageInput" value="0">
          </form>

          <ul class="nav nav-tabs customtab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="<?= $tabButtonClass('anagrafica') ?> js-athlete-edit-tab-trigger" data-bs-toggle="tab" data-bs-target="#athlete-tab-anagrafica" type="button" role="tab">Anagrafica</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="<?= $tabButtonClass('contatti') ?> js-athlete-edit-tab-trigger" data-bs-toggle="tab" data-bs-target="#athlete-tab-contatti" type="button" role="tab">Contatti</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="<?= $tabButtonClass('documenti') ?> js-athlete-edit-tab-trigger" data-bs-toggle="tab" data-bs-target="#athlete-tab-documenti" type="button" role="tab">Documenti</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="<?= $tabButtonClass('iscrizioni') ?> js-athlete-edit-tab-trigger" data-bs-toggle="tab" data-bs-target="#athlete-tab-iscrizioni" type="button" role="tab">Iscrizioni</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="<?= $tabButtonClass('pagamenti') ?> js-athlete-edit-tab-trigger" data-bs-toggle="tab" data-bs-target="#athlete-tab-pagamenti" type="button" role="tab">Pagamenti</button>
            </li>
          </ul>

          <div class="tab-content border border-top-0 rounded-bottom p-3">
            <div class="<?= $tabPaneClass('anagrafica') ?>" id="athlete-tab-anagrafica" role="tabpanel">
              <div class="row g-3">
                <div class="col-12 col-md-3">
                  <label class="form-label">Immagine atleta</label>
                  <input class="form-control" type="file" id="editAthleteImageInput" name="image" form="editAthleteProfileForm" accept="image/jpeg,image/png,image/webp,image/gif">
                  <div id="editAthleteImagePreviewWrap" class="mt-2 <?= ((string) ($selectedAtleta['image_url'] ?? '')) !== '' ? '' : 'd-none' ?>">
                    <img id="editAthleteImagePreview" src="<?= htmlspecialchars((string) ($selectedAtleta['image_url'] ?? '')) ?>" data-initial-src="<?= htmlspecialchars((string) ($selectedAtleta['image_url'] ?? '')) ?>" alt="Anteprima immagine atleta" class="img-thumbnail" style="max-width: 140px; max-height: 140px; object-fit: cover;">
                  </div>
                  <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="editAthleteRemoveImageCheckbox" form="editAthleteProfileForm">
                    <label class="form-check-label" for="editAthleteRemoveImageCheckbox">Rimuovi immagine attuale</label>
                  </div>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Stato</label>
                  <select class="form-select" name="status" form="editAthleteProfileForm">
                    <option value="Attivo" <?= ($selectedAtleta['status'] ?? '') === 'Attivo' ? 'selected' : '' ?>>Attivo</option>
                    <option value="Sospeso" <?= ($selectedAtleta['status'] ?? '') === 'Sospeso' ? 'selected' : '' ?>>Sospeso</option>
                  </select>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Sesso</label>
                  <select class="form-select" name="sesso" form="editAthleteProfileForm">
                    <option value="">Seleziona</option>
                    <option value="M" <?= ($selectedAtleta['gender'] ?? '') === 'M' ? 'selected' : '' ?>>Maschio</option>
                    <option value="F" <?= ($selectedAtleta['gender'] ?? '') === 'F' ? 'selected' : '' ?>>Femmina</option>
                  </select>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Data scadenza account</label>
                  <input type="date" class="form-control" name="data_scadenza_account" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['account_expiry_date'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label">Nome</label>
                  <input class="form-control" name="nome" required form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['first_name'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label">Cognome</label>
                  <input class="form-control" name="cognome" required form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['last_name'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Codice fiscale</label>
                  <div class="input-group">
                    <input class="form-control" name="codice_fiscale" maxlength="16" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['tax_code'] ?? '')) ?>">
                    <button class="btn btn-outline-secondary js-cf-calc-btn" type="button" data-form-id="editAthleteProfileForm">Calcola</button>
                  </div>
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">P.IVA</label>
                  <input class="form-control" name="piva" maxlength="11" pattern="\d{11}" title="Inserisci 11 cifre" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['vat_number'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Codice univoco fatturazione</label>
                  <input class="form-control" name="codice_univoco_fatturazione" maxlength="7" pattern="[A-Za-z0-9]{6,7}" title="Inserisci 6-7 caratteri alfanumerici" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['invoice_code'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Data nascita</label>
                  <input type="date" class="form-control" name="data_nascita" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['birth_date'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Città nascita</label>
                  <select class="form-select js-comune-select" name="citta_nascita" id="edit_citta_nascita" data-province-target="edit_provincia_nascita" data-country-target="edit_stato_nascita" form="editAthleteProfileForm">
                    <option value="<?= htmlspecialchars((string) ($selectedAtleta['birth_city'] ?? '')) ?>" selected><?= htmlspecialchars((string) ($selectedAtleta['birth_city'] ?? '')) ?></option>
                  </select>
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Provincia nascita</label>
                  <input class="form-control" name="provincia_nascita" id="edit_provincia_nascita" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['birth_province'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Stato nascita</label>
                  <input class="form-control" name="stato_nascita" id="edit_stato_nascita" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['birth_country'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Altezza (cm)</label>
                  <input type="number" min="0" class="form-control" name="altezza" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['height'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Peso</label>
                  <input type="number" min="0" step="0.01" class="form-control" name="peso" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['weight'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Misura</label>
                  <input class="form-control" maxlength="3" name="misura" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['size'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Misura maglia</label>
                  <input class="form-control" maxlength="3" name="misura_maglia" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['shirt_size'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Misura pantaloni</label>
                  <input class="form-control" maxlength="3" name="misura_pantaloni" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['pants_size'] ?? '')) ?>">
                </div>
                <div class="col-12">
                  <label class="form-label">Note atleta</label>
                  <textarea class="form-control" rows="4" name="note_atleta" form="editAthleteProfileForm"><?= htmlspecialchars((string) ($selectedAtleta['notes'] ?? '')) ?></textarea>
                </div>
                <div class="col-12 d-flex justify-content-end">
                  <button class="btn btn-primary" type="submit" form="editAthleteProfileForm">Salva anagrafica</button>
                </div>
              </div>
            </div>

            <div class="<?= $tabPaneClass('contatti') ?>" id="athlete-tab-contatti" role="tabpanel">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">Indirizzo residenza</label>
                  <input class="form-control" name="indirizzo_residenza" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['address'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Città residenza</label>
                  <select class="form-select js-comune-select" name="citta_residenza" id="edit_citta_residenza" data-province-target="edit_provincia_residenza" data-country-target="edit_stato_residenza" data-cap-target="edit_cap_residenza" form="editAthleteProfileForm">
                    <option value="<?= htmlspecialchars((string) ($selectedAtleta['city'] ?? '')) ?>" selected><?= htmlspecialchars((string) ($selectedAtleta['city'] ?? '')) ?></option>
                  </select>
                </div>
                <div class="col-12 col-md-2">
                  <label class="form-label">Provincia</label>
                  <input class="form-control" name="provincia_residenza" id="edit_provincia_residenza" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['province'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-2">
                  <label class="form-label">CAP</label>
                  <input class="form-control" name="cap_residenza" id="edit_cap_residenza" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['postal_code'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Stato residenza</label>
                  <input class="form-control" name="stato_residenza" id="edit_stato_residenza" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['country'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Telefono 1</label>
                  <input class="form-control" name="telefono_1" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['phone'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Telefono 2</label>
                  <input class="form-control" name="telefono_2" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['phone_alt'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">PEC</label>
                  <input type="email" class="form-control" name="pec" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['pec'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label">Email 1</label>
                  <input type="email" class="form-control" name="email_1" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['email'] ?? '')) ?>">
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label">Email 2</label>
                  <input type="email" class="form-control" name="email_2" form="editAthleteProfileForm" value="<?= htmlspecialchars((string) ($selectedAtleta['email_alt'] ?? '')) ?>">
                </div>
                <div class="col-12 d-flex justify-content-end">
                  <button class="btn btn-primary" type="submit" form="editAthleteProfileForm">Salva contatti</button>
                </div>
              </div>
            </div>

            <div class="<?= $tabPaneClass('documenti') ?>" id="athlete-tab-documenti" role="tabpanel">
              <form method="post" action="<?= htmlspecialchars($atletiApiUrl) ?>" class="row g-3" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_documento">
                <input type="hidden" name="idatleta" value="<?= (int) ($selectedAtleta['id'] ?? 0) ?>">
                <div class="col-12 col-md-4">
                  <label class="form-label">Tipo documento</label>
                  <select class="form-select" name="idtipo_documento" required>
                    <option value="">Seleziona</option>
                    <?php foreach ($tipiDocumenti as $tipoDocumento): ?>
                      <option value="<?= (int) ($tipoDocumento['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($tipoDocumento['type'] ?? '')) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Descrizione</label>
                  <input class="form-control" name="descrizione_documento">
                </div>
                <div class="col-12 col-md-2">
                  <label class="form-label">Data documento</label>
                  <input type="date" class="form-control" name="data_documento">
                </div>
                <div class="col-12 col-md-2">
                  <label class="form-label">Scadenza</label>
                  <input type="date" class="form-control" name="data_scadenza">
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label">File documento</label>
                  <input class="form-control" type="file" name="document_file" accept="application/pdf,image/jpeg,image/png,image/webp">
                  <small class="text-muted">Formati supportati: PDF, JPG, PNG, WEBP. Max 8MB.</small>
                </div>
                <div class="col-12">
                  <label class="form-label">URL documento</label>
                  <input class="form-control" name="url_documento" placeholder="https://... se non carichi un file">
                  <small class="text-muted">Compila l'URL solo se il documento non viene caricato da questo form.</small>
                </div>
                <div class="col-12 d-flex justify-content-end">
                  <button class="btn btn-outline-primary" type="submit">Aggiungi documento</button>
                </div>
              </form>

              <div class="table-responsive mt-4">
                <table class="table table-sm align-middle">
                  <thead>
                    <tr>
                      <th>Tipo</th>
                      <th>Descrizione</th>
                      <th>Data</th>
                      <th>Scadenza</th>
                      <th>Link</th>
                      <th class="text-end">Azioni</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($selectedDocumenti === []): ?>
                      <tr>
                        <td colspan="6" class="text-center text-muted py-3">Nessun documento registrato.</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($selectedDocumenti as $documento): ?>
                        <tr>
                          <td><?= htmlspecialchars((string) ($documento['type_name'] ?? '')) ?></td>
                          <td><?= htmlspecialchars((string) ($documento['description'] ?? '')) ?></td>
                          <td><?= htmlspecialchars((string) ($documento['document_date'] ?? '')) ?></td>
                          <td><?= htmlspecialchars((string) ($documento['expiry_date'] ?? '')) ?></td>
                          <td>
                            <?php if (((string) ($documento['public_url'] ?? '')) !== ''): ?>
                              <a href="<?= htmlspecialchars((string) $documento['public_url']) ?>" target="_blank" rel="noreferrer">
                                <?= htmlspecialchars((string) (($documento['file_name'] ?? '') !== '' ? $documento['file_name'] : 'Apri')) ?>
                              </a>
                            <?php else: ?>
                              <span class="text-muted">-</span>
                            <?php endif; ?>
                          </td>
                          <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                              <button
                                type="button"
                                class="btn btn-sm btn-outline-primary js-edit-documento-btn"
                                data-document-id="<?= (int) ($documento['id'] ?? 0) ?>"
                                data-type-id="<?= (int) ($documento['type_id'] ?? 0) ?>"
                                data-description="<?= htmlspecialchars((string) ($documento['description'] ?? ''), ENT_QUOTES) ?>"
                                data-document-date="<?= htmlspecialchars((string) ($documento['document_date'] ?? ''), ENT_QUOTES) ?>"
                                data-expiry-date="<?= htmlspecialchars((string) ($documento['expiry_date'] ?? ''), ENT_QUOTES) ?>"
                                data-url="<?= htmlspecialchars((string) ($documento['url'] ?? ''), ENT_QUOTES) ?>"
                              >Modifica</button>
                              <form method="post" action="<?= htmlspecialchars($atletiApiUrl) ?>" onsubmit="return confirm('Eliminare questo documento?');">
                                <input type="hidden" name="action" value="delete_documento">
                                <input type="hidden" name="idatleta" value="<?= (int) ($selectedAtleta['id'] ?? 0) ?>">
                                <input type="hidden" name="iddocumento" value="<?= (int) ($documento['id'] ?? 0) ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Elimina</button>
                              </form>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

              <div id="editDocumentoPanel" class="card border mt-3 d-none">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h6 class="m-0">Modifica documento</h6>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="closeEditDocumentoPanelBtn">Chiudi</button>
                </div>
                <div class="card-body">
                  <form method="post" action="<?= htmlspecialchars($atletiApiUrl) ?>" class="row g-3" enctype="multipart/form-data" id="editDocumentoForm">
                    <input type="hidden" name="action" value="update_documento">
                    <input type="hidden" name="idatleta" value="<?= (int) ($selectedAtleta['id'] ?? 0) ?>">
                    <input type="hidden" name="iddocumento" id="editDocumentoId">
                    <div class="col-12 col-md-4">
                      <label class="form-label">Tipo documento</label>
                      <select class="form-select" name="idtipo_documento" id="editDocumentoType" required>
                        <option value="">Seleziona</option>
                        <?php foreach ($tipiDocumenti as $tipoDocumento): ?>
                          <option value="<?= (int) ($tipoDocumento['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($tipoDocumento['type'] ?? '')) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-12 col-md-4">
                      <label class="form-label">Descrizione</label>
                      <input class="form-control" name="descrizione_documento" id="editDocumentoDescription">
                    </div>
                    <div class="col-12 col-md-2">
                      <label class="form-label">Data documento</label>
                      <input type="date" class="form-control" name="data_documento" id="editDocumentoDate">
                    </div>
                    <div class="col-12 col-md-2">
                      <label class="form-label">Scadenza</label>
                      <input type="date" class="form-control" name="data_scadenza" id="editDocumentoExpiryDate">
                    </div>
                    <div class="col-12 col-md-6">
                      <label class="form-label">Nuovo file documento</label>
                      <input class="form-control" type="file" name="document_file" accept="application/pdf,image/jpeg,image/png,image/webp">
                    </div>
                    <div class="col-12">
                      <label class="form-label">URL documento</label>
                      <input class="form-control" name="url_documento" id="editDocumentoUrl" placeholder="https://... oppure lascia il valore attuale">
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                      <button class="btn btn-primary" type="submit">Salva modifiche</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <div class="<?= $tabPaneClass('iscrizioni') ?>" id="athlete-tab-iscrizioni" role="tabpanel">
              <form method="post" action="<?= htmlspecialchars($atletiApiUrl) ?>" class="row g-3">
                <input type="hidden" name="action" value="add_iscrizione">
                <input type="hidden" name="idatleta" value="<?= (int) ($selectedAtleta['id'] ?? 0) ?>">
                <div class="col-12 col-md-3">
                  <label class="form-label">Data inizio</label>
                  <input type="date" class="form-control" name="data_inizio_iscrizione" required>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Data fine</label>
                  <input type="date" class="form-control" name="data_fine_iscrizione">
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Totale iscrizione</label>
                  <input type="number" step="0.01" min="0" class="form-control" name="totale_iscrizione">
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Stato</label>
                  <select class="form-select" name="stato_iscrizione" required>
                    <option value="A">Attiva</option>
                    <option value="S">Sospesa</option>
                    <option value="C">Conclusa</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label">Corsi collegati</label>
                  <select class="form-select" name="course_ids[]" multiple size="5">
                    <?php foreach ($corsi as $corso): ?>
                      <option value="<?= (int) ($corso['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($corso['name'] ?? '')) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <small class="text-muted">Puoi associare piu corsi alla stessa iscrizione.</small>
                </div>
                <div class="col-12">
                  <label class="form-label">Note iscrizione</label>
                  <textarea class="form-control" rows="3" name="note_iscrizione"></textarea>
                </div>
                <div class="col-12 d-flex justify-content-end">
                  <button class="btn btn-outline-primary" type="submit">Aggiungi iscrizione</button>
                </div>
              </form>

              <div class="table-responsive mt-4">
                <table class="table table-sm align-middle">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Periodo</th>
                      <th>Corsi</th>
                      <th>Totale</th>
                      <th>Stato</th>
                      <th>Note</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($selectedIscrizioni === []): ?>
                      <tr>
                        <td colspan="6" class="text-center text-muted py-3">Nessuna iscrizione registrata.</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($selectedIscrizioni as $iscrizione): ?>
                        <tr>
                          <td>#<?= (int) ($iscrizione['id'] ?? 0) ?></td>
                          <td>
                            <?= htmlspecialchars((string) ($iscrizione['start_date'] ?? '')) ?>
                            <?php if (((string) ($iscrizione['end_date'] ?? '')) !== ''): ?>
                              - <?= htmlspecialchars((string) $iscrizione['end_date']) ?>
                            <?php endif; ?>
                          </td>
                          <td><?= htmlspecialchars((string) ($iscrizione['courses'] ?? '')) ?></td>
                          <td><?= htmlspecialchars((string) ($iscrizione['total'] ?? '')) ?></td>
                          <td><?= htmlspecialchars((string) ($iscrizione['status_label'] ?? '')) ?></td>
                          <td><?= htmlspecialchars((string) ($iscrizione['notes'] ?? '')) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="<?= $tabPaneClass('pagamenti') ?>" id="athlete-tab-pagamenti" role="tabpanel">
              <form method="post" action="<?= htmlspecialchars($atletiApiUrl) ?>" class="row g-3">
                <input type="hidden" name="action" value="add_pagamento">
                <input type="hidden" name="idatleta" value="<?= (int) ($selectedAtleta['id'] ?? 0) ?>">
                <div class="col-12 col-md-4">
                  <label class="form-label">Corso iscritto</label>
                  <select class="form-select" name="idcorso" <?= $selectedIscrizioni === [] ? 'disabled' : 'required' ?>>
                    <option value="">Seleziona</option>
                    <?php foreach ($selectedIscrizioni as $iscrizione): ?>
                      <option value="<?= (int) ($iscrizione['course_id'] ?? $iscrizione['id'] ?? 0) ?>">
                        #<?= (int) ($iscrizione['course_id'] ?? $iscrizione['id'] ?? 0) ?> - <?= htmlspecialchars((string) ($iscrizione['courses'] ?? '')) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <?php if ($selectedIscrizioni === []): ?>
                    <small class="text-muted">Aggiungi prima almeno un corso nel tab Iscrizioni.</small>
                  <?php endif; ?>
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Data pagamento</label>
                  <input type="date" class="form-control" name="data_pagamento" required>
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Importo</label>
                  <input type="number" step="0.01" min="0" class="form-control" name="quota_pagamento" required>
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label">Data scadenza</label>
                  <input type="date" class="form-control" name="data_scadenza">
                </div>
                <div class="col-12">
                  <label class="form-label">Note pagamento</label>
                  <textarea class="form-control" rows="3" name="note_pagamento"></textarea>
                </div>
                <div class="col-12 d-flex justify-content-end">
                  <button class="btn btn-outline-primary" type="submit" <?= $selectedIscrizioni === [] ? 'disabled' : '' ?>>Registra pagamento</button>
                </div>
              </form>

              <div class="table-responsive mt-4">
                <table class="table table-sm align-middle">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Corso</th>
                      <th>Data pagamento</th>
                      <th>Scadenza</th>
                      <th>Importo</th>
                      <th>Note</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($selectedPagamenti === []): ?>
                      <tr>
                        <td colspan="6" class="text-center text-muted py-3">Nessun pagamento registrato.</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($selectedPagamenti as $pagamento): ?>
                        <tr>
                          <td>#<?= (int) ($pagamento['id'] ?? 0) ?></td>
                          <td>
                            <?= htmlspecialchars((string) ($pagamento['course_name'] ?? '')) ?>
                            <?php if (((string) ($pagamento['course_name'] ?? '')) === ''): ?>
                              #<?= (int) ($pagamento['course_id'] ?? 0) ?>
                            <?php endif; ?>
                          </td>
                          <td><?= htmlspecialchars((string) ($pagamento['payment_date'] ?? '')) ?></td>
                          <td><?= htmlspecialchars((string) ($pagamento['expiry_date'] ?? '')) ?></td>
                          <td><?= htmlspecialchars((string) ($pagamento['amount'] ?? '')) ?></td>
                          <td><?= htmlspecialchars((string) ($pagamento['notes'] ?? '')) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php elseif (((string) ($_GET['open_edit'] ?? '0')) === '1'): ?>
      <div class="alert alert-warning mt-4 mb-0" role="alert">
        Scheda atleta non trovata. Seleziona un atleta dalla tabella e clicca su "Scheda" per aprire tutti i tab (Documenti, Iscrizioni, Pagamenti).
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const setupCodiceFiscaleAutocalcolo = function (formId) {
    const form = document.getElementById(formId);
    if (!form) {
      return;
    }

    const cfInput = form.querySelector('input[name="codice_fiscale"]');
    const nomeInput = form.querySelector('input[name="nome"]');
    const cognomeInput = form.querySelector('input[name="cognome"]');
    const sessoInput = form.querySelector('select[name="sesso"]');
    const dataInput = form.querySelector('input[name="data_nascita"]');
    const cittaInput = form.querySelector('[name="citta_nascita"]');
    const provinciaInput = form.querySelector('input[name="provincia_nascita"]');
    const statoInput = form.querySelector('input[name="stato_nascita"]');

    if (!cfInput || !nomeInput || !cognomeInput || !sessoInput || !dataInput || !cittaInput || !provinciaInput || !statoInput) {
      return;
    }

    const baseUrl = (window.SeiryokukaiConfig && window.SeiryokukaiConfig.api && window.SeiryokukaiConfig.api.atleti) || '';
    if (!baseUrl) {
      return;
    }

    let debounceTimer = null;

    const isAutoGenerated = function () {
      return cfInput.dataset.autoGenerated === '1';
    };

    const shouldAutocalculate = function () {
      const current = String(cfInput.value || '').trim();
      return current === '' || isAutoGenerated();
    };

    const compute = function (force) {
      if (force !== true && !shouldAutocalculate()) {
        return;
      }

      const payload = {
        action: 'calcola_codice_fiscale',
        nome: nomeInput.value || '',
        cognome: cognomeInput.value || '',
        sesso: sessoInput.value || '',
        data_nascita: dataInput.value || '',
        citta_nascita: cittaInput.value || '',
        provincia_nascita: provinciaInput.value || '',
        stato_nascita: statoInput.value || '',
      };

      if (!payload.nome || !payload.cognome || !payload.sesso || !payload.data_nascita || !payload.citta_nascita) {
        return;
      }

      const url = new URL(baseUrl, window.location.origin);
      Object.entries(payload).forEach(function (entry) {
        url.searchParams.set(entry[0], String(entry[1]));
      });

      fetch(url.toString(), {
        method: 'GET',
        headers: {
          Accept: 'application/json',
        },
      })
        .then(function (response) {
          if (!response.ok) {
            return null;
          }
          return response.json();
        })
        .then(function (json) {
          if (!json || json.ok !== true || !json.codice_fiscale) {
            return;
          }

          cfInput.value = String(json.codice_fiscale).toUpperCase();
          cfInput.dataset.autoGenerated = '1';
        })
        .catch(function () {
          // Ignora errori di rete: il campo resta modificabile manualmente.
        });
    };

    const scheduleCompute = function () {
      if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
      }
      debounceTimer = window.setTimeout(compute, 250);
    };

    [nomeInput, cognomeInput, sessoInput, dataInput, cittaInput, provinciaInput, statoInput].forEach(function (field) {
      field.addEventListener('change', scheduleCompute);
      field.addEventListener('blur', scheduleCompute);
    });

    cfInput.addEventListener('input', function () {
      cfInput.dataset.autoGenerated = '0';
      if (String(cfInput.value || '').trim() === '') {
        cfInput.dataset.autoGenerated = '1';
      }
    });

    document.querySelectorAll('.js-cf-calc-btn[data-form-id="' + formId + '"]').forEach(function (button) {
      button.addEventListener('click', function () {
        compute(true);
      });
    });

    if (String(cfInput.value || '').trim() === '') {
      cfInput.dataset.autoGenerated = '1';
      scheduleCompute();
    }
  };

  const setupComuniSelect2 = function () {
    if (typeof window.jQuery === 'undefined' || !window.jQuery.fn || typeof window.jQuery.fn.select2 !== 'function') {
      return;
    }

    const $ = window.jQuery;
    const baseUrl = (window.SeiryokukaiConfig && window.SeiryokukaiConfig.api && window.SeiryokukaiConfig.api.atleti) || '';
    if (!baseUrl) {
      return;
    }

    $('.js-comune-select').each(function () {
      const $select = $(this);

      $select.select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Digita il comune',
        minimumInputLength: 2,
        language: {
          inputTooShort: function () {
            return 'Digita almeno 2 caratteri';
          },
          searching: function () {
            return 'Ricerca in corso...';
          },
          noResults: function () {
            return 'Nessun comune trovato';
          },
        },
        tags: true,
        createTag: function (params) {
          const term = String(params.term || '').trim();
          if (term === '') {
            return null;
          }

          return {
            id: term,
            text: term,
            isManual: true,
          };
        },
        ajax: {
          url: baseUrl,
          dataType: 'json',
          delay: 250,
          data: function (params) {
            return {
              action: 'search_comuni',
              q: params.term || '',
            };
          },
          processResults: function (data) {
            const rows = data && Array.isArray(data.results) ? data.results : [];
            return {
              results: rows.map(function (row) {
                const comune = String(row.comune || '').trim();
                const provincia = String(row.provincia || '').trim();
                const nazione = String(row.nazione || '').trim();
                const cap = String(row.cap || '').trim();
                return {
                  id: comune,
                  text: provincia !== '' ? comune + ' (' + provincia + ')' : comune,
                  comune: comune,
                  provincia: provincia,
                  nazione: nazione,
                  cap: cap,
                };
              }),
            };
          },
        },
      });

      $select.on('select2:select', function (event) {
        const data = event.params && event.params.data ? event.params.data : null;
        const selectedComune = data && data.comune ? String(data.comune) : String($select.val() || '');
        if (selectedComune !== '') {
          const option = new Option(selectedComune, selectedComune, true, true);
          $select.find('option').remove();
          $select.append(option).trigger('change.select2');
        }

        const targetId = String($select.data('province-target') || '');
        const provincia = data && data.provincia ? String(data.provincia) : '';
        if (targetId !== '' && provincia !== '') {
          const provinceInput = document.getElementById(targetId);
          if (provinceInput) {
            provinceInput.value = provincia;
          }
        }

        const countryTargetId = String($select.data('country-target') || '');
        const nazione = data && data.nazione ? String(data.nazione) : '';
        if (countryTargetId !== '' && nazione !== '') {
          const countryInput = document.getElementById(countryTargetId);
          if (countryInput) {
            countryInput.value = nazione;
          }
        }

        const capTargetId = String($select.data('cap-target') || '');
        const cap = data && data.cap ? String(data.cap) : '';
        if (capTargetId !== '' && cap !== '') {
          const capInput = document.getElementById(capTargetId);
          if (capInput) {
            capInput.value = cap;
          }
        }
      });
    });
  };

  if (typeof DataTable !== 'undefined') {
    const dataTableLangUrl =
      (window.SeiryokukaiConfig && window.SeiryokukaiConfig.dataTableLangUrl)
      || '';
    const api = (window.SeiryokukaiConfig && window.SeiryokukaiConfig.api) || {};
    const atletiApiUrl = api.atleti || '';
    const atletiPageUrl = <?= json_encode($atletiPageUrl, JSON_UNESCAPED_UNICODE) ?>;

    const escapeHtml = (value) => String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');

    new DataTable('#atleti-table', {
      serverSide: true,
      processing: true,
      pageLength: 10,
      order: [[0, 'desc']],
      ajax: {
        url: atletiApiUrl,
        type: 'GET',
      },
      language: {
        url: dataTableLangUrl,
      },
      columns: [
        { data: 'id' },
        { data: 'name' },
        { data: 'email' },
        { data: 'phone' },
        {
          data: 'status',
          render: function (data) {
            const active = data === 'Attivo';
            const cls = active ? 'success' : 'secondary';
            return '<span class="badge text-bg-' + cls + '">' + escapeHtml(data) + '</span>';
          },
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          className: 'text-end',
          render: function (row) {
            const id = Number(row.id || 0);
            const isActive = row.status === 'Attivo';
            const nextStatus = isActive ? 'Sospeso' : 'Attivo';
            const statusLabel = isActive ? 'Sospendi' : 'Attiva';
            const statusClass = isActive ? 'btn-outline-warning' : 'btn-outline-success';

            return ''
              + '<div class="d-flex justify-content-end gap-2 flex-wrap">'
              + '<a class="btn btn-sm btn-primary" href="' + escapeHtml(atletiPageUrl + '&open_edit=1&edit_id=' + id) + '">Scheda</a>'
              + '<form method="post" action="' + escapeHtml(atletiApiUrl) + '">'
              + '<input type="hidden" name="action" value="status">'
              + '<input type="hidden" name="id" value="' + id + '">'
              + '<input type="hidden" name="status" value="' + nextStatus + '">'
              + '<button class="btn btn-sm ' + statusClass + '" type="submit">' + statusLabel + '</button>'
              + '</form>'
              + '<form method="post" action="' + escapeHtml(atletiApiUrl) + '" onsubmit="return confirm(\'Eliminare questo atleta?\');">'
              + '<input type="hidden" name="action" value="delete">'
              + '<input type="hidden" name="id" value="' + id + '">'
              + '<button class="btn btn-sm btn-outline-danger" type="submit">Elimina</button>'
              + '</form>'
              + '</div>';
          },
        },
      ],
    });
  }

  const addPanel = document.getElementById('addAtletaPanel');
  const addAthleteForm = document.getElementById('addAthleteForm');
  const openAddBtn = document.getElementById('openAddAtletaPanelBtn');
  const closeAddBtn = document.getElementById('closeAddAtletaPanelBtn');
  const cancelAddBtn = document.getElementById('cancelAddAtletaBtn');
  const addAthleteImageInput = document.getElementById('addAthleteImageInput');
  const addAthleteImagePreviewWrap = document.getElementById('addAthleteImagePreviewWrap');
  const addAthleteImagePreview = document.getElementById('addAthleteImagePreview');
  const addTabInput = document.getElementById('addAthleteTabInput');
  const editTabInput = document.getElementById('editAthleteTabInput');
  const editAthleteImageInput = document.getElementById('editAthleteImageInput');
  const editAthleteImagePreviewWrap = document.getElementById('editAthleteImagePreviewWrap');
  const editAthleteImagePreview = document.getElementById('editAthleteImagePreview');
  const editAthleteRemoveImageCheckbox = document.getElementById('editAthleteRemoveImageCheckbox');
  const editAthleteRemoveImageInput = document.getElementById('editAthleteRemoveImageInput');

  const resetAddImagePreview = function () {
    if (addAthleteImagePreview) {
      addAthleteImagePreview.src = '';
    }
    if (addAthleteImagePreviewWrap) {
      addAthleteImagePreviewWrap.classList.add('d-none');
    }
  };

  const restoreAddFormFromQuery = function () {
    if (!addAthleteForm) {
      return;
    }

    const params = new URLSearchParams(window.location.search);
    params.forEach(function (value, key) {
      if (!key.startsWith('add_')) {
        return;
      }

      const fieldName = key.substring(4);
      if (!fieldName) {
        return;
      }

      const field = addAthleteForm.elements.namedItem(fieldName);
      if (!field) {
        return;
      }

      if (field instanceof RadioNodeList) {
        field.value = value;
        return;
      }

      if (field instanceof HTMLSelectElement) {
        const hasOption = Array.from(field.options).some(function (option) {
          return option.value === value;
        });

        if (!hasOption && value !== '') {
          field.add(new Option(value, value, true, true));
        }

        field.value = value;
        return;
      }

      if (field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement) {
        if (field.type === 'checkbox') {
          field.checked = ['1', 'true', 'on', 'yes'].includes(String(value).toLowerCase());
          return;
        }
        field.value = value;
      }
    });
  };

  restoreAddFormFromQuery();

  if (addAthleteForm) {
    addAthleteForm.addEventListener('submit', function (event) {
      if (!addAthleteForm.checkValidity()) {
        event.preventDefault();
        addAthleteForm.reportValidity();
      }
    });
  }

  if (addAthleteImageInput) {
    addAthleteImageInput.addEventListener('change', function () {
      const file = addAthleteImageInput.files && addAthleteImageInput.files[0] ? addAthleteImageInput.files[0] : null;
      if (!file) {
        resetAddImagePreview();
        return;
      }

      if (typeof file.type === 'string' && file.type.indexOf('image/') !== 0) {
        addAthleteImageInput.value = '';
        resetAddImagePreview();
        return;
      }

      const reader = new FileReader();
      reader.onload = function (event) {
        if (!addAthleteImagePreview || !addAthleteImagePreviewWrap) {
          return;
        }
        addAthleteImagePreview.src = String((event.target && event.target.result) || '');
        addAthleteImagePreviewWrap.classList.remove('d-none');
      };
      reader.onerror = function () {
        addAthleteImageInput.value = '';
        resetAddImagePreview();
      };
      reader.readAsDataURL(file);
    });
  }

  const resetEditImagePreview = function () {
    if (!editAthleteImagePreview || !editAthleteImagePreviewWrap) {
      return;
    }

    const initialSrc = String(editAthleteImagePreview.dataset.initialSrc || '');
    if (initialSrc === '') {
      editAthleteImagePreview.src = '';
      editAthleteImagePreviewWrap.classList.add('d-none');
      return;
    }

    editAthleteImagePreview.src = initialSrc;
    editAthleteImagePreviewWrap.classList.remove('d-none');
  };

  const applyEditImageRemovalState = function () {
    if (!editAthleteRemoveImageInput || !editAthleteRemoveImageCheckbox) {
      return;
    }

    const isRemoving = editAthleteRemoveImageCheckbox.checked;
    editAthleteRemoveImageInput.value = isRemoving ? '1' : '0';

    if (!editAthleteImagePreviewWrap || !editAthleteImagePreview) {
      return;
    }

    if (isRemoving) {
      editAthleteImagePreview.src = '';
      editAthleteImagePreviewWrap.classList.add('d-none');
      if (editAthleteImageInput) {
        editAthleteImageInput.value = '';
      }
      return;
    }

    if (editAthleteImageInput && editAthleteImageInput.files && editAthleteImageInput.files[0]) {
      return;
    }

    resetEditImagePreview();
  };

  if (editAthleteImageInput) {
    editAthleteImageInput.addEventListener('change', function () {
      const file = editAthleteImageInput.files && editAthleteImageInput.files[0] ? editAthleteImageInput.files[0] : null;
      if (!file) {
        if (editAthleteRemoveImageCheckbox && editAthleteRemoveImageCheckbox.checked) {
          return;
        }
        resetEditImagePreview();
        return;
      }

      if (typeof file.type === 'string' && file.type.indexOf('image/') !== 0) {
        editAthleteImageInput.value = '';
        resetEditImagePreview();
        return;
      }

      if (editAthleteRemoveImageCheckbox) {
        editAthleteRemoveImageCheckbox.checked = false;
      }
      if (editAthleteRemoveImageInput) {
        editAthleteRemoveImageInput.value = '0';
      }

      const reader = new FileReader();
      reader.onload = function (event) {
        if (!editAthleteImagePreview || !editAthleteImagePreviewWrap) {
          return;
        }
        editAthleteImagePreview.src = String((event.target && event.target.result) || '');
        editAthleteImagePreviewWrap.classList.remove('d-none');
      };
      reader.onerror = function () {
        editAthleteImageInput.value = '';
        resetEditImagePreview();
      };
      reader.readAsDataURL(file);
    });
  }

  if (editAthleteRemoveImageCheckbox) {
    editAthleteRemoveImageCheckbox.addEventListener('change', applyEditImageRemovalState);
    applyEditImageRemovalState();
  }

  const showAddPanel = function () {
    if (!addPanel) {
      return;
    }
    addPanel.classList.remove('d-none');
    addPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
  };

  const hideAddPanel = function () {
    if (!addPanel) {
      return;
    }
    addPanel.classList.add('d-none');
    if (addAthleteImageInput) {
      addAthleteImageInput.value = '';
    }
    resetAddImagePreview();
  };

  if (openAddBtn) {
    openAddBtn.addEventListener('click', showAddPanel);
  }
  if (closeAddBtn) {
    closeAddBtn.addEventListener('click', hideAddPanel);
  }
  if (cancelAddBtn) {
    cancelAddBtn.addEventListener('click', hideAddPanel);
  }

  document.querySelectorAll('.js-athlete-add-tab-trigger').forEach(function (tabButton) {
    tabButton.addEventListener('shown.bs.tab', function (event) {
      if (!addTabInput) {
        return;
      }
      const target = event.target.getAttribute('data-bs-target') || '';
      addTabInput.value = target.includes('contatti') ? 'contatti' : 'anagrafica';
    });
  });

  document.querySelectorAll('.js-athlete-edit-tab-trigger').forEach(function (tabButton) {
    tabButton.addEventListener('shown.bs.tab', function (event) {
      if (!editTabInput) {
        return;
      }
      const target = event.target.getAttribute('data-bs-target') || '';
      if (target.includes('contatti')) {
        editTabInput.value = 'contatti';
      } else if (target.includes('documenti')) {
        editTabInput.value = 'documenti';
      } else if (target.includes('iscrizioni')) {
        editTabInput.value = 'iscrizioni';
      } else if (target.includes('pagamenti')) {
        editTabInput.value = 'pagamenti';
      } else {
        editTabInput.value = 'anagrafica';
      }
    });
  });

  const editPanel = document.getElementById('editAtletaPanel');
  if (editPanel && !editPanel.classList.contains('d-none')) {
    editPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  const editDocumentoPanel = document.getElementById('editDocumentoPanel');
  const closeEditDocumentoPanelBtn = document.getElementById('closeEditDocumentoPanelBtn');
  const editDocumentoId = document.getElementById('editDocumentoId');
  const editDocumentoType = document.getElementById('editDocumentoType');
  const editDocumentoDescription = document.getElementById('editDocumentoDescription');
  const editDocumentoDate = document.getElementById('editDocumentoDate');
  const editDocumentoExpiryDate = document.getElementById('editDocumentoExpiryDate');
  const editDocumentoUrl = document.getElementById('editDocumentoUrl');

  document.querySelectorAll('.js-edit-documento-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!editDocumentoPanel) {
        return;
      }

      if (editDocumentoId) {
        editDocumentoId.value = btn.getAttribute('data-document-id') || '';
      }
      if (editDocumentoType) {
        editDocumentoType.value = btn.getAttribute('data-type-id') || '';
      }
      if (editDocumentoDescription) {
        editDocumentoDescription.value = btn.getAttribute('data-description') || '';
      }
      if (editDocumentoDate) {
        editDocumentoDate.value = btn.getAttribute('data-document-date') || '';
      }
      if (editDocumentoExpiryDate) {
        editDocumentoExpiryDate.value = btn.getAttribute('data-expiry-date') || '';
      }
      if (editDocumentoUrl) {
        editDocumentoUrl.value = btn.getAttribute('data-url') || '';
      }

      editDocumentoPanel.classList.remove('d-none');
      editDocumentoPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
  });

  if (closeEditDocumentoPanelBtn && editDocumentoPanel) {
    closeEditDocumentoPanelBtn.addEventListener('click', function () {
      editDocumentoPanel.classList.add('d-none');
    });
  }

  setupCodiceFiscaleAutocalcolo('addAthleteForm');
  setupCodiceFiscaleAutocalcolo('editAthleteProfileForm');
  setupComuniSelect2();
});
</script>
