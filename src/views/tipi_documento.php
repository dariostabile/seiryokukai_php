<?php

declare(strict_types=1);

/** @var array $documentTypes */

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
      <button class="btn btn-success" type="button" id="openAddDocumentTypePanel">+ Aggiungi tipo</button>
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

    <div id="addDocumentTypePanel" class="card border mt-4 <?= $openAddPanel ? '' : 'd-none' ?>">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Scheda Nuovo Tipo Documento</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeAddDocumentTypePanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" action="/seiryokukai_php/public/api/tipi_documento.php" class="row g-3" id="addDocumentTypeForm">
          <input type="hidden" name="action" value="add">

          <div class="col-12">
            <label class="form-label">Tipo Documento</label>
            <input class="form-control" name="type" placeholder="Nuovo tipo documento" required value="<?= htmlspecialchars($addPrefill['type']) ?>">
          </div>

          <div class="col-12 d-flex justify-content-end gap-2">
            <button class="btn btn-secondary" type="button" id="cancelAddDocumentTypeBtn">Annulla</button>
            <button class="btn btn-success" type="submit">+ Aggiungi Tipo</button>
          </div>
        </form>
      </div>
    </div>

    <div id="editDocumentTypePanel" class="card border mt-4 d-none">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Scheda Tipo Documento</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeEditDocumentTypePanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" action="/seiryokukai_php/public/api/tipi_documento.php" class="row g-3" id="editDocumentTypeForm">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" id="editDocumentTypeId">

          <div class="col-12">
            <label class="form-label">Tipo Documento</label>
            <input class="form-control" name="type" id="editDocumentTypeType" placeholder="Tipo documento" required>
          </div>

          <div class="col-12 d-flex justify-content-end gap-2">
            <button class="btn btn-secondary" type="button" id="cancelEditDocumentTypeBtn">Annulla</button>
            <button class="btn btn-warning" type="submit">Salva Modifiche</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const addPanelBtn = document.getElementById('openAddDocumentTypePanel');
  const addPanel = document.getElementById('addDocumentTypePanel');
  const closeAddPanelBtn = document.getElementById('closeAddDocumentTypePanelBtn');
  const cancelAddBtn = document.getElementById('cancelAddDocumentTypeBtn');

  const editPanel = document.getElementById('editDocumentTypePanel');
  const closeEditPanelBtn = document.getElementById('closeEditDocumentTypePanelBtn');
  const cancelEditBtn = document.getElementById('cancelEditDocumentTypeBtn');

  const tableEl = document.getElementById('tipi-documento-table');

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

  // DataTable per tipi documento
  if (tableEl.__dataTable) {
    tableEl.__dataTable.destroy();
  }

  tableEl.__dataTable = new DataTable(tableEl, {
    serverSide: true,
    processing: true,
    ajax: {
      url: '/seiryokukai_php/public/api/tipi_documento.php',
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
    const editBtn = e.target.closest('.edit-document-type-btn');
    if (editBtn) {
      const id = parseInt(editBtn.dataset.id);
      const type = editBtn.dataset.type;

      document.getElementById('editDocumentTypeId').value = id;
      document.getElementById('editDocumentTypeType').value = type;

      editPanel.classList.remove('d-none');
      editPanel.scrollIntoView({ behavior: 'smooth' });
    }

    const deleteBtn = e.target.closest('.delete-document-type-btn');
    if (deleteBtn) {
      if (confirm('Sei sicuro di voler eliminare questo tipo documento?')) {
        const id = parseInt(deleteBtn.dataset.id);
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/seiryokukai_php/public/api/tipi_documento.php';

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
    document.getElementById('editDocumentTypeId').value = <?= (int) $editPrefill['id'] ?>;
    document.getElementById('editDocumentTypeType').value = <?= json_encode($editPrefill['type']) ?>;
    editPanel.classList.remove('d-none');
  <?php endif; ?>
});
</script>
