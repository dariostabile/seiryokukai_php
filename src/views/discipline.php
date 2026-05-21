<?php

declare(strict_types=1);

/** @var array $discipline */

$frontendApi = frontend_api_urls();
$disciplinaApiUrl = (string) ($frontendApi['disciplina'] ?? '');

$okMessage = trim((string) ($_GET['ok'] ?? ''));
$errMessage = trim((string) ($_GET['err'] ?? ''));
$addPrefill = [
  'name' => trim((string) ($_GET['add_name'] ?? '')),
  'notes' => trim((string) ($_GET['add_notes'] ?? '')),
];
$openAddPanel = $addPrefill['name'] !== '' || $addPrefill['notes'] !== '';

$openEdit = ((string) ($_GET['open_edit'] ?? '0')) === '1';
$editPrefill = [
  'id' => (int) ($_GET['edit_id'] ?? 0),
  'name' => trim((string) ($_GET['edit_name'] ?? '')),
  'notes' => trim((string) ($_GET['edit_notes'] ?? '')),
];
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
      <h5 class="m-0">Gestione Discipline</h5>
      <button class="btn btn-success" type="button" id="openAddDisciplinaPanel">+ Aggiungi disciplina</button>
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

    <div id="disciplinaAjaxAlert" class="alert d-none" role="alert"></div>

    <div class="table-responsive">
      <table id="disciplina-table" class="table align-middle js-datatable table-hover" data-server-side="1">
        <thead>
          <tr>
            <th>Disciplina</th>
            <th>Note</th>
            <th class="text-end">Azioni</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <div id="addDisciplinaPanel" class="card border mt-4 <?= $openAddPanel ? '' : 'd-none' ?>">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Scheda Nuova Disciplina</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeAddDisciplinaPanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($disciplinaApiUrl) ?>" class="row g-3" id="addDisciplinaForm">
          <input type="hidden" name="action" value="add">

          <div class="col-12">
            <label class="form-label">Nome Disciplina</label>
            <input class="form-control" name="name" placeholder="Nome della disciplina" required value="<?= htmlspecialchars($addPrefill['name']) ?>">
          </div>

          <div class="col-12">
            <label class="form-label">Note</label>
            <textarea class="form-control" name="notes" placeholder="Note (opzionale)" rows="3"><?= htmlspecialchars($addPrefill['notes']) ?></textarea>
          </div>

          <div class="col-12 d-flex justify-content-end gap-2">
            <button class="btn btn-secondary" type="button" id="cancelAddDisciplinaBtn">Annulla</button>
            <button class="btn btn-success" type="submit">+ Aggiungi Disciplina</button>
          </div>
        </form>
      </div>
    </div>

    <div id="editDisciplinaPanel" class="card border mt-4 d-none">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Scheda Disciplina</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeEditDisciplinaPanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($disciplinaApiUrl) ?>" class="row g-3" id="editDisciplinaForm">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" id="editDisciplinaId">

          <div class="col-12">
            <label class="form-label">Nome Disciplina</label>
            <input class="form-control" name="name" id="editDisciplinaName" placeholder="Nome della disciplina" required>
          </div>

          <div class="col-12">
            <label class="form-label">Note</label>
            <textarea class="form-control" name="notes" id="editDisciplinaNotes" placeholder="Note (opzionale)" rows="3"></textarea>
          </div>

          <div class="col-12 d-flex justify-content-end gap-2">
            <button class="btn btn-secondary" type="button" id="cancelEditDisciplinaBtn">Annulla</button>
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
  const addPanelBtn = document.getElementById('openAddDisciplinaPanel');
  const addPanel = document.getElementById('addDisciplinaPanel');
  const addForm = document.getElementById('addDisciplinaForm');
  const closeAddPanelBtn = document.getElementById('closeAddDisciplinaPanelBtn');
  const cancelAddBtn = document.getElementById('cancelAddDisciplinaBtn');

  const editPanel = document.getElementById('editDisciplinaPanel');
  const editForm = document.getElementById('editDisciplinaForm');
  const closeEditPanelBtn = document.getElementById('closeEditDisciplinaPanelBtn');
  const cancelEditBtn = document.getElementById('cancelEditDisciplinaBtn');

  const tableEl = document.getElementById('disciplina-table');
  const ajaxAlert = document.getElementById('disciplinaAjaxAlert');

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
  const disciplinaApiUrl = api.disciplina || '';

  // DataTable per discipline
  if (tableEl.__dataTable) {
    tableEl.__dataTable.destroy();
  }

  tableEl.__dataTable = new DataTable(tableEl, {
    serverSide: true,
    processing: true,
    ajax: {
      url: disciplinaApiUrl,
      type: 'GET'
    },
    columns: [
      { data: 'id', visible: false, searchable: false },
      { data: 'name' },
      { data: 'notes' },
      {
        data: 'id',
        orderable: false,
        render: function (data, type, row) {
          return `
            <div class="text-end">
              <button class="btn btn-sm btn-primary edit-disciplina-btn" data-id="${data}" data-name="${htmlEscape(row.name)}" data-notes="${htmlEscape(row.notes)}">Modifica</button>
              <button class="btn btn-sm btn-danger delete-disciplina-btn" data-id="${data}">Elimina</button>
            </div>
          `;
        }
      }
    ],
    order: [[0, 'desc']],
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
    const editBtn = e.target.closest('.edit-disciplina-btn');
    if (editBtn) {
      hideAlert();
      const id = parseInt(editBtn.dataset.id);
      const name = editBtn.dataset.name;
      const notes = editBtn.dataset.notes;

      document.getElementById('editDisciplinaId').value = id;
      document.getElementById('editDisciplinaName').value = name;
      document.getElementById('editDisciplinaNotes').value = notes;

      editPanel.classList.remove('d-none');
      editPanel.scrollIntoView({ behavior: 'smooth' });
    }

    const deleteBtn = e.target.closest('.delete-disciplina-btn');
    if (deleteBtn) {
      if (!ui || typeof ui.postForm !== 'function') {
        return;
      }

      if (confirm('Sei sicuro di voler eliminare questa disciplina?')) {
        const id = parseInt(deleteBtn.dataset.id, 10);
        if (!id || id <= 0) {
          showAlert('danger', 'ID disciplina non valido');
          return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = disciplinaApiUrl;

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
            showAlert('success', payload.message || 'Disciplina eliminata con successo');
            if (tableEl.__dataTable) {
              tableEl.__dataTable.ajax.reload(null, false);
            }

            const currentEditId = parseInt(document.getElementById('editDisciplinaId').value || '0', 10);
            if (currentEditId === id) {
              editPanel.classList.add('d-none');
            }
          })
          .catch(function (errorPayload) {
            const message = (errorPayload && errorPayload.message) ? errorPayload.message : 'Errore durante eliminazione disciplina';
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
        showAlert('success', payload.message || 'Disciplina creata con successo');
        addForm.reset();
        addPanel.classList.add('d-none');
        if (tableEl.__dataTable) {
          tableEl.__dataTable.ajax.reload(null, false);
        }
      } catch (errorPayload) {
        const message = (errorPayload && errorPayload.message) ? errorPayload.message : 'Errore durante salvataggio disciplina';
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
        showAlert('success', payload.message || 'Disciplina modificata con successo');
        editPanel.classList.add('d-none');
        if (tableEl.__dataTable) {
          tableEl.__dataTable.ajax.reload(null, false);
        }
      } catch (errorPayload) {
        const message = (errorPayload && errorPayload.message) ? errorPayload.message : 'Errore durante aggiornamento disciplina';
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
    document.getElementById('editDisciplinaId').value = <?= (int) $editPrefill['id'] ?>;
    document.getElementById('editDisciplinaName').value = <?= json_encode($editPrefill['name']) ?>;
    document.getElementById('editDisciplinaNotes').value = <?= json_encode($editPrefill['notes']) ?>;
    editPanel.classList.remove('d-none');
  <?php endif; ?>
});
</script>
