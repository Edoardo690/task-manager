<?php
session_start();
require 'db.php';
header("Content-Type: application/json");
if (!isset($_SESSION["username"], $_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode("Errore: non sei loggato");
    exit;
}
$username = $_SESSION["username"];

$dati = json_decode(file_get_contents("php://input"), true);

$testo = trim($dati["testo"] ?? "");


if ($testo === ""){
    echo json_encode("Errore, impossibile aggiungere una task vuota");
    exit;
}
if (mb_strlen($testo) > 500) {
    echo json_encode("Errore: la task può avere al massimo 500 caratteri");
    exit;
}

$data = date("Y-m-d H:i:s");
try{
    $query = $pdo->prepare("INSERT INTO attivita (username, testo, data, completata) VALUES (?,?,?,0)");
    $query ->execute([$username, $testo, $data]);
    echo json_encode("Ok attività registrata");
}   catch (PDOException $e){
    error_log($e->getMessage());
    echo json_encode("Errore del server");
}
