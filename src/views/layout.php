<?php

declare(strict_types=1);

/** @var string $pageTitle */
/** @var string $viewContent */
/** @var array|null $user */
/** @var array $menuGroups */
/** @var string $currentPage */

$frontendAssets = frontend_asset_urls();
$frontendApi = frontend_api_urls();
$appPaths = app_paths();
$publicAssetsPath = (string) $appPaths['assets'];
$indexPath = (string) $appPaths['index'];
$templateDistPath = (string) $appPaths['public'] . '/template/university/dist';
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?> - Seiryokukai</title>
  <link rel="preconnect" href="<?= htmlspecialchars((string) ($frontendAssets['font_preconnect_api'] ?? 'https://fonts.googleapis.com')) ?>">
  <link rel="preconnect" href="<?= htmlspecialchars((string) ($frontendAssets['font_preconnect_static'] ?? 'https://fonts.gstatic.com')) ?>" crossorigin>
  <link href="<?= htmlspecialchars((string) ($frontendAssets['font_stylesheet'] ?? 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap')) ?>" rel="stylesheet">
  <link href="<?= htmlspecialchars((string) ($frontendAssets['bootstrap_css'] ?? 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css')) ?>" rel="stylesheet">
  <link href="<?= htmlspecialchars((string) ($frontendAssets['template_css'] ?? '/seiryokukai_php/public/template/university/dist/css/style.css')) ?>" rel="stylesheet">
  <link href="<?= htmlspecialchars((string) ($frontendAssets['datatables_css_bootstrap'] ?? 'https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css')) ?>" rel="stylesheet">
  <link href="<?= htmlspecialchars((string) ($frontendAssets['select2_css'] ?? 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css')) ?>" rel="stylesheet">
  <link href="<?= htmlspecialchars((string) ($frontendAssets['select2_bootstrap5_css'] ?? 'https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css')) ?>" rel="stylesheet">
  <link href="<?= htmlspecialchars((string) ($frontendAssets['fontawesome_css'] ?? 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css')) ?>" rel="stylesheet">
  <link href="<?= htmlspecialchars($publicAssetsPath . '/app.css') ?>" rel="stylesheet">
</head>
<body class="skin-default fixed-header fixed-sidebar card-no-border">
  <div id="main-wrapper">
    <header class="topbar">
      <nav class="navbar top-navbar navbar-expand-md navbar-dark">
        <div class="navbar-header">
          <a class="navbar-brand" href="<?= htmlspecialchars($indexPath) ?>?page=dashboard">
            <span class="logo-text text-white fw-semibold">SEIRYOKUKAI</span>
          </a>
        </div>

        <div class="navbar-collapse">
          <ul class="navbar-nav me-auto">
            <li class="nav-item">
              <a class="nav-link nav-toggler d-block d-md-none waves-effect waves-dark" href="javascript:void(0)">
                <i class="fa-solid fa-bars"></i>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link sidebartoggler d-none d-lg-block d-md-block waves-effect waves-dark" href="javascript:void(0)">
                <i class="fa-solid fa-bars"></i>
              </a>
            </li>
          </ul>

          <ul class="navbar-nav my-lg-0">
            <li class="nav-item dropdown u-pro">
              <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark profile-pic" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-user-shield me-2"></i>
                <span class="hidden-md-down"><?= htmlspecialchars($user['name'] ?? 'Utente') ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item" href="<?= htmlspecialchars($indexPath) ?>?page=logout">
                    <i class="fa-solid fa-right-from-bracket me-2"></i>Logout
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>
    </header>

    <aside class="left-sidebar">
      <div class="scroll-sidebar">
        <nav class="sidebar-nav">
          <ul id="sidebarnav">
            <li class="user-pro"></li>
            <li>
              <a class="waves-effect waves-dark <?= $currentPage === 'dashboard' ? 'active' : '' ?>" href="<?= htmlspecialchars($indexPath) ?>?page=dashboard" aria-expanded="false">
                <i class="fa-solid fa-chart-line"></i>
                <span class="hide-menu">Dashboard</span>
              </a>
            </li>

            <?php foreach ($menuGroups as $group): ?>
              <?php
                $groupItems = (array) ($group['items'] ?? []);
                $groupHasActive = false;
                foreach ($groupItems as $groupItem) {
                    if (($groupItem['enabled'] ?? false) === true && (string) ($groupItem['page'] ?? '') === $currentPage) {
                        $groupHasActive = true;
                        break;
                    }
                }
              ?>
              <li class="<?= $groupHasActive ? 'active' : '' ?>">
                <a class="has-arrow waves-effect waves-dark <?= $groupHasActive ? 'active' : '' ?>" href="javascript:void(0)" aria-expanded="<?= $groupHasActive ? 'true' : 'false' ?>">
                  <i class="<?= htmlspecialchars((string) ($group['icon'] ?? 'fa-solid fa-layer-group')) ?>"></i>
                  <span class="hide-menu"><?= htmlspecialchars((string) ($group['label'] ?? 'Applicazioni')) ?></span>
                </a>
                <ul aria-expanded="<?= $groupHasActive ? 'true' : 'false' ?>" class="collapse <?= $groupHasActive ? 'in' : '' ?>">
                  <?php foreach ($groupItems as $item): ?>
                    <?php if (($item['enabled'] ?? false) === true): ?>
                      <li>
                        <a class="waves-effect waves-dark <?= ($item['page'] ?? '') === $currentPage ? 'active' : '' ?>" href="<?= htmlspecialchars($indexPath) ?>?page=<?= urlencode((string) $item['page']) ?>" aria-expanded="false">
                          <i class="<?= htmlspecialchars((string) ($item['icon'] ?? 'fa-solid fa-circle')) ?>"></i>
                          <span class="hide-menu"><?= htmlspecialchars((string) ($item['label'] ?? 'Modulo')) ?></span>
                        </a>
                      </li>
                    <?php else: ?>
                      <li>
                        <a class="waves-effect waves-dark text-muted" href="javascript:void(0)" aria-expanded="false" title="Modulo non ancora disponibile in questa versione">
                          <i class="<?= htmlspecialchars((string) ($item['icon'] ?? 'fa-solid fa-circle')) ?>"></i>
                          <span class="hide-menu"><?= htmlspecialchars((string) ($item['label'] ?? 'Modulo')) ?></span>
                        </a>
                      </li>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </ul>
              </li>
            <?php endforeach; ?>
          </ul>
        </nav>
      </div>
    </aside>

    <div class="page-wrapper">
      <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="mb-0"><?= htmlspecialchars($pageTitle) ?></h4>
        </div>
        <?= $viewContent ?>
      </div>
    </div>
  </div>

  <script src="<?= htmlspecialchars((string) ($frontendAssets['jquery_js'] ?? 'https://code.jquery.com/jquery-3.7.1.min.js')) ?>"></script>
  <script src="<?= htmlspecialchars((string) ($frontendAssets['bootstrap_js'] ?? 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js')) ?>"></script>
  <script src="<?= htmlspecialchars($templateDistPath . '/js/perfect-scrollbar.jquery.min.js') ?>"></script>
  <script src="<?= htmlspecialchars($templateDistPath . '/js/waves.js') ?>"></script>
  <script src="<?= htmlspecialchars($templateDistPath . '/js/sidebarmenu.js') ?>"></script>
  <script src="<?= htmlspecialchars($templateDistPath . '/js/custom.js') ?>"></script>
  <script src="<?= htmlspecialchars((string) ($frontendAssets['datatables_js_core'] ?? 'https://cdn.datatables.net/2.0.8/js/dataTables.min.js')) ?>"></script>
  <script src="<?= htmlspecialchars((string) ($frontendAssets['datatables_js_bootstrap'] ?? 'https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js')) ?>"></script>
  <script src="<?= htmlspecialchars((string) ($frontendAssets['select2_js'] ?? 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js')) ?>"></script>
  <script>
    window.SeiryokukaiConfig = Object.assign({}, window.SeiryokukaiConfig || {}, {
      dataTableLangUrl: <?= json_encode((string) ($frontendAssets['datatables_i18n_it'] ?? ''), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>,
      api: <?= json_encode($frontendApi, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
    });
  </script>
  <script src="<?= htmlspecialchars($publicAssetsPath . '/app.js') ?>"></script>
</body>
</html>
