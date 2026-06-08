<?php

declare(strict_types=1);

/** @var string $formAction */
/** @var string $formId */
/** @var array<int, string> $hiddenFields */
/** @var array<int, array<string, mixed>> $paymentCourseOptions */
/** @var array<string, string> $values */
/** @var array<string, string> $fieldIds */
/** @var string $courseHelpText */
/** @var string $submitLabel */
/** @var string $submitButtonClass */
/** @var string $footerJustifyClass */
/** @var string $cancelButtonId */
/** @var string $cancelButtonLabel */
/** @var bool $disablePaymentActions */

$courseValue = (string) ($values['idcorso'] ?? '');
$amountValue = (string) ($values['quota_pagamento'] ?? '');
$paymentDateValue = (string) ($values['data_pagamento'] ?? '');
$expiryDateValue = (string) ($values['data_scadenza'] ?? '');
$notesValue = (string) ($values['note_pagamento'] ?? '');
?>
<form method="post" action="<?= htmlspecialchars($formAction) ?>" class="row g-3" id="<?= htmlspecialchars($formId) ?>">
  <?php foreach ($hiddenFields as $hiddenField): ?>
    <?= $hiddenField ?>
  <?php endforeach; ?>

  <div class="col-12 col-md-3">
    <label class="form-label">Corso iscritto</label>
    <select
      class="form-select"
      name="idcorso"
      <?= ($fieldIds['idcorso'] ?? '') !== '' ? 'id="' . htmlspecialchars($fieldIds['idcorso']) . '"' : '' ?>
      <?= $disablePaymentActions ? 'disabled' : 'required' ?>
    >
      <option value="">Seleziona</option>
      <?php foreach ($paymentCourseOptions as $courseOption): ?>
        <?php $optionValue = (string) ((int) ($courseOption['course_id'] ?? 0)); ?>
        <option
          value="<?= htmlspecialchars($optionValue) ?>"
          data-enrollment-id="<?= (int) ($courseOption['enrollment_id'] ?? 0) ?>"
          data-subscription-months="<?= (int) ($courseOption['subscription_months'] ?? 1) ?>"
          data-total-subscription="<?= htmlspecialchars(number_format((float) ($courseOption['total_subscription'] ?? 0), 2, '.', '')) ?>"
          data-paid-amount="<?= htmlspecialchars(number_format((float) ($courseOption['paid_amount'] ?? 0), 2, '.', '')) ?>"
          data-residual-amount="<?= htmlspecialchars(number_format((float) ($courseOption['residual_amount'] ?? 0), 2, '.', '')) ?>"
          data-suggested-amount="<?= htmlspecialchars(number_format((float) ($courseOption['suggested_amount'] ?? 0), 2, '.', '')) ?>"
          <?= $courseValue === $optionValue ? 'selected' : '' ?>
        >
          #<?= (int) ($courseOption['course_id'] ?? 0) ?> - <?= htmlspecialchars((string) ($courseOption['courses'] ?? '')) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <?php if (trim($courseHelpText) !== ''): ?>
      <small class="text-muted"><?= htmlspecialchars($courseHelpText) ?></small>
    <?php endif; ?>
  </div>

  <div class="col-12 col-md-3">
    <label class="form-label">Importo</label>
    <input
      type="number"
      step="0.01"
      min="0"
      class="form-control"
      name="quota_pagamento"
      value="<?= htmlspecialchars($amountValue) ?>"
      <?= ($fieldIds['quota_pagamento'] ?? '') !== '' ? 'id="' . htmlspecialchars($fieldIds['quota_pagamento']) . '"' : '' ?>
      required
    >
  </div>

  <div class="col-12 col-md-3">
    <label class="form-label">Data pagamento</label>
    <input
      type="date"
      class="form-control"
      name="data_pagamento"
      value="<?= htmlspecialchars($paymentDateValue) ?>"
      <?= ($fieldIds['data_pagamento'] ?? '') !== '' ? 'id="' . htmlspecialchars($fieldIds['data_pagamento']) . '"' : '' ?>
      required
    >
  </div>

  <div class="col-12 col-md-3">
    <label class="form-label">Data scadenza</label>
    <input
      type="date"
      class="form-control"
      name="data_scadenza"
      value="<?= htmlspecialchars($expiryDateValue) ?>"
      <?= ($fieldIds['data_scadenza'] ?? '') !== '' ? 'id="' . htmlspecialchars($fieldIds['data_scadenza']) . '"' : '' ?>
    >
  </div>

  <div class="col-12">
    <label class="form-label">Note pagamento</label>
    <textarea
      class="form-control"
      rows="3"
      name="note_pagamento"
      <?= ($fieldIds['note_pagamento'] ?? '') !== '' ? 'id="' . htmlspecialchars($fieldIds['note_pagamento']) . '"' : '' ?>
    ><?= htmlspecialchars($notesValue) ?></textarea>
  </div>

  <div class="col-12 d-flex <?= htmlspecialchars($footerJustifyClass) ?> gap-2">
    <?php if (($cancelButtonId ?? '') !== ''): ?>
      <button class="btn btn-outline-secondary" type="button" id="<?= htmlspecialchars($cancelButtonId) ?>">
        <?= htmlspecialchars($cancelButtonLabel) ?>
      </button>
    <?php endif; ?>
    <button class="btn <?= htmlspecialchars($submitButtonClass) ?>" type="submit" <?= $disablePaymentActions ? 'disabled' : '' ?>><?= htmlspecialchars($submitLabel) ?></button>
  </div>
</form>
