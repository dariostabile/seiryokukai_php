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

$openEdit = ((string) ($_GET['open_edit'] ?? '0')) === '1';
$editPrefill = [
    'id' => (int) ($_GET['edit_id'] ?? 0),
    'name' => trim((string) ($_GET['edit_name'] ?? '')),
    'sede_id' => (int) ($_GET['edit_sede_id'] ?? 0),
    'disciplina_id' => (int) ($_GET['edit_disciplina_id'] ?? 0),
    'user_id' => (int) ($_GET['edit_user_id'] ?? 0),
    'start_date' => trim((string) ($_GET['edit_start_date'] ?? '')),
    'end_date' => trim((string) ($_GET['edit_end_date'] ?? '')),
    'monthly_fee' => trim((string) ($_GET['edit_monthly_fee'] ?? '')),
    'active' => (int) ($_GET['edit_active'] ?? 1),
];

foreach (array_keys($dayLabels) as $dayKey) {
    $editPrefill[$dayKey . '_inizio'] = trim((string) ($_GET['edit_' . $dayKey . '_inizio'] ?? ''));
    $editPrefill[$dayKey . '_fine'] = trim((string) ($_GET['edit_' . $dayKey . '_fine'] ?? ''));
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

    // === Gestione immagine corso (preview, crop, rimozione) ===
    const corsoImageInput = document.getElementById('corsoImageInput');
    const corsoImagePreview = document.getElementById('corsoImagePreview');
    const corsoImagePlaceholder = document.getElementById('corsoImagePlaceholder');
    const corsoImageCropContainer = document.getElementById('corsoImageCropContainer');
    const corsoImageCropSource = document.getElementById('corsoImageCropSource');
    const corsoImageApplyCropBtn = document.getElementById('corsoImageApplyCropBtn');
    const corsoImageCancelCropBtn = document.getElementById('corsoImageCancelCropBtn');
    const corsoImageRemoveCheckbox = document.getElementById('corsoImageRemoveCheckbox');
    let corsoImageCropper = null;
    const ALLOWED_CORSO_IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    const MAX_CORSO_IMAGE_SIZE = 2 * 1024 * 1024;

    function renderCorsoImagePreview(src) {
      if (corsoImagePreview) {
        corsoImagePreview.src = src || '';
        corsoImagePreview.classList.toggle('d-none', !src);
      }
      if (corsoImagePlaceholder) {
        corsoImagePlaceholder.classList.toggle('d-none', !!src);
      }
    }

    function destroyCorsoCropper() {
      if (corsoImageCropper && typeof corsoImageCropper.destroy === 'function') {
        corsoImageCropper.destroy();
      }
      corsoImageCropper = null;
      if (corsoImageCropContainer) {
        corsoImageCropContainer.classList.add('d-none');
      }
    }

    function showCorsoCropper(dataUrl) {
      if (!corsoImageCropSource || !corsoImageCropContainer) return false;
      corsoImageCropSource.src = dataUrl;
      corsoImageCropContainer.classList.remove('d-none');
      if (window.Cropper) {
        corsoImageCropper = new window.Cropper(corsoImageCropSource, {
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

    function applyCorsoCrop() {
      if (!corsoImageCropper) return false;
      const canvas = corsoImageCropper.getCroppedCanvas({ width: 400, height: 400 });
      if (!canvas) return false;
      const dataUrl = canvas.toDataURL('image/png');
      renderCorsoImagePreview(dataUrl);
      destroyCorsoCropper();
      // TODO: se vuoi inviare il crop al backend, salva dataUrl in un hidden input
      return true;
    }

    if (corsoImageInput) {
      corsoImageInput.addEventListener('change', function () {
        const file = corsoImageInput.files && corsoImageInput.files[0] ? corsoImageInput.files[0] : null;
        if (!file) {
          destroyCorsoCropper();
          renderCorsoImagePreview('');
          return;
        }
        if (!ALLOWED_CORSO_IMAGE_MIMES.includes(file.type)) {
          window.alert('Formato immagine non supportato. Usa JPG, PNG, WEBP o GIF.');
          corsoImageInput.value = '';
          renderCorsoImagePreview('');
          return;
        }
        if (file.size > MAX_CORSO_IMAGE_SIZE) {
          window.alert('Immagine troppo grande. Dimensione massima 2MB.');
          corsoImageInput.value = '';
          renderCorsoImagePreview('');
          return;
        }
        const reader = new FileReader();
        reader.onload = function (event) {
          const dataUrl = event.target && event.target.result ? String(event.target.result) : '';
          if (dataUrl === '') return;
          const cropShown = showCorsoCropper(dataUrl);
          if (!cropShown) {
            renderCorsoImagePreview(dataUrl);
          }
        };
        reader.onerror = function () {
          corsoImageInput.value = '';
          renderCorsoImagePreview('');
        };
        reader.readAsDataURL(file);
      });
    }

    if (corsoImageApplyCropBtn) {
      corsoImageApplyCropBtn.addEventListener('click', function () {
        if (!applyCorsoCrop()) {
          window.alert('Impossibile applicare il ritaglio immagine.');
        }
      });
    }

    if (corsoImageCancelCropBtn) {
      corsoImageCancelCropBtn.addEventListener('click', function () {
        if (corsoImageInput) corsoImageInput.value = '';
        destroyCorsoCropper();
        renderCorsoImagePreview('');
      });
    }

    if (corsoImageRemoveCheckbox) {
      corsoImageRemoveCheckbox.addEventListener('change', function () {
        if (corsoImageRemoveCheckbox.checked) {
          renderCorsoImagePreview('');
          if (corsoImageInput) corsoImageInput.value = '';
          destroyCorsoCropper();
        } else {
          // Se deselezionato, ripristina preview iniziale
          if (corsoImagePreview && corsoImagePreview.dataset.initialSrc) {
            renderCorsoImagePreview(corsoImagePreview.dataset.initialSrc);
          }
        }
      });
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