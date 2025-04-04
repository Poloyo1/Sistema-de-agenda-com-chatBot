<?php 
$host = 'mysql.railway.internal';
$user = 'root';
$pass = 'jlhxdaTtzZMaQZMNmqRzDWlYMtrKsKSd';
$db   = 'railway';
$port = 3306;

$mysqli  = new mysqli($host, $user, $pass, $db, $port);



function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
        }
    }
}

loadEnv(__DIR__ . '/.env');

// Acessando a variável
$apiKey = getenv('API_KEY');
$clientId = getenv('CLIENT_ID');

?>
