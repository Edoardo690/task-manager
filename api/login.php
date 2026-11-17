<?php
session_start();
require 'db.php';

header("Content-Type: application/json");

$dati = json_decode(file_get_contents("php://input"), true);
$username = trim($dati["username"] ?? "");
$password = $dati["password"] ?? "";
$ip = $_SERVER["REMOTE_ADDR"];

if ($username === ""|| $password === ""){
    echo json_encode("Compila tutti i campi");
    exit;
}

$query = $pdo->prepare("SELECT COUNT(*) FROM tentativi WHERE tipo = 'login' AND ip = ? AND data_tentativo > (NOW() - INTERVAL 15 MINUTE)");
$query->execute([$ip]);
$tentativi = $query->fetchColumn();

if ($tentativi >= 3){
    $query = $pdo->prepare("SELECT 900 - TIMESTAMPDIFF(SECOND, MIN(data_tentativo), NOW()) FROM tentativi WHERE tipo = 'login' AND ip = ? AND data_tentativo > (NOW() - INTERVAL 15 MINUTE)");
    $query->execute([$ip]);
    $secondi = max(0,(int)$query->fetchColumn());

    http_response_code(429);
    echo json_encode(['errore' => "Errore, troppi tentativi",
        'secondi' => $secondi]);
    exit;
}

$query = $pdo->prepare("SELECT password, id FROM utenti WHERE username = ?");
$query->execute([$username]);
$riga = $query->fetch();


if ($riga === false || !password_verify($password, $riga["password"])) {
    $query = $pdo->prepare("INSERT INTO tentativi (tipo, ip) VALUES (?, ?)");
    $query->execute(['login', $ip]);
    echo json_encode("Password o username errati");
    exit;
}
else{
    session_regenerate_id(true);
    $query = $pdo->prepare("DELETE FROM tentativi WHERE ip = ? AND tipo = ?");
    $query->execute([$ip, 'login']);
    $_SESSION["username"] = $username;
    $_SESSION["user_id"] = $riga["id"];
    $_SESSION["username"] = $username;
    $_SESSION["user_id"] = $riga["id"];
    echo json_encode("Login in corso....");
}


