// Inizializzazione servizi
$auth = aut_service();
$corsi = corsi_service();
$wantsJson = wants_json_response();

// Controllo autenticazione
if (!$auth->isLoggedIn()) {
	http_response_code(401);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(['error' => 'Non autorizzato'], JSON_UNESCAPED_UNICODE);
	exit;
}

// Gestione POST minimale
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	echo 'POST_OK';
	exit;
}

// Gestione GET minimale
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	echo 'GET_OK';
	exit;
}
/**
 * @param array<string, mixed> $post
 * @return array<string, string>
 */
function build_add_context_from_post(array $post): array
{
	$context = [];

	foreach ($post as $key => $value) {
		if (!is_string($key)) {
			continue;
		}

		if (in_array($key, ['action', 'ajax', 'page', 'open_add', 'open_edit', 'edit_id', 'corso_id'], true)) {
			continue;
		}

		<?php

