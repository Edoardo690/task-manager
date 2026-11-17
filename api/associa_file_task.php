<?php
header("Content-Type: application/json");
require_once __DIR__ . "/controllo_sessione.php";
require 'db.php';
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

$query = $pdo->prepare("SELECT 1 FROM task_file WHERE task_id = ? AND file_id = ?");
$query->execute([$idTask, $idFile]);
if ($query->fetch()) {
    echo json_encode("File già collegato a questa attività");
    exit;
}

try{
    $query = $pdo->prepare("INSERT INTO task_file (task_id, file_id) VALUES (?,?)");
    $query->execute([$idTask, $idFile]);
    echo json_encode("File collegato con successo!");
}   catch (PDOException $e){
    error_log($e->getMessage());
    echo json_encode("Errore del server");
    exit;
}