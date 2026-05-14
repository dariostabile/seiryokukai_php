<?php

declare(strict_types=1);

/** @var array $courses */
/** @var array $sites */
/** @var array $disciplines */
/** @var array $users */
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
      <div class="col-12 col-md-1">
        <button class="btn btn-success w-100" type="submit">+</button>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Corso</th>
            <th>Sede</th>
            <th>Disciplina</th>
            <th>Istruttore</th>
            <th>Inizio</th>
            <th>Quota</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($courses as $course): ?>
            <tr>
              <td><?= (int) ($course['id'] ?? 0) ?></td>
              <td><?= htmlspecialchars((string) ($course['name'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($course['site'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($course['discipline'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($course['teacher'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($course['start_date'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($course['monthly_fee'] ?? '')) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
