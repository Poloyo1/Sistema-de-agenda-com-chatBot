<?php
include_once('config.php');

header('Content-Type: application/json');

echo json_encode([
    'apiKey' => $apiKey,
    'clientId' => $clientId,
]);
?>
