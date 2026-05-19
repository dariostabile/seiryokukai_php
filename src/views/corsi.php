<?php

declare(strict_types=1);

/** @var array $courses */
/** @var array $sites */
/** @var array $disciplines */
/** @var array $users */

$okMessage = trim((string) ($_GET['ok'] ?? ''));
$errMessage = trim((string) ($_GET['err'] ?? ''));

$dayLabels = [
    'lun' => 'Lunedi',
    'mar' => 'Martedi',
    'mer' => 'Mercoledi',
    'gio' => 'Giovedi',
    'ven' => 'Venerdi',
    'sab' => 'Sabato',
    'dom' => 'Domenica',
];

$addPrefill = [
    'name' => trim((string) ($_GET['add_name'] ?? '')),
    'site_id' => (int) ($_GET['add_site_id'] ?? 0),
    'discipline_id' => (int) ($_GET['add_discipline_id'] ?? 0),
    'user_id' => (int) ($_GET['add_user_id'] ?? 0),
    'start_date' => trim((string) ($_GET['add_start_date'] ?? '')),
    'end_date' => trim((string) ($_GET['add_end_date'] ?? '')),
    'monthly_fee' => trim((string) ($_GET['add_monthly_fee'] ?? '')),
    'active' => (int) ($_GET['add_active'] ?? 1),
    'only_active' => ((int) ($_GET['add_only_active'] ?? 1)) === 1,
];

foreach (array_keys($dayLabels) as $dayKey) {
    $addPrefill[$dayKey . '_inizio'] = trim((string) ($_GET['add_' . $dayKey . '_inizio'] ?? ''));
    $addPrefill[$dayKey . '_fine'] = trim((string) ($_GET['add_' . $dayKey . '_fine'] ?? ''));
}

$openAddPanel =
    $addPrefill['name'] !== ''
    || $addPrefill['site_id'] > 0
    || $addPrefill['discipline_id'] > 0
    || $addPrefill['user_id'] > 0
    || $addPrefill['start_date'] !== ''
    || $addPrefill['end_date'] !== ''
    || $addPrefill['monthly_fee'] !== '';

$openEdit = ((string) ($_GET['open_edit'] ?? '0')) === '1';
$editPrefill = [
    'id' => (int) ($_GET['edit_id'] ?? 0),
    'name' => trim((string) ($_GET['edit_name'] ?? '')),
    'site_id' => (int) ($_GET['edit_site_id'] ?? 0),
    'discipline_id' => (int) ($_GET['edit_discipline_id'] ?? 0),
    'user_id' => (int) ($_GET['edit_user_id'] ?? 0),
    'start_date' => trim((string) ($_GET['edit_start_date'] ?? '')),
    'end_date' => trim((string) ($_GET['edit_end_date'] ?? '')),
    'monthly_fee' => trim((string) ($_GET['edit_monthly_fee'] ?? '')),
    'active' => (int) ($_GET['edit_active'] ?? 1),
];

foreach (array_keys($dayLabels) as $dayKey) {
    $editPrefill[$dayKey . '_inizio'] = trim((string) ($_GET['edit_' . $dayKey . '_inizio'] ?? ''));
    $editPrefill[$dayKey . '_fine'] = trim((string) ($_GET['edit_' . $dayKey . '_fine'] ?? ''));
}
?>
<div class="card shadow-sm border-0 mt-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
      <h5 class="m-0">Gestione Corsi</h5>
      <button class="btn btn-success" type="button" id="openAddCoursePanelBtn">+ Aggiungi corso</button>
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
          <input class="form-check-input" type="checkbox" id="coursesOnlyActive" <?= $addPrefill['only_active'] ? 'checked' : '' ?>>
          <label class="form-check-label" for="coursesOnlyActive">Mostra solo corsi attivi</label>
        </div>
      </div>
      <table id="corsi-table" class="table align-middle js-datatable" data-server-side="1">
        <thead>
          <tr>
            <th>Corso</th>
            <th>Istruttore</th>
            <th>Inizio</th>
            <th>Fine</th>
            <th>Stato</th>
            <th>Orari</th>
            <th class="text-end">Azioni</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <div id="addCoursePanel" class="card border mt-4 <?= $openAddPanel ? '' : 'd-none' ?>">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Scheda Nuovo Corso</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeAddCoursePanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" action="/seiryokukai_php/public/api/corsi.php" class="row g-3" id="addCourseForm">
          <input type="hidden" name="action" value="add">

          <div class="col-12 d-flex justify-content-md-end">
            <div class="w-100" style="max-width: 220px;">
              <label class="form-label">Stato</label>
              <select class="form-select" name="active">
                <option value="1" <?= $addPrefill['active'] === 1 ? 'selected' : '' ?>>Attivo</option>
                <option value="0" <?= $addPrefill['active'] === 0 ? 'selected' : '' ?>>Non attivo</option>
              </select>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Nome Corso</label>
            <input class="form-control" name="name" placeholder="Nome corso" required value="<?= htmlspecialchars($addPrefill['name']) ?>">
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">Disciplina</label>
            <select class="form-select" name="discipline_id" required>
              <option value="">Seleziona disciplina</option>
              <?php foreach ($disciplines as $discipline): ?>
                <?php $disciplineId = (int) ($discipline['id'] ?? 0); ?>
                <option value="<?= $disciplineId ?>" <?= $disciplineId === $addPrefill['discipline_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars((string) ($discipline['name'] ?? '')) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Istruttore</label>
            <select class="form-select" name="user_id" required>
              <option value="">Seleziona istruttore</option>
              <?php foreach ($users as $u): ?>
                <?php $userId = (int) ($u['id'] ?? 0); ?>
                <option value="<?= $userId ?>" <?= $userId === $addPrefill['user_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars((string) ($u['name'] ?? '')) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">Sede</label>
            <select class="form-select" name="site_id" required>
              <option value="">Seleziona sede</option>
              <?php foreach ($sites as $site): ?>
                <?php $siteId = (int) ($site['id'] ?? 0); ?>
                <?php $siteIsActive = (int) ($site['active'] ?? 1) === 1; ?>
                <option value="<?= $siteId ?>" <?= $siteId === $addPrefill['site_id'] ? 'selected' : '' ?> <?= $siteIsActive ? '' : 'disabled' ?>>
                  <?= htmlspecialchars((string) ($site['name'] ?? '')) ?><?= $siteIsActive ? '' : ' (non attiva)' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12 col-md-2">
            <label class="form-label">Data Inizio</label>
            <input class="form-control" type="date" name="start_date" value="<?= htmlspecialchars($addPrefill['start_date']) ?>">
          </div>
          <div class="col-12 col-md-2">
            <label class="form-label">Data Fine</label>
            <input class="form-control" type="date" name="end_date" value="<?= htmlspecialchars($addPrefill['end_date']) ?>">
          </div>
          <div class="col-12 col-md-2">
            <label class="form-label">Quota Mensile</label>
            <input class="form-control" type="number" name="monthly_fee" step="0.01" placeholder="Quota" value="<?= htmlspecialchars($addPrefill['monthly_fee']) ?>">
          </div>

          <div class="col-12">
            <small class="text-muted">Orari settimanali:</small>
          </div>

          <?php foreach ($dayLabels as $dayKey => $dayLabel): ?>
            <div class="col-12 col-md-2">
              <label class="form-label mb-1"><?= htmlspecialchars($dayLabel) ?></label>
            </div>
            <div class="col-6 col-md-2">
              <input type="time" class="form-control" name="<?= htmlspecialchars($dayKey) ?>_inizio" value="<?= htmlspecialchars((string) ($addPrefill[$dayKey . '_inizio'] ?? '')) ?>">
            </div>
            <div class="col-6 col-md-2">
              <input type="time" class="form-control" name="<?= htmlspecialchars($dayKey) ?>_fine" value="<?= htmlspecialchars((string) ($addPrefill[$dayKey . '_fine'] ?? '')) ?>">
            </div>
          <?php endforeach; ?>

          <div class="col-12 d-flex justify-content-end gap-2">
            <button class="btn btn-secondary" type="button" id="cancelAddCourseBtn">Annulla</button>
            <button class="btn btn-success" type="submit">+ Aggiungi Corso</button>
          </div>
        </form>
      </div>
    </div>

    <div id="editCoursePanel" class="card border mt-4 d-none">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Scheda Corso</h6>
        <button class="btn btn-sm btn-outline-secondary" type="button" id="closeEditCoursePanelBtn">Chiudi</button>
      </div>
      <div class="card-body">
        <form method="post" action="/seiryokukai_php/public/api/corsi.php" class="row g-3" id="editCourseForm">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="course_id" id="editCourseId">

          <div class="col-12 d-flex justify-content-md-end">
            <div class="w-100" style="max-width: 220px;">
              <label class="form-label">Stato</label>
              <select class="form-select" name="active" id="editCourseActive">
                <option value="1">Attivo</option>
                <option value="0">Non attivo</option>
              </select>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Nome Corso</label>
            <input type="text" class="form-control" name="name" id="editCourseName" required>
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
          <div class="col-12 col-md-3">
            <label class="form-label">Sede</label>
            <select class="form-select" name="site_id" id="editCourseSite" required>
              <option value="">Seleziona sede</option>
              <?php foreach ($sites as $site): ?>
                <?php $siteId = (int) ($site['id'] ?? 0); ?>
                <?php $siteIsActive = (int) ($site['active'] ?? 1) === 1; ?>
                <option value="<?= $siteId ?>" <?= $siteIsActive ? '' : 'disabled' ?>>
                  <?= htmlspecialchars((string) ($site['name'] ?? '')) ?><?= $siteIsActive ? '' : ' (non attiva)' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12 col-md-2">
            <label class="form-label">Data Inizio</label>
            <input type="date" class="form-control" name="start_date" id="editCourseStartDate">
          </div>
          <div class="col-12 col-md-2">
            <label class="form-label">Data Fine</label>
            <input type="date" class="form-control" name="end_date" id="editCourseEndDate">
          </div>
          <div class="col-12 col-md-2">
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

          <div class="col-12 d-flex justify-content-end gap-2">
            <button class="btn btn-secondary" type="button" id="cancelEditCourseBtn">Annulla</button>
            <button class="btn btn-primary" type="submit">Salva</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const openAddCoursePanelBtn = document.getElementById('openAddCoursePanelBtn');
  const addCoursePanel = document.getElementById('addCoursePanel');
  const closeAddCoursePanelBtn = document.getElementById('closeAddCoursePanelBtn');
  const cancelAddCourseBtn = document.getElementById('cancelAddCourseBtn');

  const editCoursePanel = document.getElementById('editCoursePanel');
  const closeEditCoursePanelBtn = document.getElementById('closeEditCoursePanelBtn');
  const cancelEditCourseBtn = document.getElementById('cancelEditCourseBtn');

  const coursesOnlyActiveCheckbox = document.getElementById('coursesOnlyActive');

  if (openAddCoursePanelBtn && addCoursePanel) {
    openAddCoursePanelBtn.addEventListener('click', function () {
      addCoursePanel.classList.remove('d-none');
    });
  }

  [closeAddCoursePanelBtn, cancelAddCourseBtn].forEach(function (btn) {
    if (btn && addCoursePanel) {
      btn.addEventListener('click', function () {
        addCoursePanel.classList.add('d-none');
      });
    }
  });

  [closeEditCoursePanelBtn, cancelEditCourseBtn].forEach(function (btn) {
    if (btn && editCoursePanel) {
      btn.addEventListener('click', function () {
        editCoursePanel.classList.add('d-none');
      });
    }
  });

  if (typeof DataTable === 'undefined') {
    return;
  }

  const dayKeys = ['lun', 'mar', 'mer', 'gio', 'ven', 'sab', 'dom'];
  const dayLabels = {
    lun: 'Lun',
    mar: 'Mar',
    mer: 'Mer',
    gio: 'Gio',
    ven: 'Ven',
    sab: 'Sab',
    dom: 'Dom',
  };

  function htmlEscape(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function buildOrariRows(row) {
    const rows = [];

    dayKeys.forEach(function (day) {
      const start = String(row[day + '_inizio'] || '').substring(0, 5);
      const end = String(row[day + '_fine'] || '').substring(0, 5);
      if (start !== '' || end !== '') {
        rows.push(dayLabels[day] + ': ' + (start || '--:--') + '-' + (end || '--:--'));
      }
    });

    return rows;
  }

  function formatDateIt(value, type) {
    const raw = String(value || '').trim();
    if (raw === '') {
      return '';
    }

    const isoDate = raw.substring(0, 10);
    const match = isoDate.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (!match) {
      return htmlEscape(isoDate);
    }

    const formatted = match[3] + '/' + match[2] + '/' + match[1];
    if (type === 'display' || type === 'filter') {
      return htmlEscape(formatted);
    }

    return isoDate;
  }

  function fillEditCourseForm(row) {
    document.getElementById('editCourseId').value = row.id || '';
    document.getElementById('editCourseName').value = row.name || '';
    document.getElementById('editCourseSite').value = row.site_id || '';
    document.getElementById('editCourseDiscipline').value = row.discipline_id || '';
    document.getElementById('editCourseUser').value = row.user_id || '';
    document.getElementById('editCourseStartDate').value = row.start_date || '';
    document.getElementById('editCourseEndDate').value = row.end_date || '';
    document.getElementById('editCourseMonthlyFee').value = row.monthly_fee || '';
    document.getElementById('editCourseActive').value = Number(row.active) === 0 ? '0' : '1';

    dayKeys.forEach(function (day) {
      const startInput = document.getElementById('edit_' + day + '_inizio');
      const endInput = document.getElementById('edit_' + day + '_fine');
      if (startInput) {
        startInput.value = String(row[day + '_inizio'] || '').substring(0, 5);
      }
      if (endInput) {
        endInput.value = String(row[day + '_fine'] || '').substring(0, 5);
      }
    });

    if (editCoursePanel) {
      editCoursePanel.classList.remove('d-none');
      editCoursePanel.scrollIntoView({ behavior: 'smooth' });
    }
  }

  const coursesTable = new DataTable('#corsi-table', {
    serverSide: true,
    processing: true,
    pageLength: 10,
    order: [[0, 'desc']],
    ajax: {
      url: '/seiryokukai_php/public/api/corsi.php',
      type: 'GET',
      data: function (d) {
        d.active_only = coursesOnlyActiveCheckbox && coursesOnlyActiveCheckbox.checked ? 1 : 0;
      },
    },
    language: {
      url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/it-IT.json',
    },
    columns: [
      {
        data: 'name',
        render: function (value, type, row) {
          const courseName = htmlEscape(value);
          const siteName = htmlEscape(row.site || '');

          if (type !== 'display') {
            return String(value || '');
          }

          if (siteName === '') {
            return '<span>' + courseName + '</span>';
          }

          return ''
            + '<span>' + courseName + '</span>'
            + '<small class="d-block text-muted">[' + siteName + ']</small>';
        }
      },
      { data: 'teacher' },
      {
        data: 'start_date',
        render: function (value, type) {
          return formatDateIt(value, type);
        }
      },
      {
        data: 'end_date',
        render: function (value, type) {
          return formatDateIt(value, type);
        }
      },
      {
        data: 'active',
        render: function (value) {
          return Number(value) === 1
            ? '<span class="badge text-bg-success">Attivo</span>'
            : '<span class="badge text-bg-secondary">Non attivo</span>';
        }
      },
      {
        data: null,
        render: function (row) {
          const rows = buildOrariRows(row);
          if (rows.length === 0) {
            return '<small>--</small>';
          }

          return rows.map(function (line) {
            return '<small class="d-block">' + htmlEscape(line) + '</small>';
          }).join('');
        }
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-end',
        render: function (row) {
          const id = Number(row.id || 0);
          const payload = htmlEscape(JSON.stringify(row));

          return ''
            + '<button class="btn btn-sm btn-outline-primary edit-course-btn" type="button" data-course="' + payload + '">Modifica</button> '
            + '<form method="post" action="/seiryokukai_php/public/api/corsi.php" style="display:inline;" onsubmit="return confirm(\'Eliminare questo corso?\');">'
            + '<input type="hidden" name="action" value="delete">'
            + '<input type="hidden" name="course_id" value="' + id + '">'
            + '<button class="btn btn-sm btn-outline-danger" type="submit">Elimina</button>'
            + '</form>';
        }
      },
    ]
  });

  const tableElement = document.getElementById('corsi-table');
  if (tableElement) {
    tableElement.addEventListener('click', function (event) {
      const button = event.target.closest('.edit-course-btn');
      if (!button) {
        return;
      }

      const payload = button.getAttribute('data-course') || '{}';
      let row = {};
      try {
        row = JSON.parse(payload);
      } catch (error) {
        row = {};
      }

      fillEditCourseForm(row);
    });
  }

  if (coursesOnlyActiveCheckbox) {
    coursesOnlyActiveCheckbox.addEventListener('change', function () {
      coursesTable.ajax.reload();
    });
  }

  <?php if ($openEdit && $editPrefill['id'] > 0): ?>
    fillEditCourseForm({
      id: <?= (int) $editPrefill['id'] ?>,
      name: <?= json_encode($editPrefill['name']) ?>,
      site_id: <?= (int) $editPrefill['site_id'] ?>,
      discipline_id: <?= (int) $editPrefill['discipline_id'] ?>,
      user_id: <?= (int) $editPrefill['user_id'] ?>,
      start_date: <?= json_encode($editPrefill['start_date']) ?>,
      end_date: <?= json_encode($editPrefill['end_date']) ?>,
      monthly_fee: <?= json_encode($editPrefill['monthly_fee']) ?>,
      active: <?= (int) $editPrefill['active'] === 0 ? '0' : '1' ?>,
      lun_inizio: <?= json_encode($editPrefill['lun_inizio']) ?>,
      lun_fine: <?= json_encode($editPrefill['lun_fine']) ?>,
      mar_inizio: <?= json_encode($editPrefill['mar_inizio']) ?>,
      mar_fine: <?= json_encode($editPrefill['mar_fine']) ?>,
      mer_inizio: <?= json_encode($editPrefill['mer_inizio']) ?>,
      mer_fine: <?= json_encode($editPrefill['mer_fine']) ?>,
      gio_inizio: <?= json_encode($editPrefill['gio_inizio']) ?>,
      gio_fine: <?= json_encode($editPrefill['gio_fine']) ?>,
      ven_inizio: <?= json_encode($editPrefill['ven_inizio']) ?>,
      ven_fine: <?= json_encode($editPrefill['ven_fine']) ?>,
      sab_inizio: <?= json_encode($editPrefill['sab_inizio']) ?>,
      sab_fine: <?= json_encode($editPrefill['sab_fine']) ?>,
      dom_inizio: <?= json_encode($editPrefill['dom_inizio']) ?>,
      dom_fine: <?= json_encode($editPrefill['dom_fine']) ?>,
    });
  <?php endif; ?>
});
</script>
