<?php

declare(strict_types=1);

/** @var array $sedi */

$frontendApi = frontend_api_urls();
$sediApiUrl = (string) ($frontendApi['sedi'] ?? '');

$okMessage = trim((string) ($_GET['ok'] ?? ''));
$errMessage = trim((string) ($_GET['err'] ?? ''));
$addPrefill = [
  'name' => trim((string) ($_GET['add_name'] ?? '')),
  'code' => trim((string) ($_GET['add_code'] ?? '')),
  'active' => (int) ($_GET['add_active'] ?? 1),
];
$openAddPanel = $addPrefill['name'] !== '' || $addPrefill['code'] !== '';

$openEdit = ((string) ($_GET['open_edit'] ?? '0')) === '1';
$editPrefill = [
  'id' => (int) ($_GET['edit_id'] ?? 0),
  'name' => trim((string) ($_GET['edit_name'] ?? '')),
  'code' => trim((string) ($_GET['edit_code'] ?? '')),
  'active' => (int) ($_GET['edit_active'] ?? 1),
];
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
      <h5 class="m-0">Gestione Sedi</h5>
      <button class="btn btn-success" type="button" id="openAddSitePanel">+ Aggiungi sede</button>
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

    <div id="sediAjaxAlert" class="alert d-none" role="alert"></div>

    <div class="table-responsive">
      <div class="d-flex justify-content-end mb-2">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="sediOnlyActive">
          <label class="form-check-label" for="sediOnlyActive">Mostra solo sedi attive</label>
        </div>
      </div>
      <table id="sedi-table" class="table align-middle js-datatable table-hover" data-server-side="1">
        <thead>
          <tr>
            <th>Sede</th>
            <th>Codice</th>
            <th>Stato</th>
            <th class="text-end">Azioni</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <div id="addSedePanel" class="card border mt-4 <?= $openAddPanel ? '' : 'd-none' ?>">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Scheda Nuova Sede</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeAddSitePanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($sediApiUrl) ?>" class="row g-3" id="addSedeForm">
          <input type="hidden" name="action" value="add">

          <div class="col-12 col-md-6">
            <label class="form-label">Nome Sede</label>
            <input class="form-control" name="name" placeholder="Nome della sede" required value="<?= htmlspecialchars($addPrefill['name']) ?>">
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Codice Sede</label>
            <input class="form-control" name="code" placeholder="Codice (es. PALERMO)" value="<?= htmlspecialchars($addPrefill['code']) ?>">
            <small class="text-muted">Se lasciato vuoto, verrà generato dal nome.</small>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Stato</label>
            <select class="form-select" name="active">
              <option value="1" <?= (int) $addPrefill['active'] === 1 ? 'selected' : '' ?>>Attiva</option>
              <option value="0" <?= (int) $addPrefill['active'] === 0 ? 'selected' : '' ?>>Non attiva</option>
            </select>
          </div>

          <div class="col-12 d-flex justify-content-end gap-2">
            <button class="btn btn-secondary" type="button" id="cancelAddSiteBtn">Annulla</button>
            <button class="btn btn-success" type="submit">+ Aggiungi Sede</button>
          </div>
        </form>
      </div>
    </div>

    <div id="editSitePanel" class="card border mt-4 d-none">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Scheda Sede</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeEditSitePanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($sediApiUrl) ?>" class="row g-3" id="editSiteForm">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" id="editSiteId">

          <div class="col-12 col-md-6">
            <label class="form-label">Nome Sede</label>
            <input class="form-control" name="name" id="editSiteName" placeholder="Nome della sede" required>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Codice Sede</label>
            <input class="form-control" name="code" id="editSiteCode" placeholder="Codice (es. PALERMO)">
            <small class="text-muted">Se lasciato vuoto, verrà generato dal nome.</small>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Stato</label>
            <select class="form-select" name="active" id="editSiteActive">
              <option value="1">Attiva</option>
              <option value="0">Non attiva</option>
            </select>
          </div>

          <div class="col-12 d-flex justify-content-end gap-2">
            <button class="btn btn-secondary" type="button" id="cancelEditSiteBtn">Annulla</button>
            <button class="btn btn-warning" type="submit">Salva Modifiche</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const ui = window.SeiryokukaiUi || null;
  const addPanelBtn = document.getElementById('openAddSitePanel');
  const addPanel = document.getElementById('addSedePanel');
  const closeAddPanelBtn = document.getElementById('closeAddSitePanelBtn');
  const cancelAddBtn = document.getElementById('cancelAddSiteBtn');
  const addForm = document.getElementById('addSedeForm');

  const editPanel = document.getElementById('editSitePanel');
  const closeEditPanelBtn = document.getElementById('closeEditSitePanelBtn');
  const cancelEditBtn = document.getElementById('cancelEditSiteBtn');
  const editForm = document.getElementById('editSiteForm');

  const tableEl = document.getElementById('sedi-table');
  const onlyActiveCheckbox = document.getElementById('sediOnlyActive');
  const ajaxAlert = document.getElementById('sediAjaxAlert');

  function showAlert(type, message) {
    if (ui && typeof ui.showAlert === 'function') {
      ui.showAlert(ajaxAlert, type, message);
      return;
    }

    if (!ajaxAlert) {
      return;
    }

    ajaxAlert.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger');
    ajaxAlert.textContent = String(message || 'Operazione completata');
    ajaxAlert.classList.remove('d-none');
  }

  function hideAlert() {
    if (ui && typeof ui.hideAlert === 'function') {
      ui.hideAlert(ajaxAlert);
      return;
    }

    if (!ajaxAlert) {
      return;
    }

    ajaxAlert.textContent = '';
    ajaxAlert.classList.add('d-none');
  }

  // Apri panel aggiunta
  addPanelBtn.addEventListener('click', () => {
    hideAlert();
    addPanel.classList.remove('d-none');
  });

  // Chiudi panel aggiunta
  [closeAddPanelBtn, cancelAddBtn].forEach(btn => {
    btn.addEventListener('click', () => {
      addPanel.classList.add('d-none');
      hideAlert();
    });
  });

  // Chiudi panel modifica
  [closeEditPanelBtn, cancelEditBtn].forEach(btn => {
    btn.addEventListener('click', () => {
      editPanel.classList.add('d-none');
      hideAlert();
    });
  });

  if (!tableEl || typeof DataTable === 'undefined') {
    return;
  }

  const dataTableLangUrl =
    (window.SeiryokukaiConfig && window.SeiryokukaiConfig.dataTableLangUrl)
    || '';
  const api = (window.SeiryokukaiConfig && window.SeiryokukaiConfig.api) || {};
  const sediApiUrl = api.sedi || '';

  // DataTable per sedi
  if (tableEl.__dataTable) {
    tableEl.__dataTable.destroy();
  }

  tableEl.__dataTable = new DataTable(tableEl, {
    serverSide: true,
    processing: true,
    ajax: {
      url: sediApiUrl,
      type: 'GET',
      data: function (d) {
        d.active_only = onlyActiveCheckbox && onlyActiveCheckbox.checked ? 1 : 0;
      }
    },
    columns: [
      { data: 'name' },
      { data: 'code' },
      {
        data: 'active',
        render: function (data) {
          return Number(data) === 1
            ? '<span class="badge text-bg-success">Attiva</span>'
            : '<span class="badge text-bg-secondary">Non attiva</span>';
        }
      },
      {
        data: 'id',
        orderable: false,
        render: function (data, type, row) {
          const active = Number(row.active) === 1 ? 1 : 0;
          return `
            <div class="text-end">
              <button class="btn btn-sm btn-primary edit-site-btn" data-id="${data}" data-name="${htmlEscape(row.name)}" data-code="${htmlEscape(row.code)}" data-active="${active}">Modifica</button>
              <button class="btn btn-sm btn-danger delete-site-btn" data-id="${data}">Elimina</button>
            </div>
          `;
        }
      }
    ],
    order: [[0, 'asc']],
    pageLength: 10,
    language: {
      url: dataTableLangUrl
    }
  });

  // Helper per escape HTML
  function htmlEscape(str) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return String(str ?? '').replace(/[&<>"']/g, m => map[m]);
  }

  // Event delegation per bottoni modifica
  tableEl.addEventListener('click', (e) => {
    const editBtn = e.target.closest('.edit-site-btn');
    if (editBtn) {
      hideAlert();
      const id = parseInt(editBtn.dataset.id);
      const name = editBtn.dataset.name;
      const code = editBtn.dataset.code;
      const active = parseInt(editBtn.dataset.active || '1', 10) === 0 ? '0' : '1';

      document.getElementById('editSiteId').value = id;
      document.getElementById('editSiteName').value = name;
      document.getElementById('editSiteCode').value = code;
      document.getElementById('editSiteActive').value = active;

      editPanel.classList.remove('d-none');
      editPanel.scrollIntoView({ behavior: 'smooth' });
    }

    const deleteBtn = e.target.closest('.delete-site-btn');
    if (deleteBtn) {
      if (!ui || typeof ui.postForm !== 'function') {
        return;
      }

      if (confirm('Sei sicuro di voler eliminare questa sede?')) {
        const id = parseInt(deleteBtn.dataset.id, 10);
        if (!id || id <= 0) {
          showAlert('danger', 'ID sede non valido');
          return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = sediApiUrl;

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;

        form.appendChild(actionInput);
        form.appendChild(idInput);

        ui.postForm(form.action, form)
          .then(function (payload) {
            showAlert('success', payload.message || 'Sede eliminata con successo');
            if (tableEl.__dataTable) {
              tableEl.__dataTable.ajax.reload(null, false);
            }

            const currentEditId = parseInt(document.getElementById('editSiteId').value || '0', 10);
            if (currentEditId === id) {
              editPanel.classList.add('d-none');
            }
          })
          .catch(function (errorPayload) {
            const message = (errorPayload && errorPayload.message) ? errorPayload.message : 'Errore durante eliminazione sede';
            showAlert('danger', message);
          });
      }
    }
  });

  if (addForm && ui && typeof ui.postForm === 'function') {
    addForm.addEventListener('submit', async function (event) {
      event.preventDefault();
      hideAlert();

      if (!addForm.checkValidity()) {
        addForm.reportValidity();
        return;
      }

      const submitButton = addForm.querySelector('button[type="submit"]');
      if (submitButton) {
        submitButton.disabled = true;
      }

      try {
        const payload = await ui.postForm(addForm.action, addForm);
        showAlert('success', payload.message || 'Sede creata con successo');
        addForm.reset();
        const activeField = addForm.querySelector('[name="active"]');
        if (activeField) {
          activeField.value = '1';
        }
        addPanel.classList.add('d-none');
        if (tableEl.__dataTable) {
          tableEl.__dataTable.ajax.reload(null, false);
        }
      } catch (errorPayload) {
        const message = (errorPayload && errorPayload.message) ? errorPayload.message : 'Errore durante salvataggio sede';
        showAlert('danger', message);
        addPanel.classList.remove('d-none');
      } finally {
        if (submitButton) {
          submitButton.disabled = false;
        }
      }
    });
  }

  if (editForm && ui && typeof ui.postForm === 'function') {
    editForm.addEventListener('submit', async function (event) {
      event.preventDefault();
      hideAlert();

      if (!editForm.checkValidity()) {
        editForm.reportValidity();
        return;
      }

      const submitButton = editForm.querySelector('button[type="submit"]');
      if (submitButton) {
        submitButton.disabled = true;
      }

      try {
        const payload = await ui.postForm(editForm.action, editForm);
        showAlert('success', payload.message || 'Sede modificata con successo');
        editPanel.classList.add('d-none');
        if (tableEl.__dataTable) {
          tableEl.__dataTable.ajax.reload(null, false);
        }
      } catch (errorPayload) {
        const message = (errorPayload && errorPayload.message) ? errorPayload.message : 'Errore durante aggiornamento sede';
        showAlert('danger', message);
        editPanel.classList.remove('d-none');
      } finally {
        if (submitButton) {
          submitButton.disabled = false;
        }
      }
    });
  }

  if (onlyActiveCheckbox) {
    onlyActiveCheckbox.addEventListener('change', () => {
      if (tableEl.__dataTable) {
        tableEl.__dataTable.ajax.reload();
      }
    });
  }

  // Gestisci il prefill da query parameters
  <?php if ($openEdit && $editPrefill['id'] > 0): ?>
    document.getElementById('editSiteId').value = <?= (int) $editPrefill['id'] ?>;
    document.getElementById('editSiteName').value = <?= json_encode($editPrefill['name']) ?>;
    document.getElementById('editSiteCode').value = <?= json_encode($editPrefill['code']) ?>;
    document.getElementById('editSiteActive').value = <?= json_encode((string) ((int) $editPrefill['active'] === 0 ? '0' : '1')) ?>;
    editPanel.classList.remove('d-none');
  <?php endif; ?>
});
</script>

