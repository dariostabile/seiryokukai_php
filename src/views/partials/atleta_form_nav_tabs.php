<?php

declare(strict_types=1);

/**
 * Variabili attese dal parent view:
 * - $athleteNavTabs: array<int, array{label:string,enabled:bool,active:bool,target?:string,trigger_class?:string}>
 * - $athleteNavTabsExtraClass: string
 * - $athleteNavTabsVertical: bool  (opzionale, default false) — usa layout vtabs verticale
 */

$athleteNavTabs      = is_array($athleteNavTabs ?? null) ? $athleteNavTabs : [];
$athleteNavTabsExtraClass = trim((string) ($athleteNavTabsExtraClass ?? ''));
$athleteNavTabsVertical   = (bool) ($athleteNavTabsVertical ?? false);

if ($athleteNavTabsVertical):
?>
<div class="vtabs<?= $athleteNavTabsExtraClass === '' ? '' : ' ' . htmlspecialchars($athleteNavTabsExtraClass) ?>">
  <ul class="nav nav-tabs tabs-vertical" role="tablist">
    <?php foreach ($athleteNavTabs as $tab): ?>
      <?php
      $enabled      = (bool)   ($tab['enabled']       ?? false);
      $active       = (bool)   ($tab['active']        ?? false);
      $target       = (string) ($tab['target']        ?? '');
      $triggerClass = trim((string) ($tab['trigger_class'] ?? ''));

      $classes = ['nav-link'];
      if ($active && $enabled)  { $classes[] = 'active'; }
      if ($triggerClass !== '')  { $classes[] = $triggerClass; }
      if (!$enabled)             { $classes[] = 'disabled'; }

      $linkClass = implode(' ', $classes);
      // Per i tab verticali il template usa <a href="#id"> — necessario affinché
      // il CSS width:150px e display:table-cell funzionino correttamente.
      $href = $enabled && $target !== '' ? $target : '#';
      ?>
      <li class="nav-item" role="presentation">
        <a
          class="<?= htmlspecialchars($linkClass) ?>"
          role="tab"
          href="<?= htmlspecialchars($href) ?>"
          <?= $enabled ? 'data-bs-toggle="tab"' : 'aria-disabled="true" tabindex="-1"' ?>
        ><?= htmlspecialchars((string) ($tab['label'] ?? '')) ?></a>
      </li>
    <?php endforeach; ?>
  </ul>
  <!-- il tab-content viene iniettato dal parent subito dopo; il </div>.vtabs lo chiude anch'esso il parent -->
<?php else: ?>
<ul class="nav nav-tabs customtab<?= $athleteNavTabsExtraClass === '' ? '' : ' ' . htmlspecialchars($athleteNavTabsExtraClass) ?>" role="tablist">
  <?php foreach ($athleteNavTabs as $tab): ?>
    <?php
    $enabled      = (bool)   ($tab['enabled']       ?? false);
    $active       = (bool)   ($tab['active']        ?? false);
    $target       = (string) ($tab['target']        ?? '');
    $triggerClass = trim((string) ($tab['trigger_class'] ?? ''));

    $classes = ['nav-link'];
    if ($active && $enabled)  { $classes[] = 'active'; }
    if ($triggerClass !== '')  { $classes[] = $triggerClass; }
    if (!$enabled)             { $classes[] = 'disabled'; }

    $buttonClass = implode(' ', $classes);
    ?>
    <li class="nav-item" role="presentation">
      <button
        class="<?= htmlspecialchars($buttonClass) ?>"
        type="button"
        role="tab"
        <?= $enabled ? 'data-bs-toggle="tab" data-bs-target="' . htmlspecialchars($target) . '"' : 'aria-disabled="true" tabindex="-1"' ?>
      ><?= htmlspecialchars((string) ($tab['label'] ?? '')) ?></button>
    </li>
  <?php endforeach; ?>
</ul>
<?php endif; ?>