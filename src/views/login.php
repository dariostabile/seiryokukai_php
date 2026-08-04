<?php

declare(strict_types=1);

$frontendAssets = frontend_asset_urls();
$frontendApi = frontend_api_urls();
$appPaths = app_paths();
$loginApiUrl = (string) ($frontendApi['login'] ?? '');
$publicAssetsPath = (string) $appPaths['assets'];
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Seiryokukai</title>
  <link rel="preconnect" href="<?= htmlspecialchars((string) ($frontendAssets['font_preconnect_api'] ?? 'https://fonts.googleapis.com')) ?>">
  <link rel="preconnect" href="<?= htmlspecialchars((string) ($frontendAssets['font_preconnect_static'] ?? 'https://fonts.gstatic.com')) ?>" crossorigin>
  <link href="<?= htmlspecialchars((string) ($frontendAssets['font_stylesheet'] ?? 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap')) ?>" rel="stylesheet">
  <link href="<?= htmlspecialchars((string) ($frontendAssets['bootstrap_css'] ?? 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css')) ?>" rel="stylesheet">
  <link href="<?= htmlspecialchars((string) ($frontendAssets['template_css'] ?? '/seiryokukai_php/public/template/university/dist/css/style.css')) ?>" rel="stylesheet">
  <link href="<?= htmlspecialchars((string) ($frontendAssets['fontawesome_css'] ?? 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css')) ?>" rel="stylesheet">
  <link href="<?= htmlspecialchars($publicAssetsPath . '/app.css') ?>" rel="stylesheet">
</head>
<body class="login-bg">
  <div class="login-wrap">
    <div class="login-card">
      <h1>SEIRYOKUKAI</h1>
      <p>Gestionale Client/Server PHP</p>

      <form method="post" action="<?= htmlspecialchars($loginApiUrl) ?>" class="mt-4">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input name="username" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input name="password" type="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Accedi</button>
      </form>

      <small class="d-block mt-3 text-muted">Demo: admin / admin123</small>
    </div>
  </div>
</body>
</html>
