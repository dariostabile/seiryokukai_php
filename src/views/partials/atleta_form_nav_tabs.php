<?php

declare(strict_types=1);

/**
 * Variabili attese dal parent view:
 * - $athleteNavTabs: array<int, array{label:string,enabled:bool,active:bool,target?:string,trigger_class?:string}>
 * - $athleteNavTabsExtraClass: string
 */

$athleteNavTabs = is_array($athleteNavTabs ?? null) ? $athleteNavTabs : [];
$athleteNavTabsExtraClass = trim((string) ($athleteNavTabsExtraClass ?? ''));
?>
<ul class="nav nav-tabs customtab<?= $athleteNavTabsExtraClass === '' ? '' : ' ' . htmlspecialchars($athleteNavTabsExtraClass) ?>" role="tablist">
  <?php foreach ($athleteNavTabs as $tab): ?>
    <?php
    $enabled = (bool) ($tab['enabled'] ?? false);
    $active = (bool) ($tab['active'] ?? false);
    $target = (string) ($tab['target'] ?? '');
    $triggerClass = trim((string) ($tab['trigger_class'] ?? ''));
    $classes = ['nav-link'];

    if ($active && $enabled) {
        $classes[] = 'active';
    }
    if ($triggerClass !== '') {
        $classes[] = $triggerClass;
    }
    if (!$enabled) {
        $classes[] = 'disabled';
    }

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
