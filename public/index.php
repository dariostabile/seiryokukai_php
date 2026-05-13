<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'project' => 'seiryokukai_php',
    'status' => 'ok',
    'message' => 'Progetto creato correttamente',
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
