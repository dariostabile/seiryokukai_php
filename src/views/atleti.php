<?php

declare(strict_types=1);

/** @var array $atleti */
/** @var array $tipiDocumenti */
/** @var array $corsi */
/** @var array|null $selectedAtleta */

$frontendApi = frontend_api_urls();
$atletiApiUrl = (string) ($frontendApi['atleti'] ?? '');
$frontendAssets = frontend_asset_urls();
$appPaths = app_paths();
$indexPath = (string) ($appPaths['index'] ?? '/seiryokukai_php/public/index.php');
$atletiPageUrl = $indexPath . '?page=atleti';

$okMessage = trim((string) ($_GET['ok'] ?? ''));
$errMessage = trim((string) ($_GET['err'] ?? ''));
$openAddPanel = ((string) ($_GET['open_add'] ?? '0')) === '1';
$activeTab = trim((string) ($_GET['athlete_tab'] ?? 'anagrafica'));
$allowedTabs = ['anagrafica', 'contatti', 'misure', 'documenti', 'iscrizioni', 'pagamenti'];
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

$addAthleteFormValues = [
  'status' => 'Attivo',
  'sesso' => '',
  'data_scadenza_account' => $addPrefill['data_scadenza_account'],
  'cognome' => $addPrefill['cognome'],
  'nome' => $addPrefill['nome'],
  'data_nascita' => '',
  'citta_nascita' => '',
  'provincia_nascita' => '',
  'stato_nascita' => '',
  'codice_fiscale' => '',
  'piva' => $addPrefill['piva'],
  'codice_univoco_fatturazione' => $addPrefill['codice_univoco_fatturazione'],
  'note_atleta' => '',
  'indirizzo_residenza' => '',
  'citta_residenza' => '',
  'provincia_residenza' => '',
  'cap_residenza' => '',
  'stato_residenza' => '',
  'telefono_1' => $addPrefill['telefono_1'],
  'telefono_2' => '',
  'email_1' => $addPrefill['email_1'],
  'email_2' => $addPrefill['email_2'],
  'pec' => $addPrefill['pec'],
  'altezza' => '',
  'peso' => '',
  'misura' => '',
  'misura_maglia' => '',
  'misura_pantaloni' => '',
  'image_url' => '',
];

if (!$openAddPanel) {
    $openAddPanel = $addPrefill['nome'] !== ''
        || $addPrefill['cognome'] !== ''
        || $addPrefill['email_1'] !== ''
        || $addPrefill['telefono_1'] !== '';
}

$hasSelectedAtleta = is_array($selectedAtleta ?? null);
$openEditPanel = $hasSelectedAtleta && (((string) ($_GET['open_edit'] ?? '0')) === '1' || (int) ($_GET['edit_id'] ?? 0) > 0);

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

$editAthleteFormValues = [
  'status' => 'Attivo',
  'sesso' => '',
  'data_scadenza_account' => '',
  'cognome' => '',
  'nome' => '',
  'data_nascita' => '',
  'citta_nascita' => '',
  'provincia_nascita' => '',
  'stato_nascita' => '',
  'codice_fiscale' => '',
  'piva' => '',
  'codice_univoco_fatturazione' => '',
  'note_atleta' => '',
  'indirizzo_residenza' => '',
  'citta_residenza' => '',
  'provincia_residenza' => '',
  'cap_residenza' => '',
  'stato_residenza' => '',
  'telefono_1' => '',
  'telefono_2' => '',
  'email_1' => '',
  'email_2' => '',
  'pec' => '',
  'altezza' => '',
  'peso' => '',
  'misura' => '',
  'misura_maglia' => '',
  'misura_pantaloni' => '',
  'image_url' => '',
];

if ($hasSelectedAtleta) {
  $editAthleteFormValues = [
    'status' => (string) ($selectedAtleta['status'] ?? 'Attivo'),
    'sesso' => (string) ($selectedAtleta['gender'] ?? ''),
    'data_scadenza_account' => (string) ($selectedAtleta['account_expiry_date'] ?? ''),
    'cognome' => (string) ($selectedAtleta['last_name'] ?? ''),
    'nome' => (string) ($selectedAtleta['first_name'] ?? ''),
    'data_nascita' => (string) ($selectedAtleta['birth_date'] ?? ''),
    'citta_nascita' => (string) ($selectedAtleta['birth_city'] ?? ''),
    'provincia_nascita' => (string) ($selectedAtleta['birth_province'] ?? ''),
    'stato_nascita' => (string) ($selectedAtleta['birth_country'] ?? ''),
    'codice_fiscale' => (string) ($selectedAtleta['tax_code'] ?? ''),
    'piva' => (string) ($selectedAtleta['vat_number'] ?? ''),
    'codice_univoco_fatturazione' => (string) ($selectedAtleta['invoice_code'] ?? ''),
    'note_atleta' => (string) ($selectedAtleta['notes'] ?? ''),
    'indirizzo_residenza' => (string) ($selectedAtleta['address'] ?? ''),
    'citta_residenza' => (string) ($selectedAtleta['city'] ?? ''),
    'provincia_residenza' => (string) ($selectedAtleta['province'] ?? ''),
    'cap_residenza' => (string) ($selectedAtleta['postal_code'] ?? ''),
    'stato_residenza' => (string) ($selectedAtleta['country'] ?? ''),
    'telefono_1' => (string) ($selectedAtleta['phone'] ?? ''),
    'telefono_2' => (string) ($selectedAtleta['phone_alt'] ?? ''),
    'email_1' => (string) ($selectedAtleta['email'] ?? ''),
    'email_2' => (string) ($selectedAtleta['email_alt'] ?? ''),
    'pec' => (string) ($selectedAtleta['pec'] ?? ''),
    'altezza' => (string) ($selectedAtleta['height'] ?? ''),
    'peso' => (string) ($selectedAtleta['weight'] ?? ''),
    'misura' => (string) ($selectedAtleta['size'] ?? ''),
    'misura_maglia' => (string) ($selectedAtleta['shirt_size'] ?? ''),
    'misura_pantaloni' => (string) ($selectedAtleta['pants_size'] ?? ''),
    'image_url' => (string) ($selectedAtleta['image_url'] ?? ''),
  ];
}
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
      <div>
        <h5 class="m-0">Gestione Atleti</h5>
        <small class="text-muted">Scheda atleta con tab anagrafica, contatti, misure, documenti/certificati, iscrizioni e pagamenti.</small>
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
            <th>Foto</th>
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
          <input type="hidden" name="form_action" value="add">
          <input type="hidden" name="athlete_tab" id="addAthleteTabInput" value="anagrafica">
          <input type="hidden" name="crop_image_base64_add" id="addAthleteCropImageData">

          <?php
          $athleteNavTabs = [
              [
                  'label' => 'Anagrafica',
                  'enabled' => true,
                  'active' => true,
                  'target' => '#add-athlete-anagrafica',
                  'trigger_class' => 'js-athlete-add-tab-trigger',
              ],
              [
                  'label' => 'Contatti',
                  'enabled' => true,
                  'active' => false,
                  'target' => '#add-athlete-contatti',
                  'trigger_class' => 'js-athlete-add-tab-trigger',
              ],
              [
                  'label' => 'Misure',
                  'enabled' => true,
                  'active' => false,
                  'target' => '#add-athlete-misure',
                  'trigger_class' => 'js-athlete-add-tab-trigger',
              ],
              [
                  'label' => 'Documenti/Certificati',
                  'enabled' => false,
                  'active' => false,
              ],
              [
                  'label' => 'Iscrizioni',
                  'enabled' => false,
                  'active' => false,
              ],
              [
                  'label' => 'Pagamenti',
                  'enabled' => false,
                  'active' => false,
              ],
          ];
          $athleteNavTabsExtraClass = 'col-12';
          require __DIR__ . '/partials/atleta_form_nav_tabs.php';
          ?>

          <div class="tab-content border border-top-0 rounded-bottom p-3 col-12">
            <?php
            $athleteFormId = 'addAthleteForm';
            $athleteFormValues = $addAthleteFormValues;
            $athleteTabPaneClasses = [
                'anagrafica' => 'tab-pane fade show active',
                'contatti' => 'tab-pane fade',
                'misure' => 'tab-pane fade',
            ];
            $athletePaneIds = [
                'anagrafica' => 'add-athlete-anagrafica',
                'contatti' => 'add-athlete-contatti',
                'misure' => 'add-athlete-misure',
            ];
            $athleteShowIntroAlert = true;
            $athleteShowTabSaveButtons = false;
            $athleteImageInputId = 'addAthleteImageInput';
            $athleteImagePreviewWrapId = 'addAthleteImagePreviewWrap';
            $athleteImagePreviewId = 'addAthleteImagePreview';
            $athleteImagePreviewWrapClass = 'd-none';
            $athleteImageRemoveCheckboxId = '';
            $athleteImagePlaceholderId = 'addAthleteImagePlaceholder';
            $athleteImageCropContainerId = 'addAthleteCropContainer';
            $athleteImageCropSourceId = 'addAthleteCropSource';
            $athleteImageApplyCropButtonId = 'applyAddAthleteImageCropBtn';
            $athleteImageCancelCropButtonId = 'cancelAddAthleteImageCropBtn';
            $athleteBirthCitySelectId = 'add_citta_nascita';
            $athleteBirthProvinceInputId = 'add_provincia_nascita';
            $athleteBirthCountryInputId = 'add_nazione_nascita';
            $athleteResidenceCitySelectId = 'add_citta_residenza';
            $athleteResidenceProvinceInputId = 'add_provincia_residenza';
            $athleteResidenceCapInputId = 'add_cap_residenza';
            $athleteResidenceCountryInputId = 'add_stato_residenza';

            require __DIR__ . '/partials/atleta_form_tabs.php';
            ?>
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
            <input type="hidden" name="current_image_path" id="editAthleteCurrentImagePath" value="<?= htmlspecialchars((string) ($selectedAtleta['image_path'] ?? '')) ?>">
            <input type="hidden" name="remove_image" id="editAthleteRemoveImageInput" value="0">
            <input type="hidden" name="crop_image_base64" id="editAthleteCropImageData">
          </form>

          <?php
          $athleteNavTabs = [
              [
                  'label' => 'Anagrafica',
                  'enabled' => true,
                  'active' => $activeTab === 'anagrafica',
                  'target' => '#athlete-tab-anagrafica',
                  'trigger_class' => 'js-athlete-edit-tab-trigger',
              ],
              [
                  'label' => 'Contatti',
                  'enabled' => true,
                  'active' => $activeTab === 'contatti',
                  'target' => '#athlete-tab-contatti',
                  'trigger_class' => 'js-athlete-edit-tab-trigger',
              ],
              [
                  'label' => 'Misure',
                  'enabled' => true,
                  'active' => $activeTab === 'misure',
                  'target' => '#athlete-tab-misure',
                  'trigger_class' => 'js-athlete-edit-tab-trigger',
              ],
              [
                  'label' => 'Documenti/Certificati',
                  'enabled' => true,
                  'active' => $activeTab === 'documenti',
                  'target' => '#athlete-tab-documenti',
                  'trigger_class' => 'js-athlete-edit-tab-trigger',
              ],
              [
                  'label' => 'Iscrizioni',
                  'enabled' => true,
                  'active' => $activeTab === 'iscrizioni',
                  'target' => '#athlete-tab-iscrizioni',
                  'trigger_class' => 'js-athlete-edit-tab-trigger',
              ],
              [
                  'label' => 'Pagamenti',
                  'enabled' => true,
                  'active' => $activeTab === 'pagamenti',
                  'target' => '#athlete-tab-pagamenti',
                  'trigger_class' => 'js-athlete-edit-tab-trigger',
              ],
          ];
          $athleteNavTabsExtraClass = '';
          require __DIR__ . '/partials/atleta_form_nav_tabs.php';
          ?>

          <div class="tab-content border border-top-0 rounded-bottom p-3">
            <?php
            $athleteFormId = 'editAthleteProfileForm';
            $athleteFormValues = $editAthleteFormValues;
            $athleteTabPaneClasses = [
                'anagrafica' => $tabPaneClass('anagrafica'),
                'contatti' => $tabPaneClass('contatti'),
                'misure' => $tabPaneClass('misure'),
            ];
            $athletePaneIds = [
                'anagrafica' => 'athlete-tab-anagrafica',
                'contatti' => 'athlete-tab-contatti',
                'misure' => 'athlete-tab-misure',
            ];
            $athleteShowIntroAlert = false;
            $athleteShowTabSaveButtons = true;
            $athleteImageInputId = 'editAthleteImageInput';
            $athleteImagePreviewWrapId = 'editAthleteImagePreviewWrap';
            $athleteImagePreviewId = 'editAthleteImagePreview';
            $athleteImagePreviewWrapClass = $editAthleteFormValues['image_url'] === '' ? 'd-none' : '';
            $athleteImageRemoveCheckboxId = 'editAthleteRemoveImageCheckbox';
            $athleteImagePlaceholderId = 'editAthleteImagePlaceholder';
            $athleteImageCropContainerId = 'editAthleteCropContainer';
            $athleteImageCropSourceId = 'editAthleteCropSource';
            $athleteImageApplyCropButtonId = 'applyEditAthleteImageCropBtn';
            $athleteImageCancelCropButtonId = 'cancelEditAthleteImageCropBtn';
            $athleteBirthCitySelectId = 'edit_citta_nascita';
            $athleteBirthProvinceInputId = 'edit_provincia_nascita';
            $athleteBirthCountryInputId = 'edit_stato_nascita';
            $athleteResidenceCitySelectId = 'edit_citta_residenza';
            $athleteResidenceProvinceInputId = 'edit_provincia_residenza';
            $athleteResidenceCapInputId = 'edit_cap_residenza';
            $athleteResidenceCountryInputId = 'edit_stato_residenza';

            require __DIR__ . '/partials/atleta_form_tabs.php';
            ?>

            <div class="<?= $tabPaneClass('documenti') ?>" id="athlete-tab-documenti" role="tabpanel">
              <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                <div>
                  <h6 class="mb-1">Documenti caricati</h6>
                  <small class="text-muted">Consulta l'archivio e aggiungi nuovi documenti da qui.</small>
                </div>
                <button type="button" class="btn btn-outline-primary" id="openAddDocumentoPanelBtn">Nuovo documento</button>
              </div>

              <div class="table-responsive">
                <table id="documenti-table" class="table table-sm align-middle w-100 js-datatable">
                  <thead>
                    <tr>
                      <th>Tipo</th>
                      <th>Descrizione</th>
                      <th>Data</th>
                      <th>Scadenza</th>
                      
                      <th class="text-end">Azioni</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($selectedDocumenti !== []): ?>
                      <?php foreach ($selectedDocumenti as $documento): ?>
                        <tr>
                          <td><?= htmlspecialchars((string) ($documento['type_name'] ?? '')) ?></td>
                          <td><?= htmlspecialchars((string) ($documento['description'] ?? '')) ?></td>
                          <td data-order="<?= htmlspecialchars((string) ($documento['document_date'] ?? '')) ?>">
                            <?php
                              $date = (string) ($documento['document_date'] ?? '');
                              if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                                [$y, $m, $d] = explode('-', $date);
                                echo htmlspecialchars("$d/$m/$y");
                              } else {
                                echo htmlspecialchars($date);
                              }
                            ?>
                          </td>
                          <td data-order="<?= htmlspecialchars((string) ($documento['expiry_date'] ?? '')) ?>">
                            <?php
                              $date = (string) ($documento['expiry_date'] ?? '');
                              if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                                [$y, $m, $d] = explode('-', $date);
                                echo htmlspecialchars("$d/$m/$y");
                              } else {
                                echo htmlspecialchars($date);
                              }
                            ?>
                          </td>
                          <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                              <?php if (((string) ($documento['public_url'] ?? '')) !== ''): ?>
                                <a href="<?= htmlspecialchars((string) $documento['public_url']) ?>" target="_blank" rel="noreferrer" class="btn btn-sm btn-outline-secondary" download>
                                  Download
                                </a>
                              <?php endif; ?>
                              <button
                                type="button"
                                class="btn btn-sm btn-outline-primary js-edit-documento-btn"
                                data-document-id="<?= (int) ($documento['id'] ?? 0) ?>"
                                data-type-id="<?= (int) ($documento['type_id'] ?? 0) ?>"
                                data-description="<?= htmlspecialchars((string) ($documento['description'] ?? ''), ENT_QUOTES) ?>"
                                data-document-date="<?= htmlspecialchars((string) ($documento['document_date'] ?? ''), ENT_QUOTES) ?>"
                                data-expiry-date="<?= htmlspecialchars((string) ($documento['expiry_date'] ?? ''), ENT_QUOTES) ?>"
                              >Modifica</button>
                              <form method="post" action="<?= htmlspecialchars($atletiApiUrl) ?>" onsubmit="return confirm('Eliminare questo documento?');" style="display:inline;">
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

              <div id="addDocumentoPanel" class="card border mt-3 d-none">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h6 class="m-0">Nuovo documento</h6>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="closeAddDocumentoPanelBtn">Chiudi</button>
                </div>
                <div class="card-body">
                  <?php
                  $formAction = $atletiApiUrl;
                  $formId = 'addDocumentoForm';
                  $submitLabel = 'Aggiungi documento';
                  $hiddenFields = [
                    '<input type="hidden" name="action" value="add_documento">',
                    '<input type="hidden" name="idatleta" value="' . (int)($selectedAtleta['id'] ?? 0) . '">',
                  ];
                  $values = [];
                  $isEdit = false;
                  require __DIR__ . '/partials/documento_form.php';
                  ?>
                </div>
              </div>

              <div id="editDocumentoPanel" class="card border mt-3 d-none">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h6 class="m-0">Modifica documento</h6>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="closeEditDocumentoPanelBtn">Chiudi</button>
                </div>
                <div class="card-body">
                  <?php
                  $formAction = $atletiApiUrl;
                  $formId = 'editDocumentoForm';
                  $submitLabel = 'Salva modifiche';
                  $hiddenFields = [
                    '<input type="hidden" name="action" value="update_documento">',
                    '<input type="hidden" name="idatleta" value="' . (int)($selectedAtleta['id'] ?? 0) . '">',
                    '<input type="hidden" name="iddocumento" id="editDocumentoId">',
                  ];
                  // I valori saranno popolati via JS all'apertura del pannello modifica
                  $values = [
                    'idtipo_documento' => '',
                    'descrizione_documento' => '',
                    'data_documento' => '',
                    'data_scadenza' => '',
                  ];
                  $isEdit = true;
                  require __DIR__ . '/partials/documento_form.php';
                  ?>
                </div>
              </div>
            </div>

            <div class="<?= $tabPaneClass('iscrizioni') ?>" id="athlete-tab-iscrizioni" role="tabpanel">
              <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                <div>
                  <h6 class="mb-1">Iscrizioni registrate</h6>
                  <small class="text-muted">Consulta le iscrizioni esistenti e aggiungine una nuova.</small>
                </div>
                <button type="button" class="btn btn-outline-primary" id="openAddIscrizionePanelBtn">Nuova iscrizione</button>
              </div>

              <div class="table-responsive">
                <table id="iscrizioni-table" class="table table-sm align-middle w-100">
                  <thead>
                    <tr>
                      <th>Corso</th>
                      <th>Periodo</th>
                      <th>Quota</th>
                      <th>Stato</th>
                      <th>Note</th>
                      <th class="text-end">Azioni</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($selectedIscrizioni !== []): ?>
                      <?php foreach ($selectedIscrizioni as $iscrizione): ?>
                        <tr>
                          <td><?= htmlspecialchars((string) ($iscrizione['courses'] ?? '')) ?></td>
                          <td data-order="<?= htmlspecialchars((string) ($iscrizione['start_date'] ?? '')) ?>">
                            <?php
                              $date = (string) ($iscrizione['start_date'] ?? '');
                              if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                                [$y, $m, $d] = explode('-', $date);
                                echo htmlspecialchars("$d/$m/$y");
                              } else {
                                echo htmlspecialchars($date);
                              }
                            ?>
                            <?php if (((string) ($iscrizione['end_date'] ?? '')) !== ''): ?>
                              – <?php
                                $date = (string) $iscrizione['end_date'];
                                if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                                  [$y, $m, $d] = explode('-', $date);
                                  echo htmlspecialchars("$d/$m/$y");
                                } else {
                                  echo htmlspecialchars($date);
                                }
                              ?>
                            <?php endif; ?>
                          </td>
                          <td><?= htmlspecialchars((string) ($iscrizione['total'] ?? '')) ?></td>
                          <td><?= htmlspecialchars((string) ($iscrizione['status_label'] ?? '')) ?></td>
                          <td><?= htmlspecialchars((string) ($iscrizione['notes'] ?? '')) ?></td>
                          <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                              <button
                                type="button"
                                class="btn btn-sm btn-outline-primary js-edit-iscrizione-btn"
                                data-idcorso="<?= (int) ($iscrizione['course_id'] ?? 0) ?>"
                                data-corso="<?= htmlspecialchars((string) ($iscrizione['courses'] ?? ''), ENT_QUOTES) ?>"
                                data-start-date="<?= htmlspecialchars((string) ($iscrizione['start_date'] ?? ''), ENT_QUOTES) ?>"
                                data-end-date="<?= htmlspecialchars((string) ($iscrizione['end_date'] ?? ''), ENT_QUOTES) ?>"
                                data-total="<?= htmlspecialchars((string) ($iscrizione['total'] ?? ''), ENT_QUOTES) ?>"
                                data-status="<?= htmlspecialchars((string) ($iscrizione['status_code'] ?? 'A'), ENT_QUOTES) ?>"
                                data-notes="<?= htmlspecialchars((string) ($iscrizione['notes'] ?? ''), ENT_QUOTES) ?>"
                              >Modifica</button>
                              <form method="post" action="<?= htmlspecialchars($atletiApiUrl) ?>" onsubmit="return confirm('Eliminare questa iscrizione al corso?');" style="display:inline;">
                                <input type="hidden" name="action" value="delete_iscrizione">
                                <input type="hidden" name="idatleta" value="<?= (int) ($selectedAtleta['id'] ?? 0) ?>">
                                <input type="hidden" name="idcorso" value="<?= (int) ($iscrizione['course_id'] ?? 0) ?>">
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

              <div id="addIscrizionePanel" class="card border mt-3 d-none">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h6 class="m-0">Nuova iscrizione</h6>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="closeAddIscrizionePanelBtn">Chiudi</button>
                </div>
                <div class="card-body">
                  <form method="post" action="<?= htmlspecialchars($atletiApiUrl) ?>" class="row g-3" id="addIscrizioneForm">
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
                      <select class="form-select" name="course_ids[]" multiple size="5" required>
                        <?php foreach ($corsi as $corso): ?>
                          <option value="<?= (int) ($corso['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($corso['name'] ?? '')) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <small class="text-muted">Seleziona almeno un corso. Usa Cmd/Ctrl + click per selezioni multiple.</small>
                    </div>
                    <div class="col-12">
                      <label class="form-label">Note iscrizione</label>
                      <textarea class="form-control" rows="3" name="note_iscrizione"></textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                      <button class="btn btn-outline-primary" type="submit">Aggiungi iscrizione</button>
                    </div>
                  </form>
                </div>
              </div>

              <div id="editIscrizionePanel" class="card border mt-3 d-none">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h6 class="m-0">Modifica iscrizione</h6>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="closeEditIscrizionePanelBtn">Chiudi</button>
                </div>
                <div class="card-body">
                  <form method="post" action="<?= htmlspecialchars($atletiApiUrl) ?>" class="row g-3" id="editIscrizioneForm">
                    <input type="hidden" name="action" value="update_iscrizione">
                    <input type="hidden" name="idatleta" value="<?= (int) ($selectedAtleta['id'] ?? 0) ?>">
                    <input type="hidden" name="idcorso_attuale" id="editIscrizioneIdCorsoAttuale">
                    <div class="col-12 col-md-6">
                      <label class="form-label">Corsi collegati</label>
                      <select class="form-select" name="course_ids[]" id="editIscrizioneCourseIds" multiple size="5" required>
                        <?php foreach ($corsi as $corso): ?>
                          <option value="<?= (int) ($corso['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($corso['name'] ?? '')) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <small class="text-muted">Mantieni selezionato almeno un corso.</small>
                    </div>
                    <div class="col-12 col-md-3">
                      <label class="form-label">Data inizio</label>
                      <input type="date" class="form-control" name="data_inizio_iscrizione" id="editIscrizioneDataInizio" required>
                    </div>
                    <div class="col-12 col-md-3">
                      <label class="form-label">Data fine</label>
                      <input type="date" class="form-control" name="data_fine_iscrizione" id="editIscrizioneDataFine">
                    </div>
                    <div class="col-12 col-md-3">
                      <label class="form-label">Totale iscrizione</label>
                      <input type="number" step="0.01" min="0" class="form-control" name="totale_iscrizione" id="editIscrizioneTotale">
                    </div>
                    <div class="col-12 col-md-3">
                      <label class="form-label">Stato</label>
                      <select class="form-select" name="stato_iscrizione" id="editIscrizioneStato" required>
                        <option value="A">Attiva</option>
                        <option value="S">Sospesa</option>
                        <option value="C">Conclusa</option>
                      </select>
                    </div>
                    <div class="col-12">
                      <label class="form-label">Note iscrizione</label>
                      <textarea class="form-control" rows="3" name="note_iscrizione" id="editIscrizioneNote"></textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2">
                      <button class="btn btn-outline-secondary" type="button" id="closeEditIscrizionePanelBtnFooter">Annulla</button>
                      <button class="btn btn-primary" type="submit">Salva modifiche</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <div class="<?= $tabPaneClass('pagamenti') ?>" id="athlete-tab-pagamenti" role="tabpanel">
              <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                <div>
                  <h6 class="mb-1">Pagamenti registrati</h6>
                  <small class="text-muted">Consulta i pagamenti inseriti e registrane uno nuovo.</small>
                </div>
                <button type="button" class="btn btn-outline-primary" id="openAddPagamentoPanelBtn" <?= $selectedIscrizioni === [] ? 'disabled' : '' ?>>Nuovo pagamento</button>
              </div>

              <?php
                $paymentCourseOptions = [];
                foreach ($selectedIscrizioni as $iscrizione) {
                  $courseId = (int) ($iscrizione['course_id'] ?? $iscrizione['id'] ?? 0);
                  if ($courseId <= 0 || isset($paymentCourseOptions[$courseId])) {
                    continue;
                  }
                  $paymentCourseOptions[$courseId] = $iscrizione;
                }
              ?>

              <?php if ($selectedIscrizioni === []): ?>
                <div class="alert alert-info py-2" role="alert">
                  Aggiungi prima almeno un corso nel tab Iscrizioni.
                </div>
              <?php endif; ?>

              <div class="table-responsive">
                <table id="pagamenti-table" class="table table-sm align-middle w-100">
                  <thead>
                    <tr>
                      <th>Data pagamento</th>
                      <th>Scadenza</th>
                      <th>Corso</th>
                      <th>Importo</th>
                      <th>Note</th>
                      <th class="text-end">Azioni</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($selectedPagamenti !== []): ?>
                      <?php
                        // Trova l'ultimo pagamento per ciascun corso
                        $lastPagamentiByCorso = [];
                        foreach ($selectedPagamenti as $idx => $pagamento) {
                          $cid = (string)($pagamento['course_id'] ?? '');
                          $date = (string)($pagamento['payment_date'] ?? '');
                          if (!isset($lastPagamentiByCorso[$cid]) || strcmp($date, $lastPagamentiByCorso[$cid]['payment_date']) > 0) {
                            $lastPagamentiByCorso[$cid] = [
                              'idx' => $idx,
                              'payment_date' => $date
                            ];
                          }
                        }
                      ?>
                      <?php foreach ($selectedPagamenti as $idx => $pagamento): ?>
                        <tr>
                          <td data-order="<?= htmlspecialchars((string) ($pagamento['payment_date'] ?? '')) ?>">
                            <?php
                              $date = (string) ($pagamento['payment_date'] ?? '');
                              if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                                [$y, $m, $d] = explode('-', $date);
                                echo htmlspecialchars("$d/$m/$y");
                              } else {
                                echo htmlspecialchars($date);
                              }
                            ?>
                          </td>
                          <td data-order="<?= htmlspecialchars((string) ($pagamento['expiry_date'] ?? '')) ?>">
                            <?php
                              $date = (string) ($pagamento['expiry_date'] ?? '');
                              if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                                [$y, $m, $d] = explode('-', $date);
                                echo htmlspecialchars("$d/$m/$y");
                              } else {
                                echo htmlspecialchars($date);
                              }
                            ?>
                          </td>
                          <td data-course-id="<?= (int)($pagamento['course_id'] ?? 0) ?>">
                            <?= htmlspecialchars((string) ($pagamento['course_name'] ?? '')) ?>
                            <?php if (((string) ($pagamento['course_name'] ?? '')) === ''): ?>
                              #<?= (int) ($pagamento['course_id'] ?? 0) ?>
                            <?php endif; ?>
                          </td>
                          <td><?= htmlspecialchars((string) ($pagamento['amount'] ?? '')) ?></td>
                          <td><?= htmlspecialchars((string) ($pagamento['notes'] ?? '')) ?></td>
                          <?php
                            $cid = (string)($pagamento['course_id'] ?? '');
                            $isLast = isset($lastPagamentiByCorso[$cid]) && $lastPagamentiByCorso[$cid]['idx'] === $idx;
                          ?>
                          <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                              <?php if ($isLast): ?>
                                <button
                                  type="button"
                                  class="btn btn-sm btn-outline-primary js-edit-pagamento-btn"
                                  data-pagamento-id="<?= (int)($pagamento['id'] ?? 0) ?>"
                                  data-course-id="<?= (int)($pagamento['course_id'] ?? 0) ?>"
                                  data-payment-date="<?= htmlspecialchars((string) ($pagamento['payment_date'] ?? ''), ENT_QUOTES) ?>"
                                  data-expiry-date="<?= htmlspecialchars((string) ($pagamento['expiry_date'] ?? ''), ENT_QUOTES) ?>"
                                  data-amount="<?= htmlspecialchars((string) ($pagamento['amount'] ?? ''), ENT_QUOTES) ?>"
                                  data-notes="<?= htmlspecialchars((string) ($pagamento['notes'] ?? ''), ENT_QUOTES) ?>"
                                >Modifica</button>
                                <form method="post" action="<?= htmlspecialchars($atletiApiUrl) ?>" onsubmit="return confirm('Eliminare questo pagamento?');" style="display:inline;">
                                  <input type="hidden" name="action" value="delete_pagamento">
                                  <input type="hidden" name="idatleta" value="<?= (int)($selectedAtleta['id'] ?? 0) ?>">
                                  <input type="hidden" name="idpagamento" value="<?= (int)($pagamento['id'] ?? 0) ?>">
                                  <button class="btn btn-sm btn-outline-danger" type="submit">Elimina</button>
                                </form>
                              <?php else: ?>
                                <span class="text-muted" data-bs-toggle="tooltip" title="Solo l'ultimo pagamento per corso puo essere modificato o eliminato">
                                  <i class="bi bi-lock" aria-hidden="true"></i>
                                </span>
                              <?php endif; ?>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

              <div id="addPagamentoPanel" class="card border mt-3 d-none">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h6 class="m-0">Nuovo pagamento</h6>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="closeAddPagamentoPanelBtn">Chiudi</button>
                </div>
                <div class="card-body">
                  <form method="post" action="<?= htmlspecialchars($atletiApiUrl) ?>" class="row g-3" id="addPagamentoForm">
                    <input type="hidden" name="action" value="add_pagamento">
                    <input type="hidden" name="idatleta" value="<?= (int) ($selectedAtleta['id'] ?? 0) ?>">
                    <div class="col-12 col-md-3">
                      <label class="form-label">Corso iscritto</label>
                      <select class="form-select" name="idcorso" <?= $selectedIscrizioni === [] ? 'disabled' : 'required' ?>>
                        <option value="">Seleziona</option>
                        <?php foreach ($paymentCourseOptions as $iscrizione): ?>
                          <option value="<?= (int) ($iscrizione['course_id'] ?? $iscrizione['id'] ?? 0) ?>">
                            #<?= (int) ($iscrizione['course_id'] ?? $iscrizione['id'] ?? 0) ?> - <?= htmlspecialchars((string) ($iscrizione['courses'] ?? '')) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-12 col-md-3">
                      <label class="form-label">Importo</label>
                      <input type="number" step="0.01" min="0" class="form-control" name="quota_pagamento" required>
                    </div>
                    <div class="col-12 col-md-3">
                      <label class="form-label">Data pagamento</label>
                      <input type="date" class="form-control" name="data_pagamento" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-12 col-md-3">
                      <label class="form-label">Data scadenza</label>
                      <input type="date" class="form-control" name="data_scadenza">
                    </div>
                    <div class="col-6">
                      <label class="form-label">Note pagamento</label>
                      <textarea class="form-control" rows="3" name="note_pagamento"></textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                      <button class="btn btn-outline-primary" type="submit" <?= $selectedIscrizioni === [] ? 'disabled' : '' ?>>Registra pagamento</button>
                    </div>
                  </form>
                </div>
              </div>

              <div id="editPagamentoPanel" class="card border mt-3 d-none">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h6 class="m-0">Modifica pagamento</h6>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="closeEditPagamentoPanelBtn">Chiudi</button>
                </div>
                <div class="card-body">
                  <form method="post" action="<?= htmlspecialchars($atletiApiUrl) ?>" class="row g-3" id="editPagamentoForm">
                    <input type="hidden" name="action" value="update_pagamento">
                    <input type="hidden" name="idatleta" value="<?= (int) ($selectedAtleta['id'] ?? 0) ?>">
                    <input type="hidden" name="idpagamento" id="editPagamentoId">
                    <div class="col-12 col-md-3">
                      <label class="form-label">Corso iscritto</label>
                      <select class="form-select" name="idcorso" id="editPagamentoCorso" required>
                        <option value="">Seleziona</option>
                        <?php foreach ($paymentCourseOptions as $iscrizione): ?>
                          <option value="<?= (int) ($iscrizione['course_id'] ?? $iscrizione['id'] ?? 0) ?>">
                            #<?= (int) ($iscrizione['course_id'] ?? $iscrizione['id'] ?? 0) ?> - <?= htmlspecialchars((string) ($iscrizione['courses'] ?? '')) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-12 col-md-3">
                      <label class="form-label">Importo</label>
                      <input type="number" step="0.01" min="0" class="form-control" name="quota_pagamento" id="editPagamentoImporto" required>
                    </div>
                    <div class="col-12 col-md-3">
                      <label class="form-label">Data pagamento</label>
                      <input type="date" class="form-control" name="data_pagamento" id="editPagamentoData" required>
                    </div>
                    <div class="col-12 col-md-3">
                      <label class="form-label">Data scadenza</label>
                      <input type="date" class="form-control" name="data_scadenza" id="editPagamentoScadenza">
                    </div>
                    <div class="col-6">
                      <label class="form-label">Note pagamento</label>
                      <textarea class="form-control" rows="3" name="note_pagamento" id="editPagamentoNote"></textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                      <button class="btn btn-outline-primary" type="submit">Salva modifiche</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php elseif (((string) ($_GET['open_edit'] ?? '0')) === '1'): ?>
      <div class="alert alert-warning mt-4 mb-0" role="alert">
        Scheda atleta non trovata. Seleziona un atleta dalla tabella e clicca su "Scheda" per aprire tutti i tab (Documenti/Certificati, Iscrizioni, Pagamenti).
      </div>
    <?php endif; ?>
  </div>
</div>

  <link rel="stylesheet" href="<?= htmlspecialchars((string) ($frontendAssets['cropper_css'] ?? 'https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css')) ?>">
  <script src="<?= htmlspecialchars((string) ($frontendAssets['cropper_js'] ?? 'https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.js')) ?>"></script>

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

    const getInitialsLabel = (athlete) => {
      const raw = (athlete && athlete.name) ? String(athlete.name).trim() : '';
      if (raw === '') {
        return 'A';
      }

      const parts = raw.split(/\s+/).filter(Boolean);
      if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase();
      }

      return (parts[0][0] + parts[1][0]).toUpperCase();
    };

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
        {
          data: 'image_url',
          orderable: false,
          searchable: false,
          render: function (data, type, row) {
            const imgUrl = String(data || '').trim();
            const label = escapeHtml(row.name || 'Atleta');
            const initials = escapeHtml(getInitialsLabel(row));
            if (imgUrl !== '') {
              return '<img src="' + escapeHtml(imgUrl) + '" alt="' + label + '" style="width: 36px; height: 36px; object-fit: cover; border-radius: 50%;" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'inline-flex\';">'
                + '<span class="badge text-bg-light border" style="display:none;width: 36px; height: 36px; line-height: 26px;">' + initials + '</span>';
            }
            return '<span class="badge text-bg-light border" style="width: 36px; height: 36px; line-height: 26px;">' + initials + '</span>';
          },
        },
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

    // Inizializzazione DataTable per documenti-table gestita da app.js

    const iscrizioniTable = document.getElementById('iscrizioni-table');
    if (iscrizioniTable) {
      new DataTable('#iscrizioni-table', {
        pageLength: 10,
        order: [[0, 'desc']],
        responsive: true,
        language: {
          url: dataTableLangUrl,
          emptyTable: 'Nessuna iscrizione registrata.',
        },
      });
    }

    const pagamentiTable = document.getElementById('pagamenti-table');
    if (pagamentiTable) {
      new DataTable('#pagamenti-table', {
        pageLength: 10,
        order: [[0, 'desc']],
        responsive: true,
        language: {
          url: dataTableLangUrl,
          emptyTable: 'Nessun pagamento registrato.',
        },
      });
    }
  }

  const addPanel = document.getElementById('addAtletaPanel');
  const addAthleteForm = document.getElementById('addAthleteForm');
  const editAthleteForm = document.getElementById('editAthleteProfileForm');
  const openAddBtn = document.getElementById('openAddAtletaPanelBtn');
  const closeAddBtn = document.getElementById('closeAddAtletaPanelBtn');
  const cancelAddBtn = document.getElementById('cancelAddAtletaBtn');
  const addAthleteImageInput = document.getElementById('addAthleteImageInput');
  const addAthleteImagePreview = document.getElementById('addAthleteImagePreview');
  const addAthleteImagePlaceholder = document.getElementById('addAthleteImagePlaceholder');
  const addAthleteCropContainer = document.getElementById('addAthleteCropContainer');
  const addAthleteCropSource = document.getElementById('addAthleteCropSource');
  const addAthleteCropDataInput = document.getElementById('addAthleteCropImageData');
  const applyAddAthleteImageCropBtn = document.getElementById('applyAddAthleteImageCropBtn');
  const cancelAddAthleteImageCropBtn = document.getElementById('cancelAddAthleteImageCropBtn');
  const addTabInput = document.getElementById('addAthleteTabInput');
  const editTabInput = document.getElementById('editAthleteTabInput');
  const editAthleteImageInput = document.getElementById('editAthleteImageInput');
  const editAthleteImagePreview = document.getElementById('editAthleteImagePreview');
  const editAthleteImagePlaceholder = document.getElementById('editAthleteImagePlaceholder');
  const editAthleteCropContainer = document.getElementById('editAthleteCropContainer');
  const editAthleteCropSource = document.getElementById('editAthleteCropSource');
  const editAthleteCropDataInput = document.getElementById('editAthleteCropImageData');
  const applyEditAthleteImageCropBtn = document.getElementById('applyEditAthleteImageCropBtn');
  const cancelEditAthleteImageCropBtn = document.getElementById('cancelEditAthleteImageCropBtn');
  const editAthleteRemoveImageCheckbox = document.getElementById('editAthleteRemoveImageCheckbox');
  const editAthleteRemoveImageInput = document.getElementById('editAthleteRemoveImageInput');

  const ALLOWED_IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
  const MAX_IMAGE_SIZE = 5 * 1024 * 1024;

  let addAthleteImageCropper = null;
  let editAthleteImageCropper = null;

  const getAthleteInitials = function (isEdit) {
    const prefix = isEdit ? 'edit' : 'add';
    const nameInput = document.getElementById(prefix + 'Nome');
    const surnameInput = document.getElementById(prefix + 'Cognome');
    const fullName = ((nameInput ? nameInput.value : '') + ' ' + (surnameInput ? surnameInput.value : '')).trim();
    if (fullName === '') {
      return 'A';
    }

    const parts = fullName.split(/\s+/).filter(Boolean);
    if (parts.length === 1) {
      return parts[0].slice(0, 2).toUpperCase();
    }

    return (parts[0][0] + parts[1][0]).toUpperCase();
  };

  const renderAthletePreview = function (isEdit, imageUrl, initials) {
    const preview = isEdit ? editAthleteImagePreview : addAthleteImagePreview;
    const placeholder = isEdit ? editAthleteImagePlaceholder : addAthleteImagePlaceholder;

    if (!preview || !placeholder) {
      return;
    }

    placeholder.textContent = String(initials || 'A').toUpperCase();

    if (imageUrl && String(imageUrl).trim() !== '') {
      preview.src = String(imageUrl);
      preview.classList.remove('d-none');
      placeholder.classList.add('d-none');
      return;
    }

    preview.src = '';
    preview.classList.add('d-none');
    placeholder.classList.remove('d-none');
  };

  const destroyAthleteCropper = function (isEdit) {
    const cropper = isEdit ? editAthleteImageCropper : addAthleteImageCropper;
    const cropContainer = isEdit ? editAthleteCropContainer : addAthleteCropContainer;
    const cropSource = isEdit ? editAthleteCropSource : addAthleteCropSource;

    if (cropper && typeof cropper.destroy === 'function') {
      cropper.destroy();
    }

    if (isEdit) {
      editAthleteImageCropper = null;
    } else {
      addAthleteImageCropper = null;
    }

    if (cropContainer) {
      cropContainer.classList.add('d-none');
    }
    if (cropSource) {
      cropSource.src = '';
    }
  };

  const showAthleteCropper = function (isEdit, dataUrl) {
    const cropContainer = isEdit ? editAthleteCropContainer : addAthleteCropContainer;
    const cropSource = isEdit ? editAthleteCropSource : addAthleteCropSource;

    if (!cropContainer || !cropSource || typeof Cropper === 'undefined') {
      return false;
    }

    destroyAthleteCropper(isEdit);
    cropSource.src = dataUrl;
    cropContainer.classList.remove('d-none');

    const cropper = new Cropper(cropSource, {
      aspectRatio: 1,
      viewMode: 1,
      dragMode: 'move',
      autoCropArea: 1,
      responsive: true,
      background: false,
      guides: true,
    });

    if (isEdit) {
      editAthleteImageCropper = cropper;
    } else {
      addAthleteImageCropper = cropper;
    }

    return true;
  };

  const applyAthleteCrop = function (isEdit) {
    const cropper = isEdit ? editAthleteImageCropper : addAthleteImageCropper;
    const cropDataInput = isEdit ? editAthleteCropDataInput : addAthleteCropDataInput;
    const imageInput = isEdit ? editAthleteImageInput : addAthleteImageInput;

    if (!cropper) {
      return false;
    }

    const canvas = cropper.getCroppedCanvas({
      width: 320,
      height: 320,
      imageSmoothingEnabled: true,
      imageSmoothingQuality: 'high',
    });

    if (!canvas) {
      return false;
    }

    const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
    if (cropDataInput) {
      cropDataInput.value = dataUrl;
    }
    if (imageInput) {
      imageInput.value = '';
    }

    if (isEdit && editAthleteRemoveImageCheckbox) {
      editAthleteRemoveImageCheckbox.checked = false;
    }
    if (isEdit && editAthleteRemoveImageInput) {
      editAthleteRemoveImageInput.value = '0';
    }

    renderAthletePreview(isEdit, dataUrl, getAthleteInitials(isEdit));
    destroyAthleteCropper(isEdit);

    return true;
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
        return;
      }

      const hasCropData = addAthleteCropDataInput && String(addAthleteCropDataInput.value || '').trim() !== '';
      if (!hasCropData && addAthleteImageCropper) {
        applyAthleteCrop(false);
      }
    });
  }

  if (addAthleteImageInput) {
    addAthleteImageInput.addEventListener('change', function () {
      const file = addAthleteImageInput.files && addAthleteImageInput.files[0] ? addAthleteImageInput.files[0] : null;
      const cropDataInput = addAthleteCropDataInput;

      if (cropDataInput) {
        cropDataInput.value = '';
      }

      if (!file) {
        destroyAthleteCropper(false);
        renderAthletePreview(false, '', getAthleteInitials(false));
        return;
      }

      if (!ALLOWED_IMAGE_MIMES.includes(file.type)) {
        window.alert('Formato immagine non supportato. Usa JPG, PNG, WEBP o GIF.');
        addAthleteImageInput.value = '';
        renderAthletePreview(false, '', getAthleteInitials(false));
        return;
      }

      if (file.size > MAX_IMAGE_SIZE) {
        window.alert('Immagine troppo grande. Dimensione massima 5MB.');
        addAthleteImageInput.value = '';
        renderAthletePreview(false, '', getAthleteInitials(false));
        return;
      }

      const reader = new FileReader();
      reader.onload = function (event) {
        const dataUrl = event.target && event.target.result ? String(event.target.result) : '';
        if (dataUrl === '') {
          return;
        }

        const cropShown = showAthleteCropper(false, dataUrl);
        if (!cropShown) {
          renderAthletePreview(false, dataUrl, getAthleteInitials(false));
        }
      };
      reader.onerror = function () {
        addAthleteImageInput.value = '';
        renderAthletePreview(false, '', getAthleteInitials(false));
      };
      reader.readAsDataURL(file);
    });
  }

  if (applyAddAthleteImageCropBtn) {
    applyAddAthleteImageCropBtn.addEventListener('click', function () {
      if (!applyAthleteCrop(false)) {
        window.alert('Impossibile applicare il ritaglio immagine.');
      }
    });
  }

  if (cancelAddAthleteImageCropBtn) {
    cancelAddAthleteImageCropBtn.addEventListener('click', function () {
      if (addAthleteImageInput) {
        addAthleteImageInput.value = '';
      }
      if (addAthleteCropDataInput) {
        addAthleteCropDataInput.value = '';
      }
      destroyAthleteCropper(false);
      renderAthletePreview(false, '', getAthleteInitials(false));
    });
  }

  const resetEditImagePreview = function () {
    if (!editAthleteImagePreview) {
      return;
    }

    const initialSrc = String(editAthleteImagePreview.dataset.initialSrc || '');
    renderAthletePreview(true, initialSrc, getAthleteInitials(true));
  };

  const applyEditImageRemovalState = function () {
    if (!editAthleteRemoveImageInput || !editAthleteRemoveImageCheckbox) {
      return;
    }

    const isRemoving = editAthleteRemoveImageCheckbox.checked;
    editAthleteRemoveImageInput.value = isRemoving ? '1' : '0';
    if (editAthleteCropDataInput) {
      editAthleteCropDataInput.value = '';
    }

    if (isRemoving) {
      destroyAthleteCropper(true);
      renderAthletePreview(true, '', getAthleteInitials(true));
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
        if (editAthleteCropDataInput) {
          editAthleteCropDataInput.value = '';
        }
        destroyAthleteCropper(true);
        resetEditImagePreview();
        return;
      }

      if (!ALLOWED_IMAGE_MIMES.includes(file.type)) {
        window.alert('Formato immagine non supportato. Usa JPG, PNG, WEBP o GIF.');
        editAthleteImageInput.value = '';
        resetEditImagePreview();
        return;
      }

      if (file.size > MAX_IMAGE_SIZE) {
        window.alert('Immagine troppo grande. Dimensione massima 5MB.');
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
      if (editAthleteCropDataInput) {
        editAthleteCropDataInput.value = '';
      }

      const reader = new FileReader();
      reader.onload = function (event) {
        const dataUrl = event.target && event.target.result ? String(event.target.result) : '';
        if (dataUrl === '') {
          return;
        }

        const cropShown = showAthleteCropper(true, dataUrl);
        if (!cropShown) {
          renderAthletePreview(true, dataUrl, getAthleteInitials(true));
        }
      };
      reader.onerror = function () {
        editAthleteImageInput.value = '';
        resetEditImagePreview();
      };
      reader.readAsDataURL(file);
    });
  }

  if (applyEditAthleteImageCropBtn) {
    applyEditAthleteImageCropBtn.addEventListener('click', function () {
      if (!applyAthleteCrop(true)) {
        window.alert('Impossibile applicare il ritaglio immagine.');
      }
    });
  }

  if (cancelEditAthleteImageCropBtn) {
    cancelEditAthleteImageCropBtn.addEventListener('click', function () {
      if (editAthleteImageInput) {
        editAthleteImageInput.value = '';
      }
      if (editAthleteCropDataInput) {
        editAthleteCropDataInput.value = '';
      }
      destroyAthleteCropper(true);
      if (editAthleteRemoveImageCheckbox && editAthleteRemoveImageCheckbox.checked) {
        renderAthletePreview(true, '', getAthleteInitials(true));
        return;
      }

      const initialSrc = String((editAthleteImagePreview && editAthleteImagePreview.dataset.initialSrc) || '');
      renderAthletePreview(true, initialSrc, getAthleteInitials(true));
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
    if (addAthleteCropDataInput) {
      addAthleteCropDataInput.value = '';
    }
    destroyAthleteCropper(false);
    renderAthletePreview(false, '', getAthleteInitials(false));
  };

  if (editAthleteForm) {
    editAthleteForm.addEventListener('submit', function () {
      const hasCropData = editAthleteCropDataInput && String(editAthleteCropDataInput.value || '').trim() !== '';
      if (!hasCropData && editAthleteImageCropper) {
        applyAthleteCrop(true);
      }
    });
  }

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
      if (target.includes('contatti')) {
        addTabInput.value = 'contatti';
      } else if (target.includes('misure')) {
        addTabInput.value = 'misure';
      } else {
        addTabInput.value = 'anagrafica';
      }
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
      } else if (target.includes('misure')) {
        editTabInput.value = 'misure';
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

  const addDocumentoPanel = document.getElementById('addDocumentoPanel');
  const openAddDocumentoPanelBtn = document.getElementById('openAddDocumentoPanelBtn');
  const closeAddDocumentoPanelBtn = document.getElementById('closeAddDocumentoPanelBtn');
  const addIscrizionePanel = document.getElementById('addIscrizionePanel');
  const openAddIscrizionePanelBtn = document.getElementById('openAddIscrizionePanelBtn');
  const closeAddIscrizionePanelBtn = document.getElementById('closeAddIscrizionePanelBtn');
  const editIscrizionePanel = document.getElementById('editIscrizionePanel');
  const closeEditIscrizionePanelBtn = document.getElementById('closeEditIscrizionePanelBtn');
  const closeEditIscrizionePanelBtnFooter = document.getElementById('closeEditIscrizionePanelBtnFooter');
  const addPagamentoPanel = document.getElementById('addPagamentoPanel');
  const openAddPagamentoPanelBtn = document.getElementById('openAddPagamentoPanelBtn');
  const closeAddPagamentoPanelBtn = document.getElementById('closeAddPagamentoPanelBtn');
  const editPagamentoPanel = document.getElementById('editPagamentoPanel');
  const closeEditPagamentoPanelBtn = document.getElementById('closeEditPagamentoPanelBtn');
  const editDocumentoPanel = document.getElementById('editDocumentoPanel');
  const closeEditDocumentoPanelBtn = document.getElementById('closeEditDocumentoPanelBtn');
  const editDocumentoId = document.getElementById('editDocumentoId');
  const editDocumentoType = document.getElementById('editDocumentoType');
  const editDocumentoDescription = document.getElementById('editDocumentoDescription');
  const editDocumentoDate = document.getElementById('editDocumentoDate');
  const editDocumentoExpiryDate = document.getElementById('editDocumentoExpiryDate');

  const hideDocumentoPanels = function () {
    if (addDocumentoPanel) {
      addDocumentoPanel.classList.add('d-none');
    }
    if (editDocumentoPanel) {
      editDocumentoPanel.classList.add('d-none');
    }
  };

  const hideIscrizionePanels = function () {
    if (addIscrizionePanel) {
      addIscrizionePanel.classList.add('d-none');
    }
    if (editIscrizionePanel) {
      editIscrizionePanel.classList.add('d-none');
    }
  };

  const hidePagamentoPanels = function () {
    if (addPagamentoPanel) {
      addPagamentoPanel.classList.add('d-none');
    }
    if (editPagamentoPanel) {
      editPagamentoPanel.classList.add('d-none');
    }
  };

  hideDocumentoPanels();
  hideIscrizionePanels();
  hidePagamentoPanels();

  document.querySelectorAll('.js-athlete-edit-tab-trigger').forEach(function (tabButton) {
    tabButton.addEventListener('shown.bs.tab', function (event) {
      const target = event.target.getAttribute('data-bs-target') || '';
      if (!target.includes('documenti')) {
        hideDocumentoPanels();
      }
      if (!target.includes('iscrizioni')) {
        hideIscrizionePanels();
      }
      if (!target.includes('pagamenti')) {
        hidePagamentoPanels();
      }
    });
  });

  if (openAddDocumentoPanelBtn && addDocumentoPanel) {
    openAddDocumentoPanelBtn.addEventListener('click', function () {
      hideDocumentoPanels();
      addDocumentoPanel.classList.remove('d-none');
      addDocumentoPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
  }

  if (closeAddDocumentoPanelBtn && addDocumentoPanel) {
    closeAddDocumentoPanelBtn.addEventListener('click', function () {
      hideDocumentoPanels();
    });
  }

  if (openAddIscrizionePanelBtn && addIscrizionePanel) {
    openAddIscrizionePanelBtn.addEventListener('click', function () {
      hideIscrizionePanels();
      addIscrizionePanel.classList.remove('d-none');
      addIscrizionePanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
  }

  if (closeAddIscrizionePanelBtn && addIscrizionePanel) {
    closeAddIscrizionePanelBtn.addEventListener('click', function () {
      hideIscrizionePanels();
    });
  }

  if (openAddPagamentoPanelBtn && addPagamentoPanel) {
    openAddPagamentoPanelBtn.addEventListener('click', function () {
      hidePagamentoPanels();
      addPagamentoPanel.classList.remove('d-none');
      addPagamentoPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
  }

  if (closeAddPagamentoPanelBtn && addPagamentoPanel) {
    closeAddPagamentoPanelBtn.addEventListener('click', function () {
      hidePagamentoPanels();
    });
  }

  document.querySelectorAll('.js-edit-documento-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!editDocumentoPanel) {
        return;
      }

      hideDocumentoPanels();

      // Popola tutti i campi del form di modifica documento in modo generico
      const mapping = {
        'editDocumentoId': 'data-document-id',
        'editDocumentoType': 'data-type-id',
        'editDocumentoDescription': 'data-description',
        'editDocumentoDate': 'data-document-date',
        'editDocumentoExpiryDate': 'data-expiry-date',
      };
      Object.entries(mapping).forEach(function ([fieldId, dataAttr]) {
        const el = document.getElementById(fieldId);
        if (el) {
          el.value = btn.getAttribute(dataAttr) || '';
        }
      });

      editDocumentoPanel.classList.remove('d-none');
      editDocumentoPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
  });

  if (closeEditDocumentoPanelBtn && editDocumentoPanel) {
    closeEditDocumentoPanelBtn.addEventListener('click', function () {
      hideDocumentoPanels();
    });
  }

  document.querySelectorAll('.js-edit-iscrizione-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!editIscrizionePanel) {
        return;
      }

      hideIscrizionePanels();

      const idCorsoAttualeEl = document.getElementById('editIscrizioneIdCorsoAttuale');
      const courseIdsEl = document.getElementById('editIscrizioneCourseIds');
      const dataInizioEl = document.getElementById('editIscrizioneDataInizio');
      const dataFineEl = document.getElementById('editIscrizioneDataFine');
      const totaleEl = document.getElementById('editIscrizioneTotale');
      const statoEl = document.getElementById('editIscrizioneStato');
      const noteEl = document.getElementById('editIscrizioneNote');

      if (idCorsoAttualeEl) idCorsoAttualeEl.value = btn.getAttribute('data-idcorso') || '';
      if (courseIdsEl) {
        const selectedCourseId = btn.getAttribute('data-idcorso') || '';
        Array.from(courseIdsEl.options).forEach(function (opt) {
          opt.selected = opt.value === selectedCourseId;
        });
      }
      if (dataInizioEl) dataInizioEl.value = btn.getAttribute('data-start-date') || '';
      if (dataFineEl) dataFineEl.value = btn.getAttribute('data-end-date') || '';
      if (totaleEl) totaleEl.value = btn.getAttribute('data-total') || '';
      if (statoEl) statoEl.value = btn.getAttribute('data-status') || 'A';
      if (noteEl) noteEl.value = btn.getAttribute('data-notes') || '';

      editIscrizionePanel.classList.remove('d-none');
      editIscrizionePanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
  });

  if (closeEditIscrizionePanelBtn && editIscrizionePanel) {
    closeEditIscrizionePanelBtn.addEventListener('click', function () {
      hideIscrizionePanels();
    });
  }

  if (closeEditIscrizionePanelBtnFooter && editIscrizionePanel) {
    closeEditIscrizionePanelBtnFooter.addEventListener('click', function () {
      hideIscrizionePanels();
    });
  }

  document.querySelectorAll('.js-edit-pagamento-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!editPagamentoPanel) {
        return;
      }

      hidePagamentoPanels();

      const idEl = document.getElementById('editPagamentoId');
      const corsoEl = document.getElementById('editPagamentoCorso');
      const importoEl = document.getElementById('editPagamentoImporto');
      const dataEl = document.getElementById('editPagamentoData');
      const scadenzaEl = document.getElementById('editPagamentoScadenza');
      const noteEl = document.getElementById('editPagamentoNote');

      if (idEl) idEl.value = btn.getAttribute('data-pagamento-id') || '';
      if (corsoEl) corsoEl.value = btn.getAttribute('data-course-id') || '';
      if (importoEl) importoEl.value = btn.getAttribute('data-amount') || '';
      if (dataEl) dataEl.value = btn.getAttribute('data-payment-date') || '';
      if (scadenzaEl) scadenzaEl.value = btn.getAttribute('data-expiry-date') || '';
      if (noteEl) noteEl.value = btn.getAttribute('data-notes') || '';

      editPagamentoPanel.classList.remove('d-none');
      editPagamentoPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
  });

  const syncDateMin = function (startEl, endEl) {
    if (!startEl || !endEl) {
      return;
    }

    const applyMin = function () {
      const startValue = (startEl.value || '').trim();
      if (startValue !== '') {
        endEl.min = startValue;
      } else {
        endEl.removeAttribute('min');
      }
    };

    startEl.addEventListener('change', applyMin);
    applyMin();
  };

  const bindDateRangeValidation = function (formId, startName, endName, errorMessage) {
    const form = document.getElementById(formId);
    if (!form) {
      return;
    }

    const startEl = form.querySelector('[name="' + startName + '"]');
    const endEl = form.querySelector('[name="' + endName + '"]');
    if (!startEl || !endEl) {
      return;
    }

    syncDateMin(startEl, endEl);

    form.addEventListener('submit', function (event) {
      const start = (startEl.value || '').trim();
      const end = (endEl.value || '').trim();
      if (start !== '' && end !== '' && end < start) {
        event.preventDefault();
        alert(errorMessage);
        endEl.focus();
      }
    });
  };

  bindDateRangeValidation('addIscrizioneForm', 'data_inizio_iscrizione', 'data_fine_iscrizione', 'La data fine iscrizione non puo essere precedente alla data inizio.');
  bindDateRangeValidation('editIscrizioneForm', 'data_inizio_iscrizione', 'data_fine_iscrizione', 'La data fine iscrizione non puo essere precedente alla data inizio.');
  bindDateRangeValidation('addPagamentoForm', 'data_pagamento', 'data_scadenza', 'La data scadenza non puo essere precedente alla data pagamento.');
  bindDateRangeValidation('editPagamentoForm', 'data_pagamento', 'data_scadenza', 'La data scadenza non puo essere precedente alla data pagamento.');

  if (closeEditPagamentoPanelBtn && editPagamentoPanel) {
    closeEditPagamentoPanelBtn.addEventListener('click', function () {
      hidePagamentoPanels();
    });
  }

  setupCodiceFiscaleAutocalcolo('addAthleteForm');
  setupCodiceFiscaleAutocalcolo('editAthleteProfileForm');
  setupComuniSelect2();
});
</script>
