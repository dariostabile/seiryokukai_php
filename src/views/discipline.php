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
$openDisciplinaPanel = $openAddPanel || ($openEdit && $editPrefill['id'] > 0);
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
      <h5 class="m-0">Gestione Discipline</h5>
      <button class="btn btn-success" type="button" id="openAddDisciplinaPanel">+ Aggiungi Disciplina</button>
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

    <div id="disciplinaPanel" class="card border mt-4 <?= $openDisciplinaPanel ? '' : 'd-none' ?>">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0" id="disciplinaPanelTitle">Scheda Nuova Disciplina</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeDisciplinaPanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($disciplinaApiUrl) ?>" class="row g-3" id="disciplinaForm">
          <input type="hidden" name="form_action" id="disciplinaAction" value="add">
          <input type="hidden" name="id" id="disciplinaId" value="">
          <?php
          $disciplinaFormValues = $openEdit && $editPrefill['id'] > 0
              ? $editPrefill
              : $addPrefill;
          $disciplinaFormIsEdit = $openEdit && $editPrefill['id'] > 0;
          $disciplinaFieldIds = [
              'name' => 'editDisciplinaName',
              'notes' => 'editDisciplinaNotes',
          ];
          $disciplinaCancelButtonId = 'cancelDisciplinaBtn';
          $disciplinaSubmitLabel = $disciplinaFormIsEdit ? 'Salva modifiche' : '+ Aggiungi Disciplina';
          $disciplinaSubmitClass = $disciplinaFormIsEdit ? 'btn-warning' : 'btn-success';
          require __DIR__ . '/partials/disciplina_form_fields.php';
          ?>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const ui = window.SeiryokukaiUi || null;
  const addPanelBtn = document.getElementById('openAddDisciplinaPanel');
  const panel = document.getElementById('disciplinaPanel');
  const form = document.getElementById('disciplinaForm');
  const closePanelBtn = document.getElementById('closeDisciplinaPanelBtn');
  const cancelBtn = document.getElementById('cancelDisciplinaBtn');
  const panelTitle = document.getElementById('disciplinaPanelTitle');
  const actionInput = document.getElementById('disciplinaAction');
  const idInput = document.getElementById('disciplinaId');

  const tableEl = document.getElementById('disciplina-table');
  const ajaxAlert = document.getElementById('disciplinaAjaxAlert');

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
    if (!form || !panelTitle || !actionInput || !idInput) {
      return;
    }

    actionInput.value = 'add';
    idInput.value = '';
    panelTitle.textContent = 'Scheda Nuova Disciplina';
    form.reset();

    const submitButton = getSubmitButton();
    if (submitButton) {
      submitButton.textContent = '+ Aggiungi Disciplina';
      submitButton.classList.remove('btn-warning');
      submitButton.classList.add('btn-success');
    }
  }

  function setEditMode(id, name, notes) {
    if (!panelTitle || !actionInput || !idInput) {
      return;
    }

    actionInput.value = 'update';
    idInput.value = String(id || '');
    panelTitle.textContent = 'Scheda Disciplina';

    const nameInput = document.getElementById('editDisciplinaName');
    const notesInput = document.getElementById('editDisciplinaNotes');
    if (nameInput) {
      nameInput.value = String(name || '');
    }
    if (notesInput) {
      notesInput.value = String(notes || '');
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

      setEditMode(id, name, notes);
      setPanelVisible(true);
      if (panel) {
        panel.scrollIntoView({ behavior: 'smooth' });
      }
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

        ui.postForm(form.getAttribute("action"), form)
          .then(function (payload) {
            showAlert('success', payload.message || 'Disciplina eliminata con successo');
            if (tableEl.__dataTable) {
              tableEl.__dataTable.ajax.reload(null, false);
            }

            const currentEditId = parseInt((idInput && idInput.value) ? idInput.value : '0', 10);
            if (currentEditId === id) {
              setPanelVisible(false);
            }
          })
          .catch(function (errorPayload) {
            const message = (errorPayload && errorPayload.message) ? errorPayload.message : 'Errore durante eliminazione disciplina';
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
        const payload = await ui.postForm(form.getAttribute("action"), form);
        showAlert('success', payload.message || (currentAction === 'update' ? 'Disciplina modificata con successo' : 'Disciplina creata con successo'));
        setAddMode();
        setPanelVisible(false);
        if (tableEl.__dataTable) {
          tableEl.__dataTable.ajax.reload(null, false);
        }
      } catch (errorPayload) {
        const currentAction = actionInput ? actionInput.value : 'add';
        const fallbackMessage = currentAction === 'update'
          ? 'Errore durante aggiornamento disciplina'
          : 'Errore durante salvataggio disciplina';
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
    setEditMode(
      <?= (int) $editPrefill['id'] ?>,
      <?= json_encode($editPrefill['name']) ?>,
      <?= json_encode($editPrefill['notes']) ?>
    );
    setPanelVisible(true);
  <?php else: ?>
    setAddMode();
  <?php endif; ?>
});
</script>