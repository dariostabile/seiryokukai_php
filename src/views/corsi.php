<?php

declare(strict_types=1);

/** @var array $corsi */
/** @var array $sedi */
/** @var array $discipline */
/** @var array $users */

$frontendApi = frontend_api_urls();
$corsiApiUrl = (string) ($frontendApi['corsi'] ?? '');

$okMessage = trim((string) ($_GET['ok'] ?? ''));
$errMessage = trim((string) ($_GET['err'] ?? ''));

$dayLabels = [
    'lun' => 'Lunedi',
    'mar' => 'Martedi',
    'mer' => 'Mercoledi',
    'gio' => 'Giovedi',
    'ven' => 'Venerdi',
    'sab' => 'Sabato',
    'dom' => 'Domenica',
];

$addPrefill = [
    'name' => trim((string) ($_GET['add_name'] ?? '')),
    'sede_id' => (int) ($_GET['add_sede_id'] ?? 0),
    'disciplina_id' => (int) ($_GET['add_disciplina_id'] ?? 0),
    'user_id' => (int) ($_GET['add_user_id'] ?? 0),
    'start_date' => trim((string) ($_GET['add_start_date'] ?? '')),
    'end_date' => trim((string) ($_GET['add_end_date'] ?? '')),
    'monthly_fee' => trim((string) ($_GET['add_monthly_fee'] ?? '')),
    'active' => (int) ($_GET['add_active'] ?? 1),
    'only_active' => ((int) ($_GET['add_only_active'] ?? 1)) === 1,
];

foreach (array_keys($dayLabels) as $dayKey) {
    $addPrefill[$dayKey . '_inizio'] = trim((string) ($_GET['add_' . $dayKey . '_inizio'] ?? ''));
    $addPrefill[$dayKey . '_fine'] = trim((string) ($_GET['add_' . $dayKey . '_fine'] ?? ''));
}

$openAddPanel =
    $addPrefill['name'] !== ''
    || $addPrefill['sede_id'] > 0
    || $addPrefill['disciplina_id'] > 0
    || $addPrefill['user_id'] > 0
    || $addPrefill['start_date'] !== ''
    || $addPrefill['end_date'] !== ''
    || $addPrefill['monthly_fee'] !== '';

$openEdit = ((string) ($_POST['open_edit'] ?? $_GET['open_edit'] ?? '0')) === '1';
$editPrefill = [
  'id' => (int) ($_POST['edit_id'] ?? $_GET['edit_id'] ?? 0),
  'name' => trim((string) ($_POST['edit_name'] ?? $_GET['edit_name'] ?? '')),
  'sede_id' => (int) ($_POST['edit_sede_id'] ?? $_GET['edit_sede_id'] ?? 0),
  'disciplina_id' => (int) ($_POST['edit_disciplina_id'] ?? $_GET['edit_disciplina_id'] ?? 0),
  'user_id' => (int) ($_POST['edit_user_id'] ?? $_GET['edit_user_id'] ?? 0),
  'start_date' => trim((string) ($_POST['edit_start_date'] ?? $_GET['edit_start_date'] ?? '')),
  'end_date' => trim((string) ($_POST['edit_end_date'] ?? $_GET['edit_end_date'] ?? '')),
  'monthly_fee' => trim((string) ($_POST['edit_monthly_fee'] ?? $_GET['edit_monthly_fee'] ?? '')),
  'active' => (int) ($_POST['edit_active'] ?? $_GET['edit_active'] ?? 1),
];

foreach (array_keys($dayLabels) as $dayKey) {
  $editPrefill[$dayKey . '_inizio'] = trim((string) ($_POST['edit_' . $dayKey . '_inizio'] ?? $_GET['edit_' . $dayKey . '_inizio'] ?? ''));
  $editPrefill[$dayKey . '_fine'] = trim((string) ($_POST['edit_' . $dayKey . '_fine'] ?? $_GET['edit_' . $dayKey . '_fine'] ?? ''));
}
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
      <h5 class="m-0">Gestione Corsi</h5>
      <button class="btn btn-success" type="button" id="openAddCorsoPanelBtn">+ Aggiungi Corso</button>
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

    <div id="corsiAjaxAlert" class="alert d-none" role="alert"></div>

    <div class="table-responsive">
      <div class="d-flex justify-content-end mb-2">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="corsiOnlyActive" <?= $addPrefill['only_active'] ? 'checked' : '' ?>>
          <label class="form-check-label" for="corsiOnlyActive">Mostra solo corsi attivi</label>
        </div>
      </div>
      <table id="corsi-table" class="table align-middle js-datatable" data-server-side="1">
        <thead>
          <tr>
              <th>Immagine</th>
            <th>Corso</th>
            <th>Istruttore</th>
            <th>Inizio</th>
            <th>Fine</th>
            <th>Stato</th>
            <th>Orari</th>
            <th class="text-end">Azioni</th>
          </tr>
        </thead>
        <tbody></tbody>

      </table>
    </div>

    <div id="addCorsoPanel" class="card border mt-4 <?= $openAddPanel ? '' : 'd-none' ?>">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Scheda Nuovo Corso</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeAddCorsoPanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="<?= htmlspecialchars($corsiApiUrl) ?>" class="row g-3" id="addCorsoForm">
          <input type="hidden" name="action" value="add">
            <input type="hidden" name="form_action" value="add">
          <?php
          $corsoFormValues = $addPrefill;
          $corsoFormIsEdit = false;
          $corsoFieldIds = [
              'active' => '',
              'start_date' => '',
              'end_date' => '',
              'name' => '',
              'disciplina_id' => '',
              'user_id' => '',
              'sede_id' => '',
              'monthly_fee' => '',
          ];
          foreach (array_keys($dayLabels) as $dayKey) {
              $corsoFieldIds[$dayKey . '_inizio'] = '';
              $corsoFieldIds[$dayKey . '_fine'] = '';
          }
          require __DIR__ . '/partials/corso_form_fields.php';
          ?>
        </form>
      </div>
    </div>

    <div id="editCorsoPanel" class="card border mt-4 d-none">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Scheda Corso</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeEditCorsoPanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="<?= htmlspecialchars($corsiApiUrl) ?>" class="row g-3" id="editCorsoForm">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="corso_id" id="editCorsoId">
          <?php
          $corsoFormValues = [
              'active' => 1,
              'start_date' => '',
              'end_date' => '',
              'name' => '',
              'disciplina_id' => 0,
              'user_id' => 0,
              'sede_id' => 0,
              'monthly_fee' => '',
          ];
          foreach (array_keys($dayLabels) as $dayKey) {
              $corsoFormValues[$dayKey . '_inizio'] = '';
              $corsoFormValues[$dayKey . '_fine'] = '';
          }

          $corsoFormIsEdit = true;
          $corsoFieldIds = [
              'active' => 'editCorsoActive',
              'start_date' => 'editCorsoStartDate',
              'end_date' => 'editCorsoEndDate',
              'name' => 'editCorsoName',
              'disciplina_id' => 'editCorsoDisciplina',
              'user_id' => 'editCorsoUser',
              'sede_id' => 'editCorsoSede',
              'monthly_fee' => 'editCorsoMonthlyFee',
          ];
          foreach (array_keys($dayLabels) as $dayKey) {
              $corsoFieldIds[$dayKey . '_inizio'] = 'edit_' . $dayKey . '_inizio';
              $corsoFieldIds[$dayKey . '_fine'] = 'edit_' . $dayKey . '_fine';
          }
          require __DIR__ . '/partials/corso_form_fields.php';
          ?>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {


    // === Gestione immagine corso (preview, crop inline, rimozione) ===
    // Funziona sia in add che in edit panel, come atleta (no modale)
    function setupCorsoImageHandlers(formElement) {
      if (!formElement) {
        return;
      }

      const imageInput = formElement.querySelector('#corsoImageInput');
      const imagePreview = formElement.querySelector('#corsoImagePreview');
      const imagePlaceholder = formElement.querySelector('#corsoImagePlaceholder');
      const cropContainer = formElement.querySelector('#corsoImageCropContainer');
      const cropSource = formElement.querySelector('#corsoImageCropSource');
      const cropApplyBtn = formElement.querySelector('#corsoImageApplyCropBtn');
      const cropCancelBtn = formElement.querySelector('#corsoImageCancelCropBtn');
      const cropBase64Input = formElement.querySelector('#corsoCropImageBase64');
      const removeCheckbox = formElement.querySelector('#removeImmagineCorsoCheckbox');
      let cropper = null;
      const ALLOWED = ['image/jpeg', 'image/png'];
      const MAX_SIZE = 2 * 1024 * 1024;

      function showPreview(src) {
        if (imagePreview) {
          imagePreview.src = src || '';
          imagePreview.classList.toggle('d-none', !src);
        }
        if (imagePlaceholder) {
          imagePlaceholder.classList.toggle('d-none', !!src);
        }
      }

      function destroyCropper() {
        if (cropper && typeof cropper.destroy === 'function') cropper.destroy();
        cropper = null;
        if (cropContainer) cropContainer.classList.add('d-none');
      }

      function showCropper(dataUrl) {
        if (!cropSource || !cropContainer) return false;
        cropSource.src = dataUrl;
        cropContainer.classList.remove('d-none');
        if (window.Cropper) {
          cropper = new window.Cropper(cropSource, {
            aspectRatio: 1,
            viewMode: 1,
            autoCropArea: 1,
            background: false,
            movable: true,
            zoomable: true,
            scalable: false,
            rotatable: false
          });
        }
        return true;
      }

      function applyCrop() {
        if (!cropper) return false;
        const canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });
        if (!canvas) return false;
        const dataUrl = canvas.toDataURL('image/png');
        showPreview(dataUrl);
        if (cropBase64Input) cropBase64Input.value = dataUrl;
        destroyCropper();
        return true;
      }

      if (imageInput) {
        imageInput.addEventListener('change', function () {
          const file = imageInput.files && imageInput.files[0] ? imageInput.files[0] : null;
          if (!file) {
            showPreview('');
            if (cropBase64Input) cropBase64Input.value = '';
            destroyCropper();
            return;
          }
          if (!ALLOWED.includes(file.type)) {
            window.alert('Formato immagine non supportato. Usa JPG o PNG.');
            imageInput.value = '';
            showPreview('');
            if (cropBase64Input) cropBase64Input.value = '';
            return;
          }
          if (file.size > MAX_SIZE) {
            window.alert('Immagine troppo grande. Dimensione massima 2MB.');
            imageInput.value = '';
            showPreview('');
            if (cropBase64Input) cropBase64Input.value = '';
            return;
          }
          const reader = new FileReader();
          reader.onload = function (event) {
            const dataUrl = event.target && event.target.result ? String(event.target.result) : '';
            if (dataUrl === '') return;
            showCropper(dataUrl);
          };
          reader.onerror = function () {
            imageInput.value = '';
            showPreview('');
            if (cropBase64Input) cropBase64Input.value = '';
          };
          reader.readAsDataURL(file);
        });
      }

      if (cropApplyBtn) {
        cropApplyBtn.addEventListener('click', function () {
          if (!applyCrop()) {
            window.alert('Impossibile applicare il ritaglio immagine.');
          }
        });
      }

      if (cropCancelBtn) {
        cropCancelBtn.addEventListener('click', function () {
          destroyCropper();
        });
      }

      if (removeCheckbox) {
        removeCheckbox.addEventListener('change', function () {
          if (removeCheckbox.checked) {
            showPreview('');
            if (imageInput) imageInput.value = '';
            if (cropBase64Input) cropBase64Input.value = '';
            destroyCropper();
          }
        });
      }
    }

  const ui = window.SeiryokukaiUi || null;
  const openAddCorsoPanelBtn = document.getElementById('openAddCorsoPanelBtn');
  const addCorsoPanel = document.getElementById('addCorsoPanel');
  const addCorsoForm = document.getElementById('addCorsoForm');
  const editCorsoForm = document.getElementById('editCorsoForm');
  const closeAddCorsoPanelBtn = document.getElementById('closeAddCorsoPanelBtn');
  const cancelAddCorsoBtn = document.getElementById('cancelAddCorsoBtn');
  const corsiAjaxAlert = document.getElementById('corsiAjaxAlert');

  const editCorsoPanel = document.getElementById('editCorsoPanel');
  const closeEditCorsoPanelBtn = document.getElementById('closeEditCorsoPanelBtn');
  const cancelEditCorsoBtn = document.getElementById('cancelEditCorsoBtn');

  const corsiOnlyActiveCheckbox = document.getElementById('corsiOnlyActive');
  let corsiTable = null;

  // Setup immagine separato per form Add/Edit (evita conflitti di ID duplicati nel DOM)
  setupCorsoImageHandlers(addCorsoForm);
  setupCorsoImageHandlers(editCorsoForm);

  function showAlert(type, message) {
    if (ui && typeof ui.showAlert === 'function') {
      ui.showAlert(corsiAjaxAlert, type, message);
      return;
    }

    if (!corsiAjaxAlert) {
      return;
    }

    corsiAjaxAlert.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger');
    corsiAjaxAlert.textContent = String(message || 'Operazione completata');
    corsiAjaxAlert.classList.remove('d-none');
  }

  function hideAlert() {
    if (ui && typeof ui.hideAlert === 'function') {
      ui.hideAlert(corsiAjaxAlert);
      return;
    }

    if (!corsiAjaxAlert) {
      return;
    }

    corsiAjaxAlert.textContent = '';
    corsiAjaxAlert.classList.add('d-none');
  }

  if (openAddCorsoPanelBtn && addCorsoPanel) {
    openAddCorsoPanelBtn.addEventListener('click', function () {
      if (editCorsoPanel) {
        editCorsoPanel.classList.add('d-none');
      }
      addCorsoPanel.classList.remove('d-none');
    });
  }

  [closeAddCorsoPanelBtn, cancelAddCorsoBtn].forEach(function (btn) {
    if (btn && addCorsoPanel) {
      btn.addEventListener('click', function () {
        addCorsoPanel.classList.add('d-none');
        hideAlert();
      });
    }
  });

  [closeEditCorsoPanelBtn, cancelEditCorsoBtn].forEach(function (btn) {
    if (btn && editCorsoPanel) {
      btn.addEventListener('click', function () {
        editCorsoPanel.classList.add('d-none');
      });
    }
  });

  if (addCorsoForm) {
    addCorsoForm.addEventListener('submit', function (event) {
      if (!addCorsoForm.checkValidity()) {
        event.preventDefault();
        addCorsoForm.reportValidity();
      }
    });
  }

  if (editCorsoForm) {
    editCorsoForm.addEventListener('submit', function (event) {
      if (!editCorsoForm.checkValidity()) {
        event.preventDefault();
        editCorsoForm.reportValidity();
      }
    });
  }

  if (typeof DataTable === 'undefined') {
    return;
  }

  const dataTableLangUrl =
    (window.SeiryokukaiConfig && window.SeiryokukaiConfig.dataTableLangUrl)
    || '';
  const api = (window.SeiryokukaiConfig && window.SeiryokukaiConfig.api) || {};
  const corsiApiUrl = api.corsi || '';

  const dayKeys = ['lun', 'mar', 'mer', 'gio', 'ven', 'sab', 'dom'];
  const dayLabels = {
    lun: 'Lun',
    mar: 'Mar',
    mer: 'Mer',
    gio: 'Gio',
    ven: 'Ven',
    sab: 'Sab',
    dom: 'Dom',
  };

  function htmlEscape(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function buildOrariRows(row) {
    const rows = [];

    dayKeys.forEach(function (day) {
      const start = String(row[day + '_inizio'] || '').substring(0, 5);
      const end = String(row[day + '_fine'] || '').substring(0, 5);
      if (start !== '' || end !== '') {
        rows.push(dayLabels[day] + ': ' + (start || '--:--') + '-' + (end || '--:--'));
      }
    });

    return rows;
  }

  function formatDateIt(value, type) {
    const raw = String(value || '').trim();
    if (raw === '') {
      return '';
    }

    const isoDate = raw.substring(0, 10);
    const match = isoDate.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (!match) {
      return htmlEscape(isoDate);
    }

    const formatted = match[3] + '/' + match[2] + '/' + match[1];
    if (type === 'display' || type === 'filter') {
      return htmlEscape(formatted);
    }

    return isoDate;
  }

  function fillEditCorsoForm(row) {
    document.getElementById('editCorsoId').value = row.id || '';
    document.getElementById('editCorsoName').value = row.name || '';
    document.getElementById('editCorsoSede').value = row.sede_id || '';
    document.getElementById('editCorsoDisciplina').value = row.disciplina_id || '';
    document.getElementById('editCorsoUser').value = row.user_id || '';
    document.getElementById('editCorsoStartDate').value = row.start_date || '';
    document.getElementById('editCorsoEndDate').value = row.end_date || '';
    document.getElementById('editCorsoMonthlyFee').value = row.monthly_fee || '';
    document.getElementById('editCorsoActive').value = Number(row.active) === 0 ? '0' : '1';

    dayKeys.forEach(function (day) {
      const startInput = document.getElementById('edit_' + day + '_inizio');
      const endInput = document.getElementById('edit_' + day + '_fine');
      if (startInput) {
        startInput.value = String(row[day + '_inizio'] || '').substring(0, 5);
      }
      if (endInput) {
        endInput.value = String(row[day + '_fine'] || '').substring(0, 5);
      }
    });

    if (editCorsoForm) {
      const imagePath = String(row.image_path || '').trim();
      const imageInput = editCorsoForm.querySelector('#corsoImageInput');
      const imagePreview = editCorsoForm.querySelector('#corsoImagePreview');
      const imagePlaceholder = editCorsoForm.querySelector('#corsoImagePlaceholder');
      const cropBase64Input = editCorsoForm.querySelector('#corsoCropImageBase64');
      const removeCheckbox = editCorsoForm.querySelector('#removeImmagineCorsoCheckbox');

      if (imageInput) {
        imageInput.value = '';
      }
      if (cropBase64Input) {
        cropBase64Input.value = '';
      }
      if (removeCheckbox) {
        removeCheckbox.checked = false;
      }
      if (imagePreview) {
        imagePreview.src = imagePath;
        imagePreview.dataset.initialSrc = imagePath;
        imagePreview.classList.toggle('d-none', imagePath === '');
      }
      if (imagePlaceholder) {
        imagePlaceholder.classList.toggle('d-none', imagePath !== '');
      }
    }

    if (addCorsoPanel) {
      addCorsoPanel.classList.add('d-none');
    }

    if (editCorsoPanel) {
      editCorsoPanel.classList.remove('d-none');
      editCorsoPanel.scrollIntoView({ behavior: 'smooth' });
    }
  }

  corsiTable = new DataTable('#corsi-table', {
    serverSide: true,
    processing: true,
    pageLength: 10,
    order: [[1, 'asc']],
    ajax: {
      url: corsiApiUrl,
      type: 'GET',
      data: function (d) {
        d.active_only = corsiOnlyActiveCheckbox && corsiOnlyActiveCheckbox.checked ? 1 : 0;
      },
    },
    language: {
      url: dataTableLangUrl,
    },
    columns: [
      {
        data: 'image_path',
        orderable: false,
        render: function (data) {
          if (!data) return '';
          return `<img src="${data}" alt="img corso" style="max-width:48px;max-height:48px;object-fit:cover;border-radius:6px;border:1px solid #ccc;">`;
        }
      },
      {
        data: 'name',
        render: function (value, type, row) {
          const corsoName = htmlEscape(value);
          const sedeName = htmlEscape(row.sede || '');
          if (type !== 'display') {
            return String(value || '');
          }
          if (sedeName === '') {
            return '<span>' + corsoName + '</span>';
          }
          return ''
            + '<span>' + corsoName + '</span>'
            + '<small class="d-block text-muted">[' + sedeName + ']</small>';
        }
      },
      { data: 'teacher' },
      {
        data: 'start_date',
        render: function (value, type) {
          return formatDateIt(value, type);
        }
      },
      {
        data: 'end_date',
        render: function (value, type) {
          return formatDateIt(value, type);
        }
      },
      {
        data: 'active',
        render: function (value) {
          return Number(value) === 1
            ? '<span class="badge text-bg-success">Attivo</span>'
            : '<span class="badge text-bg-secondary">Non attivo</span>';
        }
      },
      {
        data: null,
        render: function (row) {
          const rows = buildOrariRows(row);
          if (rows.length === 0) {
            return '<small>--</small>';
          }

          return rows.map(function (line) {
            return '<small class="d-block">' + htmlEscape(line) + '</small>';
          }).join('');
        }
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-end',
        render: function (row) {
          const id = Number(row.id || 0);
          const payload = htmlEscape(JSON.stringify(row));

          return ''
            + '<button class="btn btn-sm btn-outline-primary edit-corso-btn" type="button" data-corso="' + payload + '">Modifica</button> '
            + '<button class="btn btn-sm btn-outline-danger delete-corso-btn" type="button" data-corso-id="' + id + '">Elimina</button>';
        }
      },
    ]
  });

  const tableElement = document.getElementById('corsi-table');
  if (tableElement) {
    tableElement.addEventListener('click', function (event) {
      const button = event.target.closest('.edit-corso-btn');
      if (!button) {
        const deleteBtn = event.target.closest('.delete-corso-btn');
        if (!deleteBtn || !ui || typeof ui.postForm !== 'function') {
          return;
        }

        if (!confirm('Eliminare questo corso?')) {
          return;
        }

        const corsoId = Number(deleteBtn.getAttribute('data-corso-id') || '0');
        if (corsoId <= 0) {
          showAlert('danger', 'ID corso non valido');
          return;
        }

        const tempForm = document.createElement('form');
        tempForm.action = corsiApiUrl;
        tempForm.method = 'post';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'corso_id';
        idInput.value = String(corsoId);

        tempForm.appendChild(actionInput);
        tempForm.appendChild(idInput);

        ui.postForm(tempForm.getAttribute('action'), tempForm)
          .then(function (payload) {
            showAlert('success', payload.message || 'Corso eliminato con successo');
            if (corsiTable) {
              corsiTable.ajax.reload(null, false);
            }
            if (editCorsoPanel && !editCorsoPanel.classList.contains('d-none')) {
              const currentId = Number((document.getElementById('editCorsoId').value || '0'));
              if (currentId === corsoId) {
                editCorsoPanel.classList.add('d-none');
              }
            }
          })
          .catch(function (errorPayload) {
            const message = (errorPayload && errorPayload.message) ? errorPayload.message : 'Errore durante eliminazione corso';
            showAlert('danger', message);
          });

        return;
      }

      const payload = button.getAttribute('data-corso') || '{}';
      let row = {};
      try {
        row = JSON.parse(payload);
      } catch (error) {
        row = {};
      }

      fillEditCorsoForm(row);
    });
  }

  if (corsiOnlyActiveCheckbox) {
    corsiOnlyActiveCheckbox.addEventListener('change', function () {
      corsiTable.ajax.reload();
    });
  }

  if (addCorsoForm && ui && typeof ui.postForm === 'function') {
    addCorsoForm.addEventListener('submit', async function (event) {
      event.preventDefault();
      hideAlert();

      const submitButton = addCorsoForm.querySelector('button[type="submit"]');
      if (submitButton) {
        submitButton.disabled = true;
      }

      try {
        const payload = await ui.postForm(addCorsoForm.getAttribute('action'), addCorsoForm);
        showAlert('success', payload.message || 'Corso creato con successo');

        addCorsoForm.reset();
        const activeField = addCorsoForm.querySelector('[name="active"]');
        if (activeField) {
          activeField.value = '1';
        }

        if (addCorsoPanel) {
          addCorsoPanel.classList.add('d-none');
        }

        if (corsiTable) {
          corsiTable.ajax.reload(null, false);
        }
      } catch (errorPayload) {
        const message = (errorPayload && errorPayload.message) ? errorPayload.message : 'Errore durante il salvataggio del corso';
        showAlert('danger', message);
        if (addCorsoPanel) {
          addCorsoPanel.classList.remove('d-none');
        }
      } finally {
        if (submitButton) {
          submitButton.disabled = false;
        }
      }
    });
  }

  if (editCorsoForm && ui && typeof ui.postForm === 'function') {
    editCorsoForm.addEventListener('submit', async function (event) {
      event.preventDefault();
      hideAlert();

      const submitButton = editCorsoForm.querySelector('button[type="submit"]');
      if (submitButton) {
        submitButton.disabled = true;
      }

      try {
        const payload = await ui.postForm(editCorsoForm.getAttribute('action'), editCorsoForm);
        showAlert('success', payload.message || 'Corso modificato con successo');

        if (editCorsoPanel) {
          editCorsoPanel.classList.add('d-none');
        }

        if (corsiTable) {
          corsiTable.ajax.reload(null, false);
        }
      } catch (errorPayload) {
        const message = (errorPayload && errorPayload.message) ? errorPayload.message : 'Errore durante aggiornamento corso';
        showAlert('danger', message);
        if (editCorsoPanel) {
          editCorsoPanel.classList.remove('d-none');
        }
      } finally {
        if (submitButton) {
          submitButton.disabled = false;
        }
      }
    });
  }

  <?php if ($openEdit && $editPrefill['id'] > 0): ?>
    fillEditCorsoForm({
      id: <?= (int) $editPrefill['id'] ?>,
      name: <?= json_encode($editPrefill['name']) ?>,
      sede_id: <?= (int) $editPrefill['sede_id'] ?>,
      disciplina_id: <?= (int) $editPrefill['disciplina_id'] ?>,
      user_id: <?= (int) $editPrefill['user_id'] ?>,
      start_date: <?= json_encode($editPrefill['start_date']) ?>,
      end_date: <?= json_encode($editPrefill['end_date']) ?>,
      monthly_fee: <?= json_encode($editPrefill['monthly_fee']) ?>,
      active: <?= (int) $editPrefill['active'] === 0 ? '0' : '1' ?>,
      lun_inizio: <?= json_encode($editPrefill['lun_inizio']) ?>,
      lun_fine: <?= json_encode($editPrefill['lun_fine']) ?>,
      mar_inizio: <?= json_encode($editPrefill['mar_inizio']) ?>,
      mar_fine: <?= json_encode($editPrefill['mar_fine']) ?>,
      mer_inizio: <?= json_encode($editPrefill['mer_inizio']) ?>,
      mer_fine: <?= json_encode($editPrefill['mer_fine']) ?>,
      gio_inizio: <?= json_encode($editPrefill['gio_inizio']) ?>,
      gio_fine: <?= json_encode($editPrefill['gio_fine']) ?>,
      ven_inizio: <?= json_encode($editPrefill['ven_inizio']) ?>,
      ven_fine: <?= json_encode($editPrefill['ven_fine']) ?>,
      sab_inizio: <?= json_encode($editPrefill['sab_inizio']) ?>,
      sab_fine: <?= json_encode($editPrefill['sab_fine']) ?>,
      dom_inizio: <?= json_encode($editPrefill['dom_inizio']) ?>,
      dom_fine: <?= json_encode($editPrefill['dom_fine']) ?>,
    });
  <?php endif; ?>
});
</script>