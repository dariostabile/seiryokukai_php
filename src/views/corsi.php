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
      <table class="table align-middle js-datatable">
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
        <tbody>
          <?php foreach ($courses as $course): ?>
            <?php
            $orari = [];
            foreach ($dayLabels as $dayKey => $dayLabel) {
                $inizio = trim((string) ($course[$dayKey . '_inizio'] ?? ''));
                $fine = trim((string) ($course[$dayKey . '_fine'] ?? ''));
                if ($inizio !== '' || $fine !== '') {
                    $orari[] = substr($dayLabel, 0, 3) . ': ' . ($inizio !== '' ? $inizio : '--:--') . '-' . ($fine !== '' ? $fine : '--:--');
                }
            }
            $orariStr = $orari !== [] ? implode(' | ', $orari) : '—';
            ?>
            <tr>
              <td><?= (int) ($course['id'] ?? 0) ?></td>
              <td><?= htmlspecialchars((string) ($course['name'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($course['site'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($course['discipline'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($course['teacher'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($course['start_date'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($course['monthly_fee'] ?? '')) ?></td>
              <td><small><?= htmlspecialchars($orariStr) ?></small></td>
              <td>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editCourseModal"
                        onclick="loadCourseData(<?= (int) ($course['id'] ?? 0) ?>, <?= htmlspecialchars(json_encode($course, JSON_UNESCAPED_UNICODE)) ?>)">
                  <i class="fa-solid fa-pencil"></i>
                </button>
                <form method="post" action="/seiryokukai_php/public/api/corsi.php" style="display:inline;">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="course_id" value="<?= (int) ($course['id'] ?? 0) ?>">
                  <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Eliminare questo corso?');">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
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
</script>
