<?php
include_once('config.php');


// Capturar o corpo bruto da requisição e envia para o banco de dados
$requestBody = file_get_contents('php://input');
$salvarRequest = mysqli_query($mysqli, "INSERT INTO requests(message) VALUES ('$requestBody')");

?>
