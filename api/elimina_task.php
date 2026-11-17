<?php
header("Content-Type: application/json");
require_once __DIR__ . "/controllo_sessione.php";
require 'db.php';
$username = $_SESSION["username"];

$dati = json_decode(file_get_contents("php://input"), true);

$id = (int)($dati["id"] ?? 0);

try{
    $pdo->beginTransaction();
    $query = $pdo->prepare("DELETE FROM attivita WHERE id = ? AND username = ?");
    $query->execute([$id, $username]);
    if ($query->rowCount() === 0) {
        echo json_encode("Errore: task non trovata");
        exit;
    }
    $pdo->prepare("DELETE FROM task_file WHERE task_id = ?")->execute([$id]);
    $pdo->commit();
    echo json_encode("Attività eliminata");
}   catch (Exception $e){
    error_log($e->getMessage());
    echo json_encode("Errore del server");
}
