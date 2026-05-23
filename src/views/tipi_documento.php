<?php

declare(strict_types=1);

/** @var array $tipiDocumenti */

$frontendApi = frontend_api_urls();
$tipiDocumentiApiUrl = (string) ($frontendApi['tipi_documento'] ?? '');

$okMessage = trim((string) ($_GET['ok'] ?? ''));
$errMessage = trim((string) ($_GET['err'] ?? ''));
$addPrefill = [
  'type' => trim((string) ($_GET['add_type'] ?? '')),
];
$openAddPanel = $addPrefill['type'] !== '';

$openEdit = ((string) ($_GET['open_edit'] ?? '0')) === '1';
$editPrefill = [
  'id' => (int) ($_GET['edit_id'] ?? 0),
  'type' => trim((string) ($_GET['edit_type'] ?? '')),
];
$openTipoDocumentoPanel = $openAddPanel || ($openEdit && $editPrefill['id'] > 0);
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
      <h5 class="m-0">Gestione Tipi Documento</h5>
      <button class="btn btn-success" type="button" id="openAddTipoDocumentoPanel">+ Aggiungi Tipo</button>
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

    <div id="tipiDocumentoAjaxAlert" class="alert d-none" role="alert"></div>

    <div class="table-responsive">
      <table id="tipi-documento-table" class="table align-middle js-datatable table-hover" data-server-side="1">
        <thead>
          <tr>
            <th>Tipo Documento</th>
            <th class="text-end">Azioni</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <div id="tipoDocumentoPanel" class="card border mt-4 <?= $openTipoDocumentoPanel ? '' : 'd-none' ?>">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0" id="tipoDocumentoPanelTitle">Scheda Nuovo Tipo Documento</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeTipoDocumentoPanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($tipiDocumentiApiUrl) ?>" class="row g-3" id="tipoDocumentoForm">
          <input type="hidden" name="action" id="tipoDocumentoAction" value="add">
          <input type="hidden" name="id" id="tipoDocumentoId" value="">
          <?php
          $tipoDocumentoFormValue = $openEdit && $editPrefill['id'] > 0
            ? $editPrefill['type']
            : $addPrefill['type'];
          $tipoDocumentoFormIsEdit = $openEdit && $editPrefill['id'] > 0;
          $tipoDocumentoFieldId = 'tipoDocumentoTypeInput';
          $tipoDocumentoCancelButtonId = 'cancelTipoDocumentoBtn';
          $tipoDocumentoSubmitLabel = $tipoDocumentoFormIsEdit ? 'Salva modifiche' : '+ Aggiungi Tipo';
          $tipoDocumentoSubmitClass = $tipoDocumentoFormIsEdit ? 'btn-warning' : 'btn-success';
          require __DIR__ . '/partials/tipo_documento_form_fields.php';
          ?>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const ui = window.SeiryokukaiUi || null;
  const addPanelBtn = document.getElementById('openAddTipoDocumentoPanel');
  const panel = document.getElementById('tipoDocumentoPanel');
  const form = document.getElementById('tipoDocumentoForm');
  const closePanelBtn = document.getElementById('closeTipoDocumentoPanelBtn');
  const cancelBtn = document.getElementById('cancelTipoDocumentoBtn');
  const panelTitle = document.getElementById('tipoDocumentoPanelTitle');
  const actionInput = document.getElementById('tipoDocumentoAction');
  const idInput = document.getElementById('tipoDocumentoId');
  const typeInput = document.getElementById('tipoDocumentoTypeInput');

  const tableEl = document.getElementById('tipi-documento-table');
  const ajaxAlert = document.getElementById('tipiDocumentoAjaxAlert');

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
    if (!actionInput || !idInput || !panelTitle || !typeInput || !form) {
      return;
    }

    actionInput.value = 'add';
    idInput.value = '';
    panelTitle.textContent = 'Scheda Nuovo Tipo Documento';
    typeInput.value = '';

    const submitButton = getSubmitButton();
    if (submitButton) {
      submitButton.textContent = '+ Aggiungi Tipo';
      submitButton.classList.remove('btn-warning');
      submitButton.classList.add('btn-success');
    }
  }

  function setEditMode(id, type) {
    if (!actionInput || !idInput || !panelTitle || !typeInput) {
      return;
    }

    actionInput.value = 'update';
    idInput.value = String(id || '');
    panelTitle.textContent = 'Scheda Tipo Documento';
    typeInput.value = String(type || '');

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
  const tipiDocumentiApiUrl = api.tipi_documento || '';

  // DataTable per tipi documento
  if (tableEl.__dataTable) {
    tableEl.__dataTable.destroy();
  }

  tableEl.__dataTable = new DataTable(tableEl, {
    serverSide: true,
    processing: true,
    ajax: {
      url: tipiDocumentiApiUrl,
      type: 'GET'
    },
    columns: [
      { data: 'type' },
      {
        data: 'id',
        orderable: false,
        render: function (data, type, row) {
          return `
            <div class="text-end">
              <button class="btn btn-sm btn-primary edit-document-type-btn" data-id="${data}" data-type="${htmlEscape(row.type)}">Modifica</button>
              <button class="btn btn-sm btn-danger delete-document-type-btn" data-id="${data}">Elimina</button>
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

  // Event delegation per bottoni
  tableEl.addEventListener('click', (e) => {
    const editBtn = e.target.closest('.edit-document-type-btn');
    if (editBtn) {
      hideAlert();
      const id = parseInt(editBtn.dataset.id);
      const type = editBtn.dataset.type;

      setEditMode(id, type);
      setPanelVisible(true);
      if (panel) {
        panel.scrollIntoView({ behavior: 'smooth' });
      }
    }

    const deleteBtn = e.target.closest('.delete-document-type-btn');
    if (deleteBtn) {
      if (!ui || typeof ui.postForm !== 'function') {
        return;
      }

      if (confirm('Sei sicuro di voler eliminare questo tipo documento?')) {
        const id = parseInt(deleteBtn.dataset.id, 10);
        if (!id || id <= 0) {
          showAlert('danger', 'ID tipo documento non valido');
          return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = tipiDocumentiApiUrl;

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
            showAlert('success', payload.message || 'Tipo documento eliminato con successo');
            if (tableEl.__dataTable) {
              tableEl.__dataTable.ajax.reload(null, false);
            }

            const currentEditId = parseInt((idInput && idInput.value) ? idInput.value : '0', 10);
            if (currentEditId === id) {
              setPanelVisible(false);
            }
          })
          .catch(function (errorPayload) {
            const message = (errorPayload && errorPayload.message) ? errorPayload.message : 'Errore durante eliminazione tipo documento';
            showAlert('danger', message);
          });
      }
    }
  });

  if (form && ui && typeof ui.postForm === 'function') {
    form.addEventListener('submit', async function (event) {
      event.preventDefault();
      hideAlert();

      const submitButton = getSubmitButton();
      if (submitButton) {
        submitButton.disabled = true;
      }

      try {
        const currentAction = actionInput ? actionInput.value : 'add';
        const payload = await ui.postForm(form.action, form);
        showAlert('success', payload.message || (currentAction === 'update' ? 'Tipo documento modificato con successo' : 'Tipo documento creato con successo'));
        setAddMode();
        setPanelVisible(false);
        if (tableEl.__dataTable) {
          tableEl.__dataTable.ajax.reload(null, false);
        }
      } catch (errorPayload) {
        const currentAction = actionInput ? actionInput.value : 'add';
        const fallbackMessage = currentAction === 'update'
          ? 'Errore durante aggiornamento tipo documento'
          : 'Errore durante salvataggio tipo documento';
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

  <?php if ($openEdit && $editPrefill['id'] > 0): ?>
    setEditMode(<?= (int) $editPrefill['id'] ?>, <?= json_encode($editPrefill['type']) ?>);
    setPanelVisible(true);
  <?php else: ?>
    setAddMode();
  <?php endif; ?>
});
</script>
