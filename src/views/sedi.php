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
$openSitePanel = $openAddPanel || ($openEdit && $editPrefill['id'] > 0);
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
      <h5 class="m-0">Gestione Sedi</h5>
      <button class="btn btn-success" type="button" id="openAddSitePanel">+ Aggiungi Sede</button>
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

    <div id="sitePanel" class="card border mt-4 <?= $openSitePanel ? '' : 'd-none' ?>">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0" id="sitePanelTitle">Scheda Nuova Sede</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeSitePanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($sediApiUrl) ?>" class="row g-3" id="siteForm">
          <input type="hidden" name="action" id="siteAction" value="add">
          <input type="hidden" name="id" id="siteId" value="">
          <?php
          $sedeFormValues = $openEdit && $editPrefill['id'] > 0
              ? $editPrefill
              : $addPrefill;
          $sedeFormIsEdit = $openEdit && $editPrefill['id'] > 0;
          $sedeFieldIds = [
              'name' => 'editSiteName',
              'code' => 'editSiteCode',
              'active' => 'editSiteActive',
          ];
          $sedeCancelButtonId = 'cancelSiteBtn';
          $sedeSubmitLabel = $sedeFormIsEdit ? 'Salva modifiche' : '+ Aggiungi Sede';
          $sedeSubmitClass = $sedeFormIsEdit ? 'btn-warning' : 'btn-success';
          require __DIR__ . '/partials/sede_form_fields.php';
          ?>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const ui = window.SeiryokukaiUi || null;
  const addPanelBtn = document.getElementById('openAddSitePanel');
  const panel = document.getElementById('sitePanel');
  const closePanelBtn = document.getElementById('closeSitePanelBtn');
  const cancelBtn = document.getElementById('cancelSiteBtn');
  const form = document.getElementById('siteForm');
  const panelTitle = document.getElementById('sitePanelTitle');
  const actionInput = document.getElementById('siteAction');
  const siteIdInput = document.getElementById('siteId');

  const tableEl = document.getElementById('sedi-table');
  const onlyActiveCheckbox = document.getElementById('sediOnlyActive');
  const ajaxAlert = document.getElementById('sediAjaxAlert');

  function getSubmitButton() {
    return form ? form.querySelector('button[type="submit"]') : null;
  }

  function setPanelVisible(visible) {
    if (!panel) {
      return;
    }
    panel.classList.toggle('d-none', !visible);
  }

  function setAddMode() {
    if (!form || !panelTitle || !actionInput || !siteIdInput) {
      return;
    }

    actionInput.value = 'add';
    siteIdInput.value = '';
    panelTitle.textContent = 'Scheda Nuova Sede';

    form.reset();
    const activeField = document.getElementById('editSiteActive');
    if (activeField) {
      activeField.value = '1';
    }

    const submitButton = getSubmitButton();
    if (submitButton) {
      submitButton.textContent = '+ Aggiungi Sede';
      submitButton.classList.remove('btn-warning');
      submitButton.classList.add('btn-success');
    }
  }

  function setEditMode(id, name, code, active) {
    if (!panelTitle || !actionInput || !siteIdInput) {
      return;
    }

    actionInput.value = 'update';
    siteIdInput.value = String(id || '');
    panelTitle.textContent = 'Scheda Sede';

    const nameInput = document.getElementById('editSiteName');
    const codeInput = document.getElementById('editSiteCode');
    const activeInput = document.getElementById('editSiteActive');

    if (nameInput) {
      nameInput.value = String(name || '');
    }
    if (codeInput) {
      codeInput.value = String(code || '');
    }
    if (activeInput) {
      activeInput.value = String(active || '1');
    }

    const submitButton = getSubmitButton();
    if (submitButton) {
      submitButton.textContent = 'Salva modifiche';
      submitButton.classList.remove('btn-success');
      submitButton.classList.add('btn-warning');
    }
  }

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
  if (addPanelBtn) {
    addPanelBtn.addEventListener('click', () => {
      hideAlert();
      setAddMode();
      setPanelVisible(true);
    });
  }

  [closePanelBtn, cancelBtn].forEach(btn => {
    if (!btn) {
      return;
    }

    btn.addEventListener('click', () => {
      setPanelVisible(false);
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

      setEditMode(id, name, code, active);
      setPanelVisible(true);
      if (panel) {
        panel.scrollIntoView({ behavior: 'smooth' });
      }
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

        ui.postForm(form.getAttribute("action"), form)
          .then(function (payload) {
            showAlert('success', payload.message || 'Sede eliminata con successo');
            if (tableEl.__dataTable) {
              tableEl.__dataTable.ajax.reload(null, false);
            }

            const currentEditId = parseInt((siteIdInput && siteIdInput.value) ? siteIdInput.value : '0', 10);
            if (currentEditId === id) {
              setPanelVisible(false);
            }
          })
          .catch(function (errorPayload) {
            const message = (errorPayload && errorPayload.message) ? errorPayload.message : 'Errore durante eliminazione sede';
            showAlert('danger', message);
          });
      }
    }
  });

  if (form && ui && typeof ui.postForm === 'function') {
    form.addEventListener('submit', async function (event) {
      event.preventDefault();
      hideAlert();

      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      const submitButton = getSubmitButton();
      if (submitButton) {
        submitButton.disabled = true;
      }

      try {
        const currentAction = actionInput ? actionInput.value : 'add';
        const payload = await ui.postForm(form.getAttribute("action"), form);
        showAlert('success', payload.message || (currentAction === 'update' ? 'Sede modificata con successo' : 'Sede creata con successo'));
        setAddMode();
        setPanelVisible(false);
        if (tableEl.__dataTable) {
          tableEl.__dataTable.ajax.reload(null, false);
        }
      } catch (errorPayload) {
        const currentAction = actionInput ? actionInput.value : 'add';
        const fallbackMessage = currentAction === 'update'
          ? 'Errore durante aggiornamento sede'
          : 'Errore durante salvataggio sede';
        const message = (errorPayload && errorPayload.message) ? errorPayload.message : fallbackMessage;
        showAlert('danger', message);
        setPanelVisible(true);
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
    setEditMode(
      <?= (int) $editPrefill['id'] ?>,
      <?= json_encode($editPrefill['name']) ?>,
      <?= json_encode($editPrefill['code']) ?>,
      <?= json_encode((string) ((int) $editPrefill['active'] === 0 ? '0' : '1')) ?>
    );
    setPanelVisible(true);
  <?php else: ?>
    setAddMode();
  <?php endif; ?>
});
</script>