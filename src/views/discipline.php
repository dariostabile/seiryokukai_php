<?php

declare(strict_types=1);

/** @var array $disciplines */

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
      <button class="btn btn-success" type="button" id="openAddDisciplinePanel">+ Aggiungi disciplina</button>
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
      <table id="discipline-table" class="table align-middle js-datatable table-hover" data-server-side="1">
        <thead>
          <tr>
            <th>ID</th>
            <th>Disciplina</th>
            <th>Note</th>
            <th class="text-end">Azioni</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <div id="addDisciplinePanel" class="card border mt-4 <?= $openAddPanel ? '' : 'd-none' ?>">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Scheda Nuova Disciplina</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeAddDisciplinePanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" action="/seiryokukai_php/public/api/disciplina.php" class="row g-3" id="addDisciplineForm">
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
            <button class="btn btn-secondary" type="button" id="cancelAddDisciplineBtn">Annulla</button>
            <button class="btn btn-success" type="submit">+ Aggiungi Disciplina</button>
          </div>
        </form>
      </div>
    </div>

    <div id="editDisciplinePanel" class="card border mt-4 d-none">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Scheda Disciplina</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeEditDisciplinePanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" action="/seiryokukai_php/public/api/disciplina.php" class="row g-3" id="editDisciplineForm">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" id="editDisciplineId">

          <div class="col-12">
            <label class="form-label">Nome Disciplina</label>
            <input class="form-control" name="name" id="editDisciplineName" placeholder="Nome della disciplina" required>
          </div>

          <div class="col-12">
            <label class="form-label">Note</label>
            <textarea class="form-control" name="notes" id="editDisciplineNotes" placeholder="Note (opzionale)" rows="3"></textarea>
          </div>

          <div class="col-12 d-flex justify-content-end gap-2">
            <button class="btn btn-secondary" type="button" id="cancelEditDisciplineBtn">Annulla</button>
            <button class="btn btn-warning" type="submit">Salva Modifiche</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const addPanelBtn = document.getElementById('openAddDisciplinePanel');
  const addPanel = document.getElementById('addDisciplinePanel');
  const closeAddPanelBtn = document.getElementById('closeAddDisciplinePanelBtn');
  const cancelAddBtn = document.getElementById('cancelAddDisciplineBtn');

  const editPanel = document.getElementById('editDisciplinePanel');
  const closeEditPanelBtn = document.getElementById('closeEditDisciplinePanelBtn');
  const cancelEditBtn = document.getElementById('cancelEditDisciplineBtn');

  const tableEl = document.getElementById('discipline-table');

  // Apri panel aggiunta
  addPanelBtn.addEventListener('click', () => {
    addPanel.classList.remove('d-none');
  });

  // Chiudi panel aggiunta
  [closeAddPanelBtn, cancelAddBtn].forEach(btn => {
    btn.addEventListener('click', () => {
      addPanel.classList.add('d-none');
    });
  });

  // Chiudi panel modifica
  [closeEditPanelBtn, cancelEditBtn].forEach(btn => {
    btn.addEventListener('click', () => {
      editPanel.classList.add('d-none');
    });
  });

  if (!tableEl || typeof DataTable === 'undefined') {
    return;
  }

  // DataTable per discipline
  if (tableEl.__dataTable) {
    tableEl.__dataTable.destroy();
  }

  tableEl.__dataTable = new DataTable(tableEl, {
    serverSide: true,
    processing: true,
    ajax: {
      url: '/seiryokukai_php/public/api/disciplina.php',
      type: 'GET'
    },
    columns: [
      { data: 'id' },
      { data: 'name' },
      { data: 'notes' },
      {
        data: 'id',
        orderable: false,
        render: function (data, type, row) {
          return `
            <div class="text-end">
              <button class="btn btn-sm btn-primary edit-discipline-btn" data-id="${data}" data-name="${htmlEscape(row.name)}" data-notes="${htmlEscape(row.notes)}">Modifica</button>
              <button class="btn btn-sm btn-danger delete-discipline-btn" data-id="${data}">Elimina</button>
            </div>
          `;
        }
      }
    ],
    order: [[0, 'desc']],
    pageLength: 10,
    language: {
      url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/it-IT.json'
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
    const editBtn = e.target.closest('.edit-discipline-btn');
    if (editBtn) {
      const id = parseInt(editBtn.dataset.id);
      const name = editBtn.dataset.name;
      const notes = editBtn.dataset.notes;

      document.getElementById('editDisciplineId').value = id;
      document.getElementById('editDisciplineName').value = name;
      document.getElementById('editDisciplineNotes').value = notes;

      editPanel.classList.remove('d-none');
      editPanel.scrollIntoView({ behavior: 'smooth' });
    }

    const deleteBtn = e.target.closest('.delete-discipline-btn');
    if (deleteBtn) {
      if (confirm('Sei sicuro di voler eliminare questa disciplina?')) {
        const id = parseInt(deleteBtn.dataset.id);
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/seiryokukai_php/public/api/disciplina.php';

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
        document.body.appendChild(form);
        form.submit();
      }
    }
  });

  <?php if ($openEdit && $editPrefill['id'] > 0): ?>
    document.getElementById('editDisciplineId').value = <?= (int) $editPrefill['id'] ?>;
    document.getElementById('editDisciplineName').value = <?= json_encode($editPrefill['name']) ?>;
    document.getElementById('editDisciplineNotes').value = <?= json_encode($editPrefill['notes']) ?>;
    editPanel.classList.remove('d-none');
  <?php endif; ?>
});
</script>

