<?php

declare(strict_types=1);

/** @var array $users */
/** @var array $profiles */
/** @var array $applicationsCatalog */
/** @var int $currentUserId */

$frontendApi = frontend_api_urls();
$utentiApiUrl = (string) ($frontendApi['utenti'] ?? '');
$frontendAssets = frontend_asset_urls();
$appPaths = app_paths();
$rootPath = (string) $appPaths['root'];

$okMessage = trim((string) ($_GET['ok'] ?? ''));
$errMessage = trim((string) ($_GET['err'] ?? ''));
$addPrefill = [
  'nome' => trim((string) ($_GET['add_nome'] ?? '')),
  'cognome' => trim((string) ($_GET['add_cognome'] ?? '')),
  'username' => trim((string) ($_GET['add_username'] ?? '')),
  'email' => trim((string) ($_GET['add_email'] ?? '')),
  'telefono1' => trim((string) ($_GET['add_telefono1'] ?? '')),
  'telefono2' => trim((string) ($_GET['add_telefono2'] ?? '')),
  'email2' => trim((string) ($_GET['add_email2'] ?? '')),
  'data_scadenza_account' => trim((string) ($_GET['add_data_scadenza_account'] ?? '')),
  'application_ids' => array_values(array_filter(array_map('intval', explode(',', trim((string) ($_GET['add_application_ids'] ?? '')))), static fn (int $id): bool => $id > 0)),
  'profile_ids' => array_values(array_filter(array_map('intval', explode(',', trim((string) ($_GET['add_profile_ids'] ?? ($_GET['add_profile_id'] ?? ''))))), static fn (int $id): bool => $id > 0)),
  'status' => trim((string) ($_GET['add_status'] ?? 'Attivo')),
];
$addPrefill['data_scadenza_account'] = $addPrefill['data_scadenza_account'] !== ''
  ? substr($addPrefill['data_scadenza_account'], 0, 10)
  : date('Y-m-d', strtotime('+1 year'));
$openAddPanel =
  $addPrefill['nome'] !== ''
  || $addPrefill['cognome'] !== ''
  || $addPrefill['username'] !== ''
  || $addPrefill['email'] !== ''
  || $addPrefill['telefono1'] !== ''
  || $addPrefill['telefono2'] !== ''
  || $addPrefill['email2'] !== ''
  || count($addPrefill['application_ids']) > 0
  || count($addPrefill['profile_ids']) > 0;
$openEdit = ((string) ($_POST['open_edit'] ?? $_GET['open_edit'] ?? '0')) === '1';
$editPrefill = [
  'id' => (int) ($_POST['edit_id'] ?? $_GET['edit_id'] ?? 0),
  'first_name' => trim((string) ($_POST['edit_nome'] ?? $_GET['edit_nome'] ?? '')),
  'last_name' => trim((string) ($_POST['edit_cognome'] ?? $_GET['edit_cognome'] ?? '')),
  'username' => trim((string) ($_POST['edit_username'] ?? $_GET['edit_username'] ?? '')),
  'email' => trim((string) ($_POST['edit_email'] ?? $_GET['edit_email'] ?? '')),
  'telefono1' => trim((string) ($_POST['edit_telefono1'] ?? $_GET['edit_telefono1'] ?? '')),
  'telefono2' => trim((string) ($_POST['edit_telefono2'] ?? $_GET['edit_telefono2'] ?? '')),
  'email2' => trim((string) ($_POST['edit_email2'] ?? $_GET['edit_email2'] ?? '')),
  'profile_ids' => array_values(array_filter(array_map('intval', explode(',', trim((string) ($_POST['edit_profile_ids'] ?? $_GET['edit_profile_ids'] ?? ($_POST['edit_profile_id'] ?? $_GET['edit_profile_id'] ?? ''))))), static fn (int $id): bool => $id > 0)),
  'status' => trim((string) ($_POST['edit_status'] ?? $_GET['edit_status'] ?? 'Attivo')),
  'image_path' => trim((string) ($_POST['edit_image'] ?? $_GET['edit_image'] ?? '')),
  'application_ids' => array_values(array_filter(array_map('intval', explode(',', trim((string) ($_POST['edit_application_ids'] ?? $_GET['edit_application_ids'] ?? '')))), static fn (int $id): bool => $id > 0)),
  'data_scadenza_account' => trim((string) ($_POST['edit_data_scadenza_account'] ?? $_GET['edit_data_scadenza_account'] ?? '')),
];

$editPrefill['image_url'] = $editPrefill['image_path'] !== ''
  ? $rootPath . '/' . ltrim($editPrefill['image_path'], '/')
  : '';

$groupedApplications = [];
foreach ($applicationsCatalog as $app) {
  $groupName = trim((string) ($app['group_name'] ?? 'Applicazioni'));
  if (!isset($groupedApplications[$groupName])) {
    $groupedApplications[$groupName] = [];
  }
  $groupedApplications[$groupName][] = $app;
}
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
      <h5 class="m-0">Gestione Utenti</h5>
        <button class="btn btn-success" type="button" id="openAddUserPanelBtn">+ Aggiungi Utente</button>
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
      <table id="utenti-table" class="table align-middle js-datatable table-hover" data-server-side="1">
        <thead>
          <tr>
            <th>Foto</th>
            <th>Nome</th>
            <th>Username</th>
            <th>Email</th>
            <th>Profili</th>
            <th>Stato</th>
            <th>Data Scadenza Account</th>
            <th class="text-end">Azioni</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

      <div id="addUserPanel" class="card border mt-4 <?= $openAddPanel ? '' : 'd-none' ?>">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h6 class="m-0">Scheda Nuovo Utente</h6>
          <button class="btn btn-sm btn-outline-secondary" type="button" id="closeAddUserPanelBtn">Chiudi</button>
        </div>
        <div class="card-body">
          <form method="post" action="<?= htmlspecialchars($utentiApiUrl) ?>" class="row g-3" enctype="multipart/form-data" id="addUserForm">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="crop_image_base64_add" id="addUserCropImageData">
            <ul class="nav nav-tabs customtab col-12" id="addUserTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="add-tab-anagrafica-tab" data-bs-toggle="tab" data-bs-target="#add-tab-anagrafica" type="button" role="tab" aria-controls="add-tab-anagrafica" aria-selected="true">Anagrafica</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="add-tab-diritti-tab" data-bs-toggle="tab" data-bs-target="#add-tab-diritti" type="button" role="tab" aria-controls="add-tab-diritti" aria-selected="false">Diritti</button>
              </li>
            </ul>

            <div class="tab-content border border-top-0 rounded-bottom p-3 col-12">
              <div class="tab-pane fade show active" id="add-tab-anagrafica" role="tabpanel" aria-labelledby="add-tab-anagrafica-tab" tabindex="0">
                <div class="row g-3">
                  <div class="col-12 col-lg-3">
                    <div class="border rounded p-3 h-100">
                      <div class="text-center mb-2">
                        <img
                          id="addUserImagePreview"
                          src=""
                          alt="Immagine nuovo utente"
                          class="rounded-circle d-none"
                          style="width: 130px; height: 130px; object-fit: cover;"
                        >
                        <div id="addUserImagePlaceholder" class="rounded-circle border d-flex align-items-center justify-content-center mx-auto text-muted" style="width: 130px; height: 130px;">
                          U
                        </div>
                      </div>

                      <label for="addUserImage" class="form-label">Immagine utente</label>
                      <input class="form-control" type="file" name="image" id="addUserImage" accept="image/*">

                      <div id="addUserCropContainer" class="mt-3 d-none">
                        <div class="border rounded p-2 bg-light">
                          <img id="addUserCropSource" src="" alt="Ritaglio avatar" style="max-width: 100%; display: block;">
                        </div>
                        <div class="d-flex gap-2 mt-2">
                          <button type="button" class="btn btn-sm btn-primary" id="applyAddImageCropBtn">Usa ritaglio</button>
                          <button type="button" class="btn btn-sm btn-outline-secondary" id="cancelAddImageCropBtn">Annulla ritaglio</button>
                        </div>
                        <small class="text-muted">Trascina e zooma l'immagine, poi premi "Usa ritaglio".</small>
                      </div>
                      <small class="text-muted">Formati supportati: JPG, PNG, WEBP, GIF (max 5MB)</small>
                    </div>
                  </div>

                  <div class="col-12 col-lg-9">
                    <?php
                    $utenteFormValues = [
                        'status' => (string) ($addPrefill['status'] ?? 'Attivo'),
                        'data_scadenza_account' => (string) ($addPrefill['data_scadenza_account'] ?? ''),
                        'nome' => (string) ($addPrefill['nome'] ?? ''),
                        'cognome' => (string) ($addPrefill['cognome'] ?? ''),
                        'username' => (string) ($addPrefill['username'] ?? ''),
                        'email' => (string) ($addPrefill['email'] ?? ''),
                        'email2' => (string) ($addPrefill['email2'] ?? ''),
                        'telefono1' => (string) ($addPrefill['telefono1'] ?? ''),
                        'telefono2' => (string) ($addPrefill['telefono2'] ?? ''),
                    ];
                    $utenteAnagraficaFieldIds = [
                        'status' => '',
                        'data_scadenza_account' => 'add_data_scadenza_account',
                        'nome' => 'addUserNome',
                        'cognome' => 'addUserCognome',
                        'username' => 'addUserUsername',
                        'password' => 'addUserPassword',
                        'email' => 'addUserEmail',
                        'email2' => 'addUserEmail2',
                        'telefono1' => 'addUserTelefono1',
                        'telefono2' => 'addUserTelefono2',
                    ];
                    $utenteAnagraficaIsEdit = false;
                    require __DIR__ . '/partials/utente_form_anagrafica_fields.php';
                    ?>
                  </div>
                </div>
              </div>

              <div class="tab-pane fade" id="add-tab-diritti" role="tabpanel" aria-labelledby="add-tab-diritti-tab" tabindex="0">
                <?php
                $utenteDirittiProfileIds = (array) ($addPrefill['profile_ids'] ?? []);
                $utenteDirittiApplicationIds = (array) ($addPrefill['application_ids'] ?? []);
                $utenteDirittiProfileSelectId = 'addUserProfiles';
                $utenteDirittiApplicationClass = 'add-user-application';
                $utenteDirittiApplicationIdPrefix = 'addUserApplication';
                $utenteDirittiShowSelfEditNotice = false;
                require __DIR__ . '/partials/utente_form_diritti_fields.php';
                ?>
              </div>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2">
              <button class="btn btn-secondary" type="button" id="cancelAddUserBtn">Annulla</button>
              <button class="btn btn-success" type="submit">+ Aggiungi Utente</button>
            </div>
          </form>
        </div>
      </div>

    <div id="editUserPanel" class="card border mt-4 d-none">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Scheda Utente</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeEditPanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($utentiApiUrl) ?>" enctype="multipart/form-data" id="editUserForm">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" id="editUserId">
          <input type="hidden" name="current_image_path" id="editUserCurrentImagePath">
          <input type="hidden" name="crop_image_base64" id="editUserCropImageData">

            <ul class="nav nav-tabs customtab" id="editUserTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-anagrafica-tab" data-bs-toggle="tab" data-bs-target="#tab-anagrafica" type="button" role="tab" aria-controls="tab-anagrafica" aria-selected="true">Anagrafica</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-diritti-tab" data-bs-toggle="tab" data-bs-target="#tab-diritti" type="button" role="tab" aria-controls="tab-diritti" aria-selected="false">Diritti</button>
              </li>
            </ul>

            <div class="tab-content border border-top-0 rounded-bottom p-3">
              <div class="tab-pane fade show active" id="tab-anagrafica" role="tabpanel" aria-labelledby="tab-anagrafica-tab" tabindex="0">
                <div class="row g-3">
                  <div class="col-12 col-lg-3">
                    <div class="border rounded p-3 h-100">
                      <div class="text-center mb-2">
                        <img
                          id="editUserImagePreview"
                          src=""
                          alt="Immagine utente"
                          class="rounded-circle d-none"
                          style="width: 130px; height: 130px; object-fit: cover;"
                        >
                        <div id="editUserImagePlaceholder" class="rounded-circle border d-flex align-items-center justify-content-center mx-auto text-muted" style="width: 130px; height: 130px;">
                          U
                        </div>
                      </div>

                      <label for="editUserImage" class="form-label">Immagine utente</label>
                      <input class="form-control" type="file" name="image" id="editUserImage" accept="image/*">

                      <div id="userCropContainer" class="mt-3 d-none">
                        <div class="border rounded p-2 bg-light">
                          <img id="editUserCropSource" src="" alt="Ritaglio avatar" style="max-width: 100%; display: block;">
                        </div>
                        <div class="d-flex gap-2 mt-2">
                          <button type="button" class="btn btn-sm btn-primary" id="applyImageCropBtn">Usa ritaglio</button>
                          <button type="button" class="btn btn-sm btn-outline-secondary" id="cancelImageCropBtn">Annulla ritaglio</button>
                        </div>
                        <small class="text-muted">Trascina e zooma l'immagine, poi premi "Usa ritaglio".</small>
                      </div>

                      <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="editUserRemoveImage" name="remove_image" value="1">
                        <label class="form-check-label" for="editUserRemoveImage">Rimuovi immagine attuale</label>
                      </div>
                      <small class="text-muted">Formati supportati: JPG, PNG, WEBP, GIF (max 5MB)</small>
                    </div>
                  </div>

                  <div class="col-12 col-lg-9">
                    <?php
                    $utenteFormValues = [
                        'status' => (string) ($editPrefill['status'] ?? 'Attivo'),
                        'data_scadenza_account' => (string) ($editPrefill['data_scadenza_account'] ?? ''),
                        'nome' => (string) ($editPrefill['first_name'] ?? ''),
                        'cognome' => (string) ($editPrefill['last_name'] ?? ''),
                        'username' => (string) ($editPrefill['username'] ?? ''),
                        'email' => (string) ($editPrefill['email'] ?? ''),
                        'email2' => (string) ($editPrefill['email2'] ?? ''),
                        'telefono1' => (string) ($editPrefill['telefono1'] ?? ''),
                        'telefono2' => (string) ($editPrefill['telefono2'] ?? ''),
                    ];
                    $utenteAnagraficaFieldIds = [
                        'status' => 'editUserStatus',
                        'data_scadenza_account' => 'editUserDataScadenzaAccount',
                        'nome' => 'editUserNome',
                        'cognome' => 'editUserCognome',
                        'username' => 'editUserUsername',
                        'password' => 'editUserPassword',
                        'email' => 'editUserEmail',
                        'email2' => 'editUserEmail2',
                        'telefono1' => 'editUserTelefono1',
                        'telefono2' => 'editUserTelefono2',
                    ];
                    $utenteAnagraficaIsEdit = true;
                    require __DIR__ . '/partials/utente_form_anagrafica_fields.php';
                    ?>
                  </div>
                </div>
              </div>

              <div class="tab-pane fade" id="tab-diritti" role="tabpanel" aria-labelledby="tab-diritti-tab" tabindex="0">
                <?php
                $utenteDirittiProfileIds = (array) ($editPrefill['profile_ids'] ?? []);
                $utenteDirittiApplicationIds = (array) ($editPrefill['application_ids'] ?? []);
                $utenteDirittiProfileSelectId = 'editUserProfiles';
                $utenteDirittiApplicationClass = 'edit-user-application';
                $utenteDirittiApplicationIdPrefix = 'editUserApplication';
                $utenteDirittiShowSelfEditNotice = true;
                require __DIR__ . '/partials/utente_form_diritti_fields.php';
                ?>
              </div>
            </div>

          <div class="mt-3 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary" id="cancelEditUserBtn">Annulla</button>
            <button type="submit" class="btn btn-primary">Salva modifiche</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="<?= htmlspecialchars((string) ($frontendAssets['cropper_css'] ?? 'https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css')) ?>">
<script src="<?= htmlspecialchars((string) ($frontendAssets['cropper_js'] ?? 'https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.js')) ?>"></script>

<script>
const currentUserId = <?= (int) $currentUserId ?>;
const appRootPath = <?= json_encode($rootPath, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
let userImageCropper = null;
let addUserImageCropper = null;
let usersDataTable = null;

function getInitialsLabel(user) {
  const raw = (user && (user.name || user.username)) ? String(user.name || user.username).trim() : '';
  if (raw === '') {
    return 'U';
  }

  const parts = raw.split(/\s+/).filter(Boolean);
  if (parts.length === 1) {
    return parts[0].slice(0, 2).toUpperCase();
  }

  return (parts[0][0] + parts[1][0]).toUpperCase();
}

function showEditPanel() {
    hideAddPanel();

  const panel = document.getElementById('editUserPanel');
  if (!panel) {
    return;
  }

  panel.classList.remove('d-none');
  panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function hideEditPanel() {
  const panel = document.getElementById('editUserPanel');
  if (!panel) {
    return;
  }

  destroyUserImageCropper();
  panel.classList.add('d-none');
}

  function showAddPanel() {
    hideEditPanel();

    const panel = document.getElementById('addUserPanel');
    if (!panel) {
      return;
    }

    panel.classList.remove('d-none');
    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function hideAddPanel() {
    const panel = document.getElementById('addUserPanel');
    if (!panel) {
      return;
    }

    destroyAddUserImageCropper();
    panel.classList.add('d-none');
  }

function destroyAddUserImageCropper() {
  if (addUserImageCropper && typeof addUserImageCropper.destroy === 'function') {
    addUserImageCropper.destroy();
  }
  addUserImageCropper = null;

  const cropContainer = document.getElementById('addUserCropContainer');
  const cropSource = document.getElementById('addUserCropSource');
  if (cropContainer) {
    cropContainer.classList.add('d-none');
  }
  if (cropSource) {
    cropSource.src = '';
  }
}

function destroyUserImageCropper() {
  if (userImageCropper && typeof userImageCropper.destroy === 'function') {
    userImageCropper.destroy();
  }
  userImageCropper = null;

  const cropContainer = document.getElementById('userCropContainer');
  const cropSource = document.getElementById('editUserCropSource');
  if (cropContainer) {
    cropContainer.classList.add('d-none');
  }
  if (cropSource) {
    cropSource.src = '';
  }
}

function showUserImageCropper(dataUrl) {
  const cropContainer = document.getElementById('userCropContainer');
  const cropSource = document.getElementById('editUserCropSource');

  if (!cropContainer || !cropSource || typeof Cropper === 'undefined') {
    return false;
  }

  destroyUserImageCropper();
  cropSource.src = dataUrl;
  cropContainer.classList.remove('d-none');

  userImageCropper = new Cropper(cropSource, {
    aspectRatio: 1,
    viewMode: 1,
    dragMode: 'move',
    autoCropArea: 1,
    responsive: true,
    background: false,
    guides: true,
  });

  return true;
}

function showAddUserImageCropper(dataUrl) {
  const cropContainer = document.getElementById('addUserCropContainer');
  const cropSource = document.getElementById('addUserCropSource');

  if (!cropContainer || !cropSource || typeof Cropper === 'undefined') {
    return false;
  }

  destroyAddUserImageCropper();
  cropSource.src = dataUrl;
  cropContainer.classList.remove('d-none');

  addUserImageCropper = new Cropper(cropSource, {
    aspectRatio: 1,
    viewMode: 1,
    dragMode: 'move',
    autoCropArea: 1,
    responsive: true,
    background: false,
    guides: true,
  });

  return true;
}

function getCurrentEditInitials() {
  return getInitialsLabel({
    name: (document.getElementById('editUserNome') || {}).value + ' ' + (document.getElementById('editUserCognome') || {}).value,
    username: (document.getElementById('editUserUsername') || {}).value,
  });
}

function getCurrentAddInitials() {
  return getInitialsLabel({
    name: (document.getElementById('addUserNome') || {}).value + ' ' + (document.getElementById('addUserCognome') || {}).value,
    username: (document.getElementById('addUserUsername') || {}).value,
  });
}

function formatDateForInput(value) {
  const raw = String(value || '').trim();
  if (raw === '') {
    return '';
  }
  return raw.slice(0, 10);
}

function applyCurrentCrop() {
  if (!userImageCropper) {
    return false;
  }

  const canvas = userImageCropper.getCroppedCanvas({
    width: 320,
    height: 320,
    imageSmoothingEnabled: true,
    imageSmoothingQuality: 'high',
  });

  if (!canvas) {
    return false;
  }

  const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
  const cropDataInput = document.getElementById('editUserCropImageData');
  const removeImageInput = document.getElementById('editUserRemoveImage');
  const fileInput = document.getElementById('editUserImage');

  if (cropDataInput) {
    cropDataInput.value = dataUrl;
  }
  if (removeImageInput) {
    removeImageInput.checked = false;
  }
  if (fileInput) {
    fileInput.value = '';
  }

  renderUserImage(dataUrl, getCurrentEditInitials());
  destroyUserImageCropper();
  return true;
}

function renderUserImage(imageUrl, initials = 'U') {
  const preview = document.getElementById('editUserImagePreview');
  const placeholder = document.getElementById('editUserImagePlaceholder');

  if (!preview || !placeholder) {
    return;
  }

  placeholder.textContent = String(initials || 'U').toUpperCase();

  if (imageUrl && String(imageUrl).trim() !== '') {
    preview.src = imageUrl;
    preview.classList.remove('d-none');
    placeholder.classList.add('d-none');
    return;
  }

  preview.src = '';
  preview.classList.add('d-none');
  placeholder.classList.remove('d-none');
}

function renderAddUserImage(imageUrl, initials = 'U') {
  const preview = document.getElementById('addUserImagePreview');
  const placeholder = document.getElementById('addUserImagePlaceholder');

  if (!preview || !placeholder) {
    return;
  }

  placeholder.textContent = String(initials || 'U').toUpperCase();

  if (imageUrl && String(imageUrl).trim() !== '') {
    preview.src = imageUrl;
    preview.classList.remove('d-none');
    placeholder.classList.add('d-none');
    return;
  }

  preview.src = '';
  preview.classList.add('d-none');
  placeholder.classList.remove('d-none');
}

function applyCurrentAddCrop() {
  if (!addUserImageCropper) {
    return false;
  }

  const canvas = addUserImageCropper.getCroppedCanvas({
    width: 320,
    height: 320,
    imageSmoothingEnabled: true,
    imageSmoothingQuality: 'high',
  });

  if (!canvas) {
    return false;
  }

  const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
  const cropDataInput = document.getElementById('addUserCropImageData');
  const fileInput = document.getElementById('addUserImage');

  if (cropDataInput) {
    cropDataInput.value = dataUrl;
  }
  if (fileInput) {
    fileInput.value = '';
  }

  renderAddUserImage(dataUrl, getCurrentAddInitials());
  destroyAddUserImageCropper();
  return true;
}

function resetAddUserForm() {
  const addUserForm = document.getElementById('addUserForm');
  if (!addUserForm) {
    return;
  }

  addUserForm.reset();

  const expiryInput = document.getElementById('add_data_scadenza_account');
  if (expiryInput) {
    expiryInput.value = '<?= htmlspecialchars($addPrefill['data_scadenza_account'] ?? '') ?>';
  }

  const statusInput = addUserForm.querySelector('select[name="status"]');
  if (statusInput) {
    statusInput.value = 'Attivo';
  }

  const cropDataInput = document.getElementById('addUserCropImageData');
  if (cropDataInput) {
    cropDataInput.value = '';
  }

  document.querySelectorAll('#addUserProfiles option').forEach((option) => {
    option.selected = false;
  });
  document.querySelectorAll('.add-user-application').forEach((checkbox) => {
    checkbox.checked = false;
  });

  destroyAddUserImageCropper();
  renderAddUserImage('', 'U');
}

function loadUserData(user) {
  const selectedUserId = Number((user && user.meta && user.meta.id) || user.id || 0);
  const isCurrentUser = selectedUserId === currentUserId;
  const profileIds = Array.isArray(user.profile_ids)
    ? user.profile_ids.map((value) => Number(value)).filter((value) => Number.isFinite(value) && value > 0)
    : (Number(user.profile_id || 0) > 0 ? [Number(user.profile_id)] : []);
    const applicationIds = Array.isArray(user.application_ids)
      ? user.application_ids.map((value) => Number(value)).filter((value) => Number.isFinite(value) && value > 0)
      : [];

  document.getElementById('editUserId').value = selectedUserId > 0 ? String(selectedUserId) : '';
  document.getElementById('editUserNome').value = user.first_name || '';
  document.getElementById('editUserCognome').value = user.last_name || '';
  document.getElementById('editUserUsername').value = user.username || '';
  document.getElementById('editUserEmail').value = user.email || '';
  document.getElementById('editUserTelefono1').value = user.phone1 || '';
  document.getElementById('editUserTelefono2').value = user.phone2 || '';
  document.getElementById('editUserEmail2').value = user.email2 || '';
  document.getElementById('editUserDataScadenzaAccount').value = formatDateForInput(user.data_scadenza_account || '');
  document.getElementById('editUserPassword').value = '';
  document.querySelectorAll('#editUserProfiles option').forEach((option) => {
    const profileId = Number(option.value || 0);
    option.selected = profileIds.includes(profileId);
  });
  document.getElementById('editUserStatus').value = user.status || 'Attivo';
  document.getElementById('editUserCurrentImagePath').value = user.image_path || '';

  const usernameInput = document.getElementById('editUserUsername');
  const profileInput = document.getElementById('editUserProfiles');
  const statusInput = document.getElementById('editUserStatus');
  const selfEditNotice = document.getElementById('selfEditNotice');

  if (usernameInput) {
    usernameInput.readOnly = isCurrentUser;
    if (isCurrentUser) {
      usernameInput.setAttribute('title', 'Username non modificabile sul proprio utente da questa sezione');
    } else {
      usernameInput.removeAttribute('title');
    }
  }
  if (profileInput) {
    profileInput.disabled = isCurrentUser;
    if (isCurrentUser) {
      profileInput.setAttribute('title', 'Profili non modificabili sul proprio utente');
    } else {
      profileInput.removeAttribute('title');
    }
  }
  if (statusInput) {
    statusInput.disabled = isCurrentUser;
    if (isCurrentUser) {
      statusInput.setAttribute('title', 'Stato non modificabile sul proprio utente');
    } else {
      statusInput.removeAttribute('title');
    }
  }
  if (selfEditNotice) {
    selfEditNotice.classList.toggle('d-none', !isCurrentUser);
  }

    document.querySelectorAll('.edit-user-application').forEach((checkbox) => {
      const appId = Number(checkbox.value || 0);
      checkbox.checked = applicationIds.includes(appId);
      checkbox.disabled = isCurrentUser;
    });

  const removeImageInput = document.getElementById('editUserRemoveImage');
  const imageInput = document.getElementById('editUserImage');

  if (removeImageInput) {
    removeImageInput.checked = false;
  }
  if (imageInput) {
    imageInput.value = '';
  }

  renderUserImage(user.image_url || '', getInitialsLabel(user));
  showEditPanel();
}

document.addEventListener('DOMContentLoaded', function () {
    const openAddUserPanelBtn = document.getElementById('openAddUserPanelBtn');
    const closeAddUserPanelBtn = document.getElementById('closeAddUserPanelBtn');
    const cancelAddUserBtn = document.getElementById('cancelAddUserBtn');

    if (openAddUserPanelBtn) {
      openAddUserPanelBtn.addEventListener('click', showAddPanel);
    }
    if (closeAddUserPanelBtn) {
      closeAddUserPanelBtn.addEventListener('click', hideAddPanel);
    }
    if (cancelAddUserBtn) {
      cancelAddUserBtn.addEventListener('click', hideAddPanel);
    }

  if (typeof DataTable !== 'undefined') {
    const dataTableLangUrl =
      (window.SeiryokukaiConfig && window.SeiryokukaiConfig.dataTableLangUrl)
      || '';
    const api = (window.SeiryokukaiConfig && window.SeiryokukaiConfig.api) || {};
    const usersApiUrl = api.utenti || '';

    const escapeHtml = (value) => String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');

    usersDataTable = new DataTable('#utenti-table', {
      serverSide: true,
      processing: true,
      pageLength: 10,
      order: [[1, 'asc']],
      ajax: {
        url: usersApiUrl,
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
            const label = escapeHtml(row.name || row.username || 'Utente');
            const initials = escapeHtml(getInitialsLabel(row));
            if (imgUrl !== '') {
              return '<img src="' + escapeHtml(imgUrl) + '" alt="' + label + '" style="width: 36px; height: 36px; object-fit: cover; border-radius: 50%;" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'inline-flex\';">'
                + '<span class="badge text-bg-light border" style="display:none;width: 36px; height: 36px; line-height: 26px;">' + initials + '</span>';
            }
            return '<span class="badge text-bg-light border" style="width: 36px; height: 36px; line-height: 26px;">' + initials + '</span>';
          },
        },
        { data: 'name' },
        { data: 'username' },
        { data: 'email' },
        { data: 'role' },
        {
          data: 'status',
          render: function (data) {
            const active = data === 'Attivo';
            const cls = active ? 'success' : 'secondary';
            return '<span class="badge text-bg-' + cls + '">' + escapeHtml(data) + '</span>';
          },
        },
        {
          data: 'data_scadenza_account',
          render: function (data, type) {
            const raw = String(data || '').trim();
            if (raw === '') {
              return '';
            }
            const isoDate = raw.slice(0, 10);
            const match = isoDate.match(/^(\d{4})-(\d{2})-(\d{2})$/);
            if (!match) {
              return escapeHtml(isoDate);
            }

            const formatted = match[3] + '/' + match[2] + '/' + match[1];
            if (type === 'display' || type === 'filter') {
              return escapeHtml(formatted);
            }

            return isoDate;
          },
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          className: 'text-end',
          render: function (data, type, row) {
            const id = Number((row && row.meta && row.meta.id) || row.id || 0);
            const isCurrentUser = id === currentUserId;
            const isActive = row.status === 'Attivo';
            const nextStatus = isActive ? 'Sospeso' : 'Attivo';
            const statusLabel = isActive ? 'Sospendi' : 'Attiva';
            const statusClass = isActive ? 'btn-outline-warning' : 'btn-outline-success';
            const restrictedActionDisabled = isCurrentUser ? ' disabled title="Azione non consentita sul proprio utente"' : '';
            const editPayload = escapeHtml(JSON.stringify(row));

            return ''
              + '<div class="d-flex justify-content-end gap-2">'
                + '<button class="btn btn-sm btn-outline-primary" type="button" onclick="loadUserData(JSON.parse(this.dataset.user));" data-user="' + editPayload + '">Modifica</button>'
              + '<form method="post" action="' + escapeHtml(usersApiUrl) + '">'
              + '<input type="hidden" name="action" value="status">'
              + '<input type="hidden" name="id" value="' + id + '">'
              + '<input type="hidden" name="status" value="' + nextStatus + '">'
                + '<button class="btn btn-sm ' + statusClass + '" type="submit"' + restrictedActionDisabled + '>' + statusLabel + '</button>'
              + '</form>'
              + '<form method="post" action="' + escapeHtml(usersApiUrl) + '" onsubmit="return confirm(\'Eliminare questo utente?\');">'
              + '<input type="hidden" name="action" value="delete">'
              + '<input type="hidden" name="id" value="' + id + '">'
              + '<button class="btn btn-sm btn-outline-danger" type="submit"' + (isCurrentUser ? ' disabled title="Utente corrente non eliminabile"' : '') + '>Elimina</button>'
              + '</form>'
              + '</div>';
          },
        },
      ],
    });
  }

  const closeEditPanelBtn = document.getElementById('closeEditPanelBtn');
  const cancelEditUserBtn = document.getElementById('cancelEditUserBtn');
  if (closeEditPanelBtn) {
    closeEditPanelBtn.addEventListener('click', hideEditPanel);
  }
  if (cancelEditUserBtn) {
    cancelEditUserBtn.addEventListener('click', hideEditPanel);
  }

  const imageInput = document.getElementById('editUserImage');
  if (imageInput) {
    imageInput.addEventListener('change', function () {
      const removeImageInput = document.getElementById('editUserRemoveImage');
      const currentInitials = getCurrentEditInitials();
      const cropDataInput = document.getElementById('editUserCropImageData');

      if (removeImageInput) {
        removeImageInput.checked = false;
      }
      if (cropDataInput) {
        cropDataInput.value = '';
      }

      if (!this.files || this.files.length === 0) {
        destroyUserImageCropper();
        const currentImage = document.getElementById('editUserCurrentImagePath').value || '';
        const currentImageUrl = currentImage !== '' ? appRootPath + '/' + String(currentImage).replace(/^\/+/, '') : '';
        renderUserImage(currentImageUrl, currentInitials);
        return;
      }

      const file = this.files[0];
      const allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
      const maxSize = 5 * 1024 * 1024;

      if (!allowedMimes.includes(file.type)) {
        window.alert('Formato immagine non supportato. Usa JPG, PNG, WEBP o GIF.');
        this.value = '';
        renderUserImage('', currentInitials);
        return;
      }

      if (file.size > maxSize) {
        window.alert('Immagine troppo grande. Dimensione massima 5MB.');
        this.value = '';
        renderUserImage('', currentInitials);
        return;
      }

      const reader = new FileReader();
      reader.onload = function (event) {
        const dataUrl = event.target && event.target.result ? String(event.target.result) : '';
        if (dataUrl === '') {
          return;
        }

        const cropShown = showUserImageCropper(dataUrl);
        if (!cropShown) {
          renderUserImage(dataUrl, currentInitials);
        }
      };
      reader.readAsDataURL(file);
    });
  }

  const applyImageCropBtn = document.getElementById('applyImageCropBtn');
  if (applyImageCropBtn) {
    applyImageCropBtn.addEventListener('click', function () {
      if (!applyCurrentCrop()) {
        window.alert('Impossibile applicare il ritaglio immagine.');
      }
    });
  }

  const cancelImageCropBtn = document.getElementById('cancelImageCropBtn');
  if (cancelImageCropBtn) {
    cancelImageCropBtn.addEventListener('click', function () {
      const imageInputField = document.getElementById('editUserImage');
      if (imageInputField) {
        imageInputField.value = '';
      }
      destroyUserImageCropper();
      const currentImage = document.getElementById('editUserCurrentImagePath').value || '';
      const currentImageUrl = currentImage !== '' ? appRootPath + '/' + String(currentImage).replace(/^\/+/, '') : '';
      renderUserImage(currentImageUrl, getCurrentEditInitials());
    });
  }

  const removeImageInput = document.getElementById('editUserRemoveImage');
  if (removeImageInput) {
    removeImageInput.addEventListener('change', function () {
      if (this.checked) {
        const imageInputField = document.getElementById('editUserImage');
        const cropDataInput = document.getElementById('editUserCropImageData');
        if (imageInputField) {
          imageInputField.value = '';
        }
        if (cropDataInput) {
          cropDataInput.value = '';
        }
        destroyUserImageCropper();
        const currentInitials = getCurrentEditInitials();
        renderUserImage('', currentInitials);
      } else {
        const currentImage = document.getElementById('editUserCurrentImagePath').value || '';
        const currentImageUrl = currentImage !== '' ? appRootPath + '/' + String(currentImage).replace(/^\/+/, '') : '';
        const currentInitials = getCurrentEditInitials();
        renderUserImage(currentImageUrl, currentInitials);
      }
    });
  }

  const addImageInput = document.getElementById('addUserImage');
  if (addImageInput) {
    addImageInput.addEventListener('change', function () {
      const currentInitials = getCurrentAddInitials();
      const cropDataInput = document.getElementById('addUserCropImageData');

      if (cropDataInput) {
        cropDataInput.value = '';
      }

      if (!this.files || this.files.length === 0) {
        destroyAddUserImageCropper();
        renderAddUserImage('', currentInitials);
        return;
      }

      const file = this.files[0];
      const allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
      const maxSize = 5 * 1024 * 1024;

      if (!allowedMimes.includes(file.type)) {
        window.alert('Formato immagine non supportato. Usa JPG, PNG, WEBP o GIF.');
        this.value = '';
        renderAddUserImage('', currentInitials);
        return;
      }

      if (file.size > maxSize) {
        window.alert('Immagine troppo grande. Dimensione massima 5MB.');
        this.value = '';
        renderAddUserImage('', currentInitials);
        return;
      }

      const reader = new FileReader();
      reader.onload = function (event) {
        const dataUrl = event.target && event.target.result ? String(event.target.result) : '';
        if (dataUrl === '') {
          return;
        }

        const cropShown = showAddUserImageCropper(dataUrl);
        if (!cropShown) {
          renderAddUserImage(dataUrl, currentInitials);
        }
      };
      reader.readAsDataURL(file);
    });
  }

  const applyAddImageCropBtn = document.getElementById('applyAddImageCropBtn');
  if (applyAddImageCropBtn) {
    applyAddImageCropBtn.addEventListener('click', function () {
      if (!applyCurrentAddCrop()) {
        window.alert('Impossibile applicare il ritaglio immagine.');
      }
    });
  }

  const cancelAddImageCropBtn = document.getElementById('cancelAddImageCropBtn');
  if (cancelAddImageCropBtn) {
    cancelAddImageCropBtn.addEventListener('click', function () {
      const imageInputField = document.getElementById('addUserImage');
      if (imageInputField) {
        imageInputField.value = '';
      }
      const cropDataInput = document.getElementById('addUserCropImageData');
      if (cropDataInput) {
        cropDataInput.value = '';
      }
      destroyAddUserImageCropper();
      renderAddUserImage('', getCurrentAddInitials());
    });
  }

  const addUserForm = document.getElementById('addUserForm');
  if (addUserForm) {
    addUserForm.addEventListener('submit', async function (event) {
      if (!addUserForm.checkValidity()) {
        event.preventDefault();
        addUserForm.reportValidity();
        return;
      }

      const cropDataInput = document.getElementById('addUserCropImageData');
      const hasCropData = cropDataInput && String(cropDataInput.value || '').trim() !== '';
      if (!hasCropData && addUserImageCropper) {
        applyCurrentAddCrop();
      }

      if (typeof window.fetch !== 'function') {
        return;
      }

      event.preventDefault();

      const submitButton = addUserForm.querySelector('button[type="submit"]');
      if (submitButton) {
        submitButton.disabled = true;
      }

      try {
        const formData = new FormData(addUserForm);
        formData.append('ajax', '1');

        const response = await fetch(usersApiUrl, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
          },
        });

        const payload = await response.json();
        if (!response.ok || !payload || payload.ok !== true) {
          const message = payload && payload.message ? String(payload.message) : 'Errore durante il salvataggio utente';
          window.alert(message);
          return;
        }

        hideAddPanel();
        resetAddUserForm();
        if (usersDataTable && usersDataTable.ajax && typeof usersDataTable.ajax.reload === 'function') {
          usersDataTable.ajax.reload(null, false);
        }
      } catch (error) {
        window.alert('Errore di rete durante il salvataggio utente');
      } finally {
        if (submitButton) {
          submitButton.disabled = false;
        }
      }
    });
  }

  const editUserForm = document.getElementById('editUserForm');
  if (editUserForm) {
    editUserForm.addEventListener('submit', function (event) {
      if (!editUserForm.checkValidity()) {
        event.preventDefault();
        editUserForm.reportValidity();
        return;
      }

      const cropDataInput = document.getElementById('editUserCropImageData');
      const hasCropData = cropDataInput && String(cropDataInput.value || '').trim() !== '';
      if (!hasCropData && userImageCropper) {
        applyCurrentCrop();
      }
    });
  }

  const openEdit = <?= $openEdit ? 'true' : 'false' ?>;
  if (!openEdit) {
    return;
  }

  const prefill = <?= json_encode($editPrefill, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  loadUserData(prefill);
});
</script>
