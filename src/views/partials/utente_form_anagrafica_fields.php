<?php

declare(strict_types=1);

/**
 * Variabili attese:
 * - $utenteFormValues: array
 * - $utenteAnagraficaFieldIds: array
 * - $utenteAnagraficaIsEdit: bool
 */

$utenteFormValues = is_array($utenteFormValues ?? null) ? $utenteFormValues : [];
$utenteAnagraficaFieldIds = is_array($utenteAnagraficaFieldIds ?? null) ? $utenteAnagraficaFieldIds : [];
$utenteAnagraficaIsEdit = (bool) ($utenteAnagraficaIsEdit ?? false);

$statusFieldId = (string) ($utenteAnagraficaFieldIds['status'] ?? '');
$dataScadenzaFieldId = (string) ($utenteAnagraficaFieldIds['data_scadenza_account'] ?? '');
$nomeFieldId = (string) ($utenteAnagraficaFieldIds['nome'] ?? '');
$cognomeFieldId = (string) ($utenteAnagraficaFieldIds['cognome'] ?? '');
$usernameFieldId = (string) ($utenteAnagraficaFieldIds['username'] ?? '');
$passwordFieldId = (string) ($utenteAnagraficaFieldIds['password'] ?? '');
$emailFieldId = (string) ($utenteAnagraficaFieldIds['email'] ?? '');
$email2FieldId = (string) ($utenteAnagraficaFieldIds['email2'] ?? '');
$telefono1FieldId = (string) ($utenteAnagraficaFieldIds['telefono1'] ?? '');
$telefono2FieldId = (string) ($utenteAnagraficaFieldIds['telefono2'] ?? '');

$statusValue = (string) ($utenteFormValues['status'] ?? 'Attivo');
?>
<div class="row g-3">
  <div class="col-12 col-md-6">
    <label class="form-label">Stato</label>
    <select class="form-select" name="status" id="<?= htmlspecialchars($statusFieldId) ?>">
      <option value="Attivo" <?= $statusValue === 'Attivo' ? 'selected' : '' ?>>Attivo</option>
      <option value="Sospeso" <?= $statusValue === 'Sospeso' ? 'selected' : '' ?>>Sospeso</option>
    </select>
  </div>
  <div class="col-12 col-md-6">
    <label class="form-label" for="<?= htmlspecialchars($dataScadenzaFieldId) ?>">Data Scadenza Account</label>
    <input type="date" class="form-control" id="<?= htmlspecialchars($dataScadenzaFieldId) ?>" name="data_scadenza_account" value="<?= htmlspecialchars((string) ($utenteFormValues['data_scadenza_account'] ?? '')) ?>">
  </div>
</div>

<div class="row mt-5">
  <div class="col-12 col-md-6">
    <label class="form-label">Nome</label>
    <input class="form-control" name="nome" id="<?= htmlspecialchars($nomeFieldId) ?>" placeholder="Nome" value="<?= htmlspecialchars((string) ($utenteFormValues['nome'] ?? '')) ?>">
  </div>
  <div class="col-12 col-md-6">
    <label class="form-label">Cognome</label>
    <input class="form-control" name="cognome" id="<?= htmlspecialchars($cognomeFieldId) ?>" placeholder="Cognome" value="<?= htmlspecialchars((string) ($utenteFormValues['cognome'] ?? '')) ?>">
  </div>
  <div class="col-12 col-md-6">
    <label class="form-label">Username</label>
    <input class="form-control" name="username" id="<?= htmlspecialchars($usernameFieldId) ?>" placeholder="Username" required value="<?= htmlspecialchars((string) ($utenteFormValues['username'] ?? '')) ?>">
  </div>
  <div class="col-12 col-md-6">
    <label class="form-label">Password</label>
    <input class="form-control" name="password" id="<?= htmlspecialchars($passwordFieldId) ?>" type="password" minlength="8" <?= $utenteAnagraficaIsEdit ? 'placeholder="Lascia vuoto per non cambiare"' : 'placeholder="Password" required' ?>>
  </div>
  <div class="col-12 col-md-6">
    <label class="form-label">Email</label>
    <input class="form-control" name="email" id="<?= htmlspecialchars($emailFieldId) ?>" type="email" placeholder="Email" value="<?= htmlspecialchars((string) ($utenteFormValues['email'] ?? '')) ?>">
  </div>
  <div class="col-12 col-md-6">
    <label class="form-label">Email 2</label>
    <input class="form-control" name="email2" id="<?= htmlspecialchars($email2FieldId) ?>" type="email" placeholder="Email 2" value="<?= htmlspecialchars((string) ($utenteFormValues['email2'] ?? '')) ?>">
  </div>
  <div class="col-12 col-md-6">
    <label class="form-label">Telefono 1</label>
    <input class="form-control" name="telefono1" id="<?= htmlspecialchars($telefono1FieldId) ?>" type="tel" placeholder="Telefono 1" value="<?= htmlspecialchars((string) ($utenteFormValues['telefono1'] ?? '')) ?>">
  </div>
  <div class="col-12 col-md-6">
    <label class="form-label">Telefono 2</label>
    <input class="form-control" name="telefono2" id="<?= htmlspecialchars($telefono2FieldId) ?>" type="tel" placeholder="Telefono 2" value="<?= htmlspecialchars((string) ($utenteFormValues['telefono2'] ?? '')) ?>">
  </div>
</div>
