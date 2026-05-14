<?php

declare(strict_types=1);

/** @var array $courses */
/** @var array $sites */
/** @var array $disciplines */
/** @var array $users */

$dayLabels = ['lun' => 'Lunedì', 'mar' => 'Martedì', 'merc' => 'Mercoledì', 'giov' => 'Giovedì', 'ven' => 'Venerdì', 'sab' => 'Sabato', 'dom' => 'Domenica'];
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
      <div class="col-12 col-md-2">
        <input class="form-control" type="date" name="start_date">
      </div>
      <div class="col-12">
        <small class="text-muted">Giorni della settimana:</small>
        <div class="btn-group btn-group-sm ms-2" role="group">
          <?php foreach ($dayLabels as $key => $label): ?>
            <input type="checkbox" class="btn-check" name="day_<?= htmlspecialchars($key) ?>" id="day_<?= htmlspecialchars($key) ?>">
            <label class="btn btn-outline-secondary" for="day_<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-12">
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
            <th>Giorni</th>
            <th>Azioni</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($courses as $course): ?>
            <?php
            $daysActive = [];
            foreach (['lun', 'mar', 'merc', 'giov', 'ven', 'sab', 'dom'] as $day) {
                if ((int) ($course[$day] ?? 0) === 1) {
                    $daysActive[] = substr($dayLabels[$day], 0, 3);
                }
            }
            $daysStr = !empty($daysActive) ? implode(', ', $daysActive) : '—';
            ?>
            <tr>
              <td><?= (int) ($course['id'] ?? 0) ?></td>
              <td><?= htmlspecialchars((string) ($course['name'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($course['site'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($course['discipline'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($course['teacher'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($course['start_date'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($course['monthly_fee'] ?? '')) ?></td>
              <td><small><?= htmlspecialchars($daysStr) ?></small></td>
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

<!-- Modal Edit Corso -->
<div class="modal fade" id="editCourseModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modifica Corso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" action="/seiryokukai_php/public/api/corsi.php">
        <div class="modal-body">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="course_id" id="editCourseId">
          
          <div class="mb-3">
            <label class="form-label">Nome Corso</label>
            <input type="text" class="form-control" name="name" id="editCourseName" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Sede</label>
            <select class="form-select" name="site_id" id="editCourseSite" required>
              <option value="">Seleziona sede</option>
              <?php foreach ($sites as $site): ?>
                <option value="<?= (int) ($site['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($site['name'] ?? '')) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Disciplina</label>
            <select class="form-select" name="discipline_id" id="editCourseDiscipline" required>
              <option value="">Seleziona disciplina</option>
              <?php foreach ($disciplines as $discipline): ?>
                <option value="<?= (int) ($discipline['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($discipline['name'] ?? '')) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Istruttore</label>
            <select class="form-select" name="user_id" id="editCourseUser" required>
              <option value="">Seleziona istruttore</option>
              <?php foreach ($users as $u): ?>
                <option value="<?= (int) ($u['id'] ?? 0) ?>"><?= htmlspecialchars((string) ($u['name'] ?? '')) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Data Inizio</label>
            <input type="date" class="form-control" name="start_date" id="editCourseStartDate">
          </div>

          <div class="mb-3">
            <label class="form-label">Quota Mensile</label>
            <input type="number" class="form-control" name="monthly_fee" id="editCourseMonthlyFee" step="0.01">
          </div>

          <div class="mb-3">
            <label class="form-label d-block">Giorni della settimana:</label>
            <div class="btn-group btn-group-sm" role="group">
              <?php foreach ($dayLabels as $key => $label): ?>
                <input type="checkbox" class="btn-check day-checkbox" name="day_<?= htmlspecialchars($key) ?>" id="edit_day_<?= htmlspecialchars($key) ?>" data-day="<?= htmlspecialchars($key) ?>">
                <label class="btn btn-outline-secondary" for="edit_day_<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></label>
              <?php endforeach; ?>
            </div>
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

  // Uncheck all days first
  document.querySelectorAll('.day-checkbox').forEach(checkbox => {
    checkbox.checked = false;
  });

  // Check active days
  const days = ['lun', 'mar', 'merc', 'giov', 'ven', 'sab', 'dom'];
  days.forEach(day => {
    if ((courseData[day] ?? 0) === 1 || (courseData[day] ?? 0) === '1') {
      const checkbox = document.getElementById('edit_day_' + day);
      if (checkbox) checkbox.checked = true;
    }
  });
}
</script>
</div>
