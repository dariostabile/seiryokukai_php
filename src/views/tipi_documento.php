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
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
      <h5 class="m-0">Gestione Tipi Documento</h5>
      <button class="btn btn-success" type="button" id="openAddTipoDocumentoPanel">+ Aggiungi tipo</button>
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

    <div id="addTipoDocumentoPanel" class="card border mt-4 <?= $openAddPanel ? '' : 'd-none' ?>">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Scheda Nuovo Tipo Documento</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeAddTipoDocumentoPanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($tipiDocumentiApiUrl) ?>" class="row g-3" id="addTipoDocumentoForm">
          <input type="hidden" name="action" value="add">

          <div class="col-12">
            <label class="form-label">Tipo Documento</label>
            <input class="form-control" name="type" placeholder="Nuovo tipo documento" required value="<?= htmlspecialchars($addPrefill['type']) ?>">
          </div>

          <div class="col-12 d-flex justify-content-end gap-2">
            <button class="btn btn-secondary" type="button" id="cancelAddTipoDocumentoBtn">Annulla</button>
            <button class="btn btn-success" type="submit">+ Aggiungi Tipo</button>
          </div>
        </form>
      </div>
    </div>

    <div id="editTipoDocumentoPanel" class="card border mt-4 d-none">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Scheda Tipo Documento</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeEditTipoDocumentoPanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($tipiDocumentiApiUrl) ?>" class="row g-3" id="editTipoDocumentoForm">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" id="editTipoDocumentoId">

          <div class="col-12">
            <label class="form-label">Tipo Documento</label>
            <input class="form-control" name="type" id="editTipoDocumentoType" placeholder="Tipo documento" required>
          </div>

          <div class="col-12 d-flex justify-content-end gap-2">
            <button class="btn btn-secondary" type="button" id="cancelEditTipoDocumentoBtn">Annulla</button>
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
  const addPanelBtn = document.getElementById('openAddTipoDocumentoPanel');
  const addPanel = document.getElementById('addTipoDocumentoPanel');
  const addForm = document.getElementById('addTipoDocumentoForm');
  const closeAddPanelBtn = document.getElementById('closeAddTipoDocumentoPanelBtn');
  const cancelAddBtn = document.getElementById('cancelAddTipoDocumentoBtn');

  const editPanel = document.getElementById('editTipoDocumentoPanel');
  const editForm = document.getElementById('editTipoDocumentoForm');
  const closeEditPanelBtn = document.getElementById('closeEditTipoDocumentoPanelBtn');
  const cancelEditBtn = document.getElementById('cancelEditTipoDocumentoBtn');

  const tableEl = document.getElementById('tipi-documento-table');
  const ajaxAlert = document.getElementById('tipiDocumentoAjaxAlert');

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

      document.getElementById('editTipoDocumentoId').value = id;
      document.getElementById('editTipoDocumentoType').value = type;

      editPanel.classList.remove('d-none');
      editPanel.scrollIntoView({ behavior: 'smooth' });
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

            const currentEditId = parseInt(document.getElementById('editTipoDocumentoId').value || '0', 10);
            if (currentEditId === id) {
              editPanel.classList.add('d-none');
            }
          })
          .catch(function (errorPayload) {
            const message = (errorPayload && errorPayload.message) ? errorPayload.message : 'Errore durante eliminazione tipo documento';
            showAlert('danger', message);
          });
      }
    }
  });

  if (addForm && ui && typeof ui.postForm === 'function') {
    addForm.addEventListener('submit', async function (event) {
      event.preventDefault();
      hideAlert();

      const submitButton = addForm.querySelector('button[type="submit"]');
      if (submitButton) {
        submitButton.disabled = true;
      }

      try {
        const payload = await ui.postForm(addForm.action, addForm);
        showAlert('success', payload.message || 'Tipo documento creato con successo');
        addForm.reset();
        addPanel.classList.add('d-none');
        if (tableEl.__dataTable) {
          tableEl.__dataTable.ajax.reload(null, false);
        }
      } catch (errorPayload) {
        const message = (errorPayload && errorPayload.message) ? errorPayload.message : 'Errore durante salvataggio tipo documento';
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

      const submitButton = editForm.querySelector('button[type="submit"]');
      if (submitButton) {
        submitButton.disabled = true;
      }

      try {
        const payload = await ui.postForm(editForm.action, editForm);
        showAlert('success', payload.message || 'Tipo documento modificato con successo');
        editPanel.classList.add('d-none');
        if (tableEl.__dataTable) {
          tableEl.__dataTable.ajax.reload(null, false);
        }
      } catch (errorPayload) {
        const message = (errorPayload && errorPayload.message) ? errorPayload.message : 'Errore durante aggiornamento tipo documento';
        showAlert('danger', message);
        editPanel.classList.remove('d-none');
      } finally {
        if (submitButton) {
          submitButton.disabled = false;
        }
      }
    });
  }

  <?php if ($openEdit && $editPrefill['id'] > 0): ?>
    document.getElementById('editTipoDocumentoId').value = <?= (int) $editPrefill['id'] ?>;
    document.getElementById('editTipoDocumentoType').value = <?= json_encode($editPrefill['type']) ?>;
    editPanel.classList.remove('d-none');
  <?php endif; ?>
});
</script>
