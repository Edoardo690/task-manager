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
$idTask = (int)($dati["idTask"] ?? 0);
$idFile = (int)($dati["idFile"] ?? 0);


$query = $pdo->prepare("SELECT id FROM file_utenti WHERE id = ? AND username = ?");
$query->execute([$idFile, $username]);
$riga = $query->fetch();
if (!$riga){
    echo json_encode("Errore file non trovato");
    exit;
}
$query = $pdo->prepare("SELECT id FROM attivita WHERE id = ? AND username = ?");
$query->execute([$idTask, $username]);
$riga = $query->fetch();
if (!$riga){
    echo json_encode("Errore attività non trovata");
    exit;
}
try{
    $query = $pdo->prepare("UPDATE attivita SET file_id = ? WHERE id = ? AND username = ?");
    $query->execute([$idFile, $idTask, $username]);
    echo json_encode("File collegato con successo!");
}   catch (PDOException $e){
    error_log($e->getMessage());
    echo json_encode("Errore del server");
    exit;
}