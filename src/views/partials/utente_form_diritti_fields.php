<?php

declare(strict_types=1);

/**
 * Variabili attese:
 * - $utenteDirittiProfileIds: array<int>
 * - $utenteDirittiApplicationIds: array<int>
 * - $utenteDirittiProfileSelectId: string
 * - $utenteDirittiApplicationClass: string
 * - $utenteDirittiApplicationIdPrefix: string
 * - $profiles: array
 * - $groupedApplications: array
 * - $utenteDirittiShowSelfEditNotice: bool
 */

$utenteDirittiProfileIds = is_array($utenteDirittiProfileIds ?? null) ? $utenteDirittiProfileIds : [];
$utenteDirittiApplicationIds = is_array($utenteDirittiApplicationIds ?? null) ? $utenteDirittiApplicationIds : [];
$utenteDirittiProfileSelectId = (string) ($utenteDirittiProfileSelectId ?? '');
$utenteDirittiApplicationClass = (string) ($utenteDirittiApplicationClass ?? '');
$utenteDirittiApplicationIdPrefix = (string) ($utenteDirittiApplicationIdPrefix ?? 'userApplication');
$utenteDirittiShowSelfEditNotice = (bool) ($utenteDirittiShowSelfEditNotice ?? false);
?>
<?php if ($utenteDirittiShowSelfEditNotice): ?>
  <div id="selfEditNotice" class="alert alert-info py-2 px-3 d-none" role="alert">
    Sul tuo utente puoi modificare solo i dati anagrafici base, i recapiti e l'immagine.
  </div>
<?php endif; ?>

<div class="row g-2">
  <div class="col-12 col-md-6">
    <label class="form-label">Profili</label>
    <select class="form-select" name="profile_ids[]" id="<?= htmlspecialchars($utenteDirittiProfileSelectId) ?>" multiple size="3" required>
      <?php foreach ($profiles as $p): ?>
        <?php $profileValue = (int) ($p['id'] ?? 0); ?>
        <option value="<?= $profileValue ?>" <?= in_array($profileValue, $utenteDirittiProfileIds, true) ? 'selected' : '' ?>><?= htmlspecialchars((string) ($p['name'] ?? '')) ?></option>
      <?php endforeach; ?>
    </select>
    <small class="text-muted">Puoi selezionare piu profili.</small>
  </div>

  <div class="col-12 mt-3">
    <label class="form-label fw-semibold">Permessi applicativi</label>
    <div class="row g-3">
      <?php foreach ($groupedApplications as $groupName => $apps): ?>
        <div class="col-12 col-lg-6">
          <div class="border rounded p-3 h-100">
            <div class="fw-semibold mb-2"><?= htmlspecialchars((string) $groupName) ?></div>
            <div class="d-flex flex-column gap-2">
              <?php foreach ($apps as $app): ?>
                <?php $appId = (int) ($app['id'] ?? 0); ?>
                <div class="form-check">
                  <input
                    class="form-check-input <?= htmlspecialchars($utenteDirittiApplicationClass) ?>"
                    type="checkbox"
                    value="<?= $appId ?>"
                    name="application_ids[]"
                    id="<?= htmlspecialchars($utenteDirittiApplicationIdPrefix) . $appId ?>"
                    <?= in_array($appId, $utenteDirittiApplicationIds, true) ? 'checked' : '' ?>
                  >
                  <label class="form-check-label" for="<?= htmlspecialchars($utenteDirittiApplicationIdPrefix) . $appId ?>">
                    <?= htmlspecialchars((string) ($app['name'] ?? 'Applicazione')) ?>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
