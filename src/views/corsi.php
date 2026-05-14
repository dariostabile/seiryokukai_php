<?php

declare(strict_types=1);

/** @var array $courses */
/** @var array $sites */
/** @var array $disciplines */
/** @var array $users */

$dayLabels = [
    'lun' => 'Lunedì',
    'mar' => 'Martedì',
  'mer' => 'Mercoledì',
  'gio' => 'Giovedì',
    'ven' => 'Venerdì',
    'sab' => 'Sabato',
    'dom' => 'Domenica',
];
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="m-0">Gestione Corsi</h5>
    </div>

    <form method="post" action="/seiryokukai_php/public/api/corsi.php" class="row g-2 mb-4">
      <div class="col-12 col-md-3">
        <input class="form-control" name="name" placeholder="Nome corso" required>
      </div>
      <div class="col-12 col-md-2">
        <select class="form-select" name="site_id" required>
          <option value="">Sede</option>
          <?php foreach ($sites as $site): ?>
            <option value="<?= (int) ($site['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($site['name'] ?? '')) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-2">
        <select class="form-select" name="discipline_id" required>
          <option value="">Disciplina</option>
          <?php foreach ($disciplines as $discipline): ?>
            <option value="<?= (int) ($discipline['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($discipline['name'] ?? '')) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-2">
        <select class="form-select" name="user_id" required>
          <option value="">Istruttore</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= (int) ($u['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($u['name'] ?? '')) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-1">
        <input class="form-control" type="date" name="start_date">
      </div>
      <div class="col-12 col-md-2">
        <input class="form-control" type="number" name="monthly_fee" step="0.01" placeholder="Quota">
      </div>

      <div class="col-12">
        <small class="text-muted">Orari settimanali:</small>
      </div>

      <?php foreach ($dayLabels as $dayKey => $dayLabel): ?>
        <div class="col-12 col-md-2">
          <label class="form-label mb-1"><?= htmlspecialchars($dayLabel) ?></label>
        </div>
        <div class="col-6 col-md-2">
          <input type="time" class="form-control" name="<?= htmlspecialchars($dayKey) ?>_inizio" aria-label="<?= htmlspecialchars($dayLabel) ?> inizio">
        </div>
        <div class="col-6 col-md-2">
          <input type="time" class="form-control" name="<?= htmlspecialchars($dayKey) ?>_fine" aria-label="<?= htmlspecialchars($dayLabel) ?> fine">
        </div>
      <?php endforeach; ?>

      <div class="col-12 mt-2">
        <button class="btn btn-success" type="submit">+ Aggiungi Corso</button>
      </div>
    </form>

    <div class="table-responsive">
      <table id="corsi-table" class="table align-middle js-datatable" data-server-side="1">
        <thead>
          <tr>
            <th>ID</th>
            <th>Corso</th>
            <th>Sede</th>
            <th>Disciplina</th>
            <th>Istruttore</th>
            <th>Inizio</th>
            <th>Quota</th>
            <th>Orari</th>
            <th>Azioni</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="editCourseModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modifica Corso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" action="/seiryokukai_php/public/api/corsi.php">
        <div class="modal-body">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="course_id" id="editCourseId">

          <div class="row g-2">
            <div class="col-12 col-md-6">
              <label class="form-label">Nome Corso</label>
              <input type="text" class="form-control" name="name" id="editCourseName" required>
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label">Sede</label>
              <select class="form-select" name="site_id" id="editCourseSite" required>
                <option value="">Seleziona sede</option>
                <?php foreach ($sites as $site): ?>
                  <option value="<?= (int) ($site['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($site['name'] ?? '')) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label">Disciplina</label>
              <select class="form-select" name="discipline_id" id="editCourseDiscipline" required>
                <option value="">Seleziona disciplina</option>
                <?php foreach ($disciplines as $discipline): ?>
                  <option value="<?= (int) ($discipline['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($discipline['name'] ?? '')) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label">Istruttore</label>
              <select class="form-select" name="user_id" id="editCourseUser" required>
                <option value="">Seleziona istruttore</option>
                <?php foreach ($users as $u): ?>
                  <option value="<?= (int) ($u['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($u['name'] ?? '')) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label">Data Inizio</label>
              <input type="date" class="form-control" name="start_date" id="editCourseStartDate">
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label">Quota Mensile</label>
              <input type="number" class="form-control" name="monthly_fee" id="editCourseMonthlyFee" step="0.01">
            </div>

            <?php foreach ($dayLabels as $dayKey => $dayLabel): ?>
              <div class="col-12 col-md-2">
                <label class="form-label mb-1"><?= htmlspecialchars($dayLabel) ?></label>
              </div>
              <div class="col-6 col-md-2">
                <input type="time" class="form-control" name="<?= htmlspecialchars($dayKey) ?>_inizio" id="edit_<?= htmlspecialchars($dayKey) ?>_inizio">
              </div>
              <div class="col-6 col-md-2">
                <input type="time" class="form-control" name="<?= htmlspecialchars($dayKey) ?>_fine" id="edit_<?= htmlspecialchars($dayKey) ?>_fine">
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
          <button type="submit" class="btn btn-primary">Salva</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function loadCourseData(courseId, courseData) {
  document.getElementById('editCourseId').value = courseId;
  document.getElementById('editCourseName').value = courseData.name || '';
  document.getElementById('editCourseSite').value = courseData.site_id || '';
  document.getElementById('editCourseDiscipline').value = courseData.discipline_id || '';
  document.getElementById('editCourseUser').value = courseData.user_id || '';
  document.getElementById('editCourseStartDate').value = courseData.start_date || '';
  document.getElementById('editCourseMonthlyFee').value = courseData.monthly_fee || '';

  const days = ['lun', 'mar', 'mer', 'gio', 'ven', 'sab', 'dom'];
  days.forEach(day => {
    const start = document.getElementById('edit_' + day + '_inizio');
    const end = document.getElementById('edit_' + day + '_fine');
    if (start) {
      start.value = (courseData[day + '_inizio'] || '').toString().substring(0, 5);
    }
    if (end) {
      end.value = (courseData[day + '_fine'] || '').toString().substring(0, 5);
    }
  });
}

document.addEventListener('DOMContentLoaded', function () {
  if (typeof DataTable === 'undefined') {
    return;
  }

  const dayLabels = {
    lun: 'Lun',
    mar: 'Mar',
    mer: 'Mer',
    gio: 'Gio',
    ven: 'Ven',
    sab: 'Sab',
    dom: 'Dom',
  };

  const buildOrariString = function (row) {
    const parts = [];
    ['lun', 'mar', 'mer', 'gio', 'ven', 'sab', 'dom'].forEach((day) => {
      const start = (row[day + '_inizio'] || '').toString().substring(0, 5);
      const end = (row[day + '_fine'] || '').toString().substring(0, 5);
      if (start !== '' || end !== '') {
        parts.push(dayLabels[day] + ': ' + (start || '--:--') + '-' + (end || '--:--'));
      }
    });

    return parts.length > 0 ? parts.join(' | ') : '—';
  };

  new DataTable('#corsi-table', {
    serverSide: true,
    processing: true,
    pageLength: 10,
    order: [[0, 'desc']],
    ajax: {
      url: '/seiryokukai_php/public/api/corsi.php',
      type: 'GET',
    },
    language: {
      url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/it-IT.json',
    },
    columns: [
      { data: 'id' },
      { data: 'name' },
      { data: 'site' },
      { data: 'discipline' },
      { data: 'teacher' },
      { data: 'start_date' },
      { data: 'monthly_fee' },
      {
        data: null,
        render: function (row) {
          return '<small>' + buildOrariString(row) + '</small>';
        },
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (row) {
          const id = Number(row.id || 0);
          const dataAttr = String(JSON.stringify(row))
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

          return ''
            + '<button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editCourseModal" data-course="' + dataAttr + '" onclick="loadCourseData(' + id + ', JSON.parse(this.dataset.course))">'
            + '<i class="fa-solid fa-pencil"></i>'
            + '</button> '
            + '<form method="post" action="/seiryokukai_php/public/api/corsi.php" style="display:inline;">'
            + '<input type="hidden" name="action" value="delete">'
            + '<input type="hidden" name="course_id" value="' + id + '">'
            + '<button class="btn btn-sm btn-danger" type="submit" onclick="return confirm(\'Eliminare questo corso?\');">'
            + '<i class="fa-solid fa-trash"></i>'
            + '</button>'
            + '</form>';
        },
      },
    ],
  });
});
</script>
