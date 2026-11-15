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

$query = $pdo->prepare("SELECT attivita.id, attivita.testo, attivita.completata, attivita.data,
       attivita.file_id, file_utenti.nome_file, file_utenti.percorso
       FROM attivita
       LEFT JOIN file_utenti ON attivita.file_id = file_utenti.id
       WHERE attivita.username = ?");
$query->execute([$username]);
$task = $query->fetchAll();

echo json_encode($task);