<?php

declare(strict_types=1);

/** @var string $pageTitle */
/** @var string $viewContent */
/** @var array|null $user */
/** @var array $menuGroups */
/** @var string $currentPage */
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?> - Seiryokukai</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link href="/seiryokukai_php/public/assets/app.css" rel="stylesheet">
</head>
<body>
  <div class="app-shell">
    <aside class="app-sidebar">
      <div>
        <h1 class="brand">SEIRYOKUKAI</h1>
        <p class="brand-sub">Gestionale PHP</p>
      </div>
      <nav class="nav flex-column gap-2 mt-4">
        <a class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>" href="/seiryokukai_php/public/index.php?page=dashboard">
          <i class="fa-solid fa-chart-line me-2"></i>Dashboard
        </a>

        <?php foreach ($menuGroups as $group): ?>
          <span class="nav-group-label">
            <i class="<?= htmlspecialchars((string) ($group['icon'] ?? 'fa-solid fa-layer-group')) ?> me-2"></i>
            <?= htmlspecialchars((string) ($group['label'] ?? 'Applicazioni')) ?>
          </span>
          <?php foreach (($group['items'] ?? []) as $item): ?>
            <?php if (($item['enabled'] ?? false) === true): ?>
              <a class="nav-link <?= ($item['page'] ?? '') === $currentPage ? 'active' : '' ?>" href="/seiryokukai_php/public/index.php?page=<?= urlencode((string) $item['page']) ?>">
                <i class="<?= htmlspecialchars((string) ($item['icon'] ?? 'fa-solid fa-circle')) ?> me-2"></i>
                <?= htmlspecialchars((string) ($item['label'] ?? 'Modulo')) ?>
              </a>
            <?php else: ?>
              <span class="nav-link nav-link-disabled" title="Modulo non ancora disponibile in questa versione">
                <i class="<?= htmlspecialchars((string) ($item['icon'] ?? 'fa-solid fa-circle')) ?> me-2"></i>
                <?= htmlspecialchars((string) ($item['label'] ?? 'Modulo')) ?>
              </span>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </nav>
      <a class="logout" href="/seiryokukai_php/public/index.php?page=logout"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a>
    </aside>

    <main class="app-main">
      <header class="app-header d-flex justify-content-between align-items-center">
        <h2 class="m-0"><?= htmlspecialchars($pageTitle) ?></h2>
        <div class="user-chip">
          <i class="fa-solid fa-user-shield"></i>
          <?= htmlspecialchars($user['name'] ?? 'Utente') ?>
        </div>
      </header>

      <?= $viewContent ?>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
  <script src="/seiryokukai_php/public/assets/app.js"></script>
</body>
</html>
