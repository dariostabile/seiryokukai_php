<?php

declare(strict_types=1);

/** @var array $clients */

$frontendApi = frontend_api_urls();
$atletiApiUrl = (string) ($frontendApi['atleti'] ?? '');
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="m-0">Anagrafica Atleti</h5>
      <form method="post" action="<?= htmlspecialchars($atletiApiUrl) ?>" class="d-flex gap-2">
        <input class="form-control" name="name" placeholder="Nome e cognome atleta" required>
        <button class="btn btn-success">Aggiungi</button>
      </form>
    </div>

    <div class="table-responsive">
      <table id="atleti-table" class="table align-middle js-datatable" data-server-side="1">
        <thead>
          <tr>
            <th>ID</th>
            <th>Atleta</th>
            <th>Email</th>
            <th>Telefono</th>
            <th>Stato</th>
            <th class="text-end">Azioni</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  if (typeof DataTable === 'undefined') {
    return;
  }

  const dataTableLangUrl =
    (window.SeiryokukaiConfig && window.SeiryokukaiConfig.dataTableLangUrl)
    || '';
  const api = (window.SeiryokukaiConfig && window.SeiryokukaiConfig.api) || {};
  const atletiApiUrl = api.atleti || '';

  const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

  new DataTable('#atleti-table', {
    serverSide: true,
    processing: true,
    pageLength: 10,
    order: [[0, 'desc']],
    ajax: {
      url: atletiApiUrl,
      type: 'GET',
    },
    language: {
      url: dataTableLangUrl,
    },
    columns: [
      { data: 'id' },
      { data: 'name' },
      { data: 'email' },
      { data: 'phone' },
      {
        data: 'status',
        render: function (data) {
          const active = data === 'Attivo';
          const cls = active ? 'success' : 'secondary';
          return '<span class="badge text-bg-' + cls + '">' + escapeHtml(data) + '</span>';
        },
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-end',
        render: function (row) {
          const id = Number(row.id || 0);
          const isActive = row.status === 'Attivo';
          const nextStatus = isActive ? 'Sospeso' : 'Attivo';
          const statusLabel = isActive ? 'Sospendi' : 'Attiva';
          const statusClass = isActive ? 'btn-outline-warning' : 'btn-outline-success';

          return ''
            + '<div class="d-flex justify-content-end gap-2">'
            + '<form method="post" action="' + escapeHtml(atletiApiUrl) + '">'
            + '<input type="hidden" name="action" value="status">'
            + '<input type="hidden" name="id" value="' + id + '">'
            + '<input type="hidden" name="status" value="' + nextStatus + '">'
            + '<button class="btn btn-sm ' + statusClass + '" type="submit">' + statusLabel + '</button>'
            + '</form>'
            + '<form method="post" action="' + escapeHtml(atletiApiUrl) + '" onsubmit="return confirm(\'Eliminare questo cliente?\');">'
            + '<input type="hidden" name="action" value="delete">'
            + '<input type="hidden" name="id" value="' + id + '">'
            + '<button class="btn btn-sm btn-outline-danger" type="submit">Elimina</button>'
            + '</form>'
            + '</div>';
        },
      },
    ],
  });
});
</script>
