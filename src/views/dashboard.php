<?php

declare(strict_types=1);

/** @var array $stats */
?>
<div class="row g-3 mt-1">
  <div class="col-12 col-md-6 col-xl-3">
    <div class="metric-card">
      <span>Atleti Totali</span>
      <h3><?= (int) $stats['totalClients'] ?></h3>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="metric-card">
      <span>Atleti Attivi</span>
      <h3><?= (int) $stats['activeClients'] ?></h3>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="metric-card">
      <span>Atleti Sospesi</span>
      <h3><?= (int) $stats['pausedClients'] ?></h3>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="metric-card">
      <span>Presenze Oggi</span>
      <h3><?= (int) $stats['todayAttendance'] ?></h3>
    </div>
  </div>
</div>
