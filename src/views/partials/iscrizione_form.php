<?php

declare(strict_types=1);

/** @var string $formAction */
/** @var string $formId */
/** @var array<int, string> $hiddenFields */
/** @var array<string, string|array<int, int|string>> $values */
/** @var array<string, string> $fieldIds */
/** @var string $courseHelpText */
/** @var string $submitLabel */
/** @var string $submitButtonClass */
/** @var string $footerJustifyClass */
/** @var string $cancelButtonId */
/** @var string $cancelButtonLabel */
/** @var array<int, array<string, mixed>> $corsi */

$selectedCourseIds = array_map(
    static fn ($value): string => (string) $value,
    is_array($values['course_ids'] ?? null) ? $values['course_ids'] : []
);

$abbonamentoValue = (string) ($values['abbonamento'] ?? '1');
$dataInizioValue = (string) ($values['data_inizio_iscrizione'] ?? '');
$dataFineValue = (string) ($values['data_fine_iscrizione'] ?? '');
$totaleValue = (string) ($values['totale_abbonamento'] ?? '');
$statoValue = (string) ($values['stato_iscrizione'] ?? 'A');
$noteValue = (string) ($values['note_iscrizione'] ?? '');
?>
<form method="post" action="<?= htmlspecialchars($formAction) ?>" class="row g-3" id="<?= htmlspecialchars($formId) ?>">
  <?php foreach ($hiddenFields as $hiddenField): ?>
    <?= $hiddenField ?>
  <?php endforeach; ?>

  <div class="col-12 col-md-3">
    <label class="form-label">Abbonamento</label>
    <select
      class="form-select"
      name="abbonamento"
      <?= ($fieldIds['abbonamento'] ?? '') !== '' ? 'id="' . htmlspecialchars($fieldIds['abbonamento']) . '"' : '' ?>
      required
    >
      <option value="1" <?= $abbonamentoValue === '1' ? 'selected' : '' ?>>1 - mensile</option>
      <option value="2" <?= $abbonamentoValue === '2' ? 'selected' : '' ?>>2 - bimestrale</option>
      <option value="3" <?= $abbonamentoValue === '3' ? 'selected' : '' ?>>3 - trimestrale</option>
      <option value="4" <?= $abbonamentoValue === '4' ? 'selected' : '' ?>>4 - quadrimestrale</option>
      <option value="6" <?= $abbonamentoValue === '6' ? 'selected' : '' ?>>6 - semestrale</option>
      <option value="12" <?= $abbonamentoValue === '12' ? 'selected' : '' ?>>12 - annuale</option>
    </select>
  </div>

  <div class="col-12 col-md-3">
    <label class="form-label">Data inizio</label>
    <input
      type="date"
      class="form-control"
      name="data_inizio_iscrizione"
      value="<?= htmlspecialchars($dataInizioValue) ?>"
      <?= ($fieldIds['data_inizio_iscrizione'] ?? '') !== '' ? 'id="' . htmlspecialchars($fieldIds['data_inizio_iscrizione']) . '"' : '' ?>
      required
    >
  </div>

  <div class="col-12 col-md-3">
    <label class="form-label">Data fine</label>
    <input
      type="date"
      class="form-control"
      name="data_fine_iscrizione"
      value="<?= htmlspecialchars($dataFineValue) ?>"
      <?= ($fieldIds['data_fine_iscrizione'] ?? '') !== '' ? 'id="' . htmlspecialchars($fieldIds['data_fine_iscrizione']) . '"' : '' ?>
    >
  </div>

  <div class="col-12 col-md-3">
    <label class="form-label">Totale abbonamento</label>
    <input
      type="number"
      step="0.01"
      min="0"
      class="form-control"
      name="totale_abbonamento"
      value="<?= htmlspecialchars($totaleValue) ?>"
      <?= ($fieldIds['totale_abbonamento'] ?? '') !== '' ? 'id="' . htmlspecialchars($fieldIds['totale_abbonamento']) . '"' : '' ?>
    >
  </div>

  <div class="col-12 col-md-3">
    <label class="form-label">Stato</label>
    <select
      class="form-select"
      name="stato_iscrizione"
      <?= ($fieldIds['stato_iscrizione'] ?? '') !== '' ? 'id="' . htmlspecialchars($fieldIds['stato_iscrizione']) . '"' : '' ?>
      required
    >
      <option value="A" <?= $statoValue === 'A' ? 'selected' : '' ?>>Attiva</option>
      <option value="S" <?= $statoValue === 'S' ? 'selected' : '' ?>>Sospesa</option>
      <option value="C" <?= $statoValue === 'C' ? 'selected' : '' ?>>Conclusa</option>
    </select>
  </div>

  <div class="col-12">
    <label class="form-label">Corsi collegati</label>
    <select
      class="form-select"
      name="course_ids[]"
      <?= ($fieldIds['course_ids'] ?? '') !== '' ? 'id="' . htmlspecialchars($fieldIds['course_ids']) . '"' : '' ?>
      multiple
      size="5"
      required
    >
      <?php foreach ($corsi as $corso): ?>
        <?php $courseId = (string) ((int) ($corso['id'] ?? 0)); ?>
        <option value="<?= htmlspecialchars($courseId) ?>" <?= in_array($courseId, $selectedCourseIds, true) ? 'selected' : '' ?>>
          <?= htmlspecialchars((string) ($corso['name'] ?? '')) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <small class="text-muted"><?= htmlspecialchars($courseHelpText) ?></small>
  </div>

  <div class="col-12">
    <label class="form-label">Note iscrizione</label>
    <textarea
      class="form-control"
      rows="3"
      name="note_iscrizione"
      <?= ($fieldIds['note_iscrizione'] ?? '') !== '' ? 'id="' . htmlspecialchars($fieldIds['note_iscrizione']) . '"' : '' ?>
    ><?= htmlspecialchars($noteValue) ?></textarea>
  </div>

  <div class="col-12 d-flex <?= htmlspecialchars($footerJustifyClass) ?> gap-2">
    <?php if (($cancelButtonId ?? '') !== ''): ?>
      <button class="btn btn-outline-secondary" type="button" id="<?= htmlspecialchars($cancelButtonId) ?>">
        <?= htmlspecialchars($cancelButtonLabel) ?>
      </button>
    <?php endif; ?>
    <button class="btn <?= htmlspecialchars($submitButtonClass) ?>" type="submit"><?= htmlspecialchars($submitLabel) ?></button>
  </div>
</form>
