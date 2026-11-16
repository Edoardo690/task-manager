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
$query = $pdo->prepare("SELECT percorso FROM file_utenti WHERE id = ? AND username = ?");
$query->execute([$id, $username]);
$riga = $query->fetch();

if (!$riga){
    echo json_encode("Errore, file non trovato");
    exit;
}

try{
    $pdo->beginTransaction();
    $pdo->prepare("UPDATE attivita SET file_id = NULL WHERE file_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM file_utenti WHERE id = ? AND username = ?")->execute([$id, $username]);
    $pdo->prepare("DELETE FROM task_file WHERE file_id = ?")->execute([$id]);
    $pdo->commit();

}   catch(PDOException $e){
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    error_log($e->getMessage());
    echo json_encode("Errore del server");
    exit;
}
$percorsoDisco = "../".$riga["percorso"];
if (file_exists($percorsoDisco)){unlink($percorsoDisco);}
echo json_encode("File eliminato con successo");