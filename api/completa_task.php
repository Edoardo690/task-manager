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

$id = (int)($dati["id"] ?? 0);
$completata = (int)($dati["completata"] ?? 0);

$query = $pdo->prepare("UPDATE attivita SET completata = ? WHERE id = ? AND username = ?");
$query->execute([$completata, $id, $username]);
echo json_encode("Attività aggiornata");
