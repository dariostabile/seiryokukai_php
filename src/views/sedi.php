<?php

declare(strict_types=1);

/** @var array $sites */

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

    <div id="addSitePanel" class="card border mt-4 <?= $openAddPanel ? '' : 'd-none' ?>">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Scheda Nuova Sede</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeAddSitePanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" action="/seiryokukai_php/public/api/sedi.php" class="row g-3" id="addSiteForm">
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
        <form method="post" action="/seiryokukai_php/public/api/sedi.php" class="row g-3" id="editSiteForm">
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
  const addPanelBtn = document.getElementById('openAddSitePanel');
  const addPanel = document.getElementById('addSitePanel');
  const closeAddPanelBtn = document.getElementById('closeAddSitePanelBtn');
  const cancelAddBtn = document.getElementById('cancelAddSiteBtn');
  const addForm = document.getElementById('addSiteForm');

  const editPanel = document.getElementById('editSitePanel');
  const closeEditPanelBtn = document.getElementById('closeEditSitePanelBtn');
  const cancelEditBtn = document.getElementById('cancelEditSiteBtn');
  const editForm = document.getElementById('editSiteForm');

  const tableEl = document.getElementById('sedi-table');
  const onlyActiveCheckbox = document.getElementById('sediOnlyActive');

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

  // DataTable per sedi
  if (tableEl.__dataTable) {
    tableEl.__dataTable.destroy();
  }

  tableEl.__dataTable = new DataTable(tableEl, {
    serverSide: true,
    processing: true,
    ajax: {
      url: '/seiryokukai_php/public/api/sedi.php',
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
      url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/it-IT.json'
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
      if (confirm('Sei sicuro di voler eliminare questa sede?')) {
        const id = parseInt(deleteBtn.dataset.id);
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/seiryokukai_php/public/api/sedi.php';

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

